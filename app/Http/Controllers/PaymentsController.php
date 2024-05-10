<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePaymentRequest;
use App\Models\Balance;
use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseGroup;
use App\Models\ExpenseParticipant;
use App\Models\ExpenseType;
use App\Models\Group;
use App\Models\Notification;
use App\Models\NotificationAttribute;
use App\Models\NotificationType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class PaymentsController extends Controller
{
    const TIMEZONE = 'America/Toronto'; // TODO: make this a user setting
    const SETTLE_ALL_BALANCES = -1;

    /**
     * Displays the create payment form.
     */
    public function create(Request $request): View
    {
        $current_user = auth()->user();

        $group = $request->input('group') ? Group::find($request->input('group')) : null;
        $friend = $request->input('friend') ? User::find($request->input('friend')) : null;

        $today = Carbon::now()->isoFormat('YYYY-MM-DD');
        $formatted_today = Carbon::now()->isoFormat('MMMM D, YYYY');

        $default_group = Group::where('id', Group::DEFAULT_GROUP)->first();

        $users_selection = $current_user->friends()
            ->select('users.*', DB::raw('SUM(balances.balance) as total_balance'))
            ->join('balances', 'users.id', 'balances.friend')
            ->where('balances.user_id', $current_user->id);

        if ($group) {
            $users_selection = $users_selection->where('balances.group_id', $group->id);
        }

        $users_selection = $users_selection->groupBy('users.id')
            ->orderBy('users.username', 'asc')
            ->get();

        if ($friend) {
            $balances_selection = Balance::select('groups.name as group_name', 'balances.*')
                ->join('groups', 'balances.group_id', 'groups.id')
                ->where('balances.user_id', $current_user->id)
                ->where('balances.friend', $friend->id)
                ->orderByRaw("
                    CASE
                        WHEN groups.id = ? THEN 0
                        ELSE 1
                    END, groups.name ASC
                ", [Group::DEFAULT_GROUP])
                ->get();
        } else {
            $balances_selection = [];
        }

        return view('payments.create', [
            'payment' => null,
            'group' => $group,
            'friend' => $friend,
            'today' => $today,
            'formatted_today' => $formatted_today,
            'default_group' => $default_group,
            'users_selection' => $users_selection,
            'balances_selection' => $balances_selection,
        ]);
    }

    /**
     * Saves the new payment.
     */
    public function store(CreatePaymentRequest $request): RedirectResponse
    {
        $current_user = auth()->user();

        $payment_validated = $request->validated();

        // Get the user_id of the payee and balance_id of the balance
        $payee_id = $payment_validated['payment-payee'];
        $payment_balance_id = (int)$payment_validated['payment-balance'];

        // Create the payment

        $payment_data = [
            'amount' => $payment_validated['payment-amount'],
            'payer' => $current_user->id,
            'expense_type_id' => $payment_balance_id === static::SETTLE_ALL_BALANCES ? ExpenseType::SETTLE_ALL_BALANCES : ExpenseType::PAYMENT,
            'category_id' => Category::PAYMENT_CATEGORY,
            'note' => $payment_validated['payment-note'],
            'date' => $payment_validated['payment-date'],
            'is_confirmed' => 0,
            'creator' => $current_user->id,
            'updator' => $current_user->id,
        ];

        $payment = Expense::create($payment_data);

        // Add the payment group(s)
        if ($payment_balance_id === static::SETTLE_ALL_BALANCES) {
            $payment_balances = Balance::where('user_id', $current_user->id)
                ->where('friend', $payee_id)
                ->get();

            foreach($payment_balances as $balance) {
                ExpenseGroup::create([
                    'expense_id' => $payment->id,
                    'group_id' => $balance->group_id,
                    'group_amount' => -1 * $balance->balance,
                ]);
            }
        } else {
            $payment_group_id = Balance::find($payment_validated['payment-balance'])->group_id;

            ExpenseGroup::create([
                'expense_id' => $payment->id,
                'group_id' => $payment_group_id,
            ]);
        }

        // Add the payee as the participant
        ExpenseParticipant::create([
            'expense_id' => $payment->id,
            'user_id' => $payee_id,
            'share' => $payment->amount,
            'percentage' => null,
            'shares' => null,
            'adjustment' => null,
            'is_settled' => 0,
        ]);

        // Send payment notifications
        $payment->sendExpenseNotifications();

        return Redirect::route('payments.show', $payment->id)->with('status', 'payment-created');
    }

    /**
     * Displays the payment page.
     */
    public function show($payment_id): View
    {
        $payment = Expense::where('id', $payment_id)->first();

        // Get formatted dates and times
        $payment->formatted_created_date = Carbon::parse($payment->created_at)->diffForHumans();
        $payment->created_date = Carbon::parse($payment->created_at)->format('M d, Y');
        $payment->created_time = Carbon::parse($payment->created_at)->setTimezone(self::TIMEZONE)->format('g:i a');
        $payment->formatted_updated_date = Carbon::parse($payment->updated_at)->diffForHumans();
        $payment->updated_date = Carbon::parse($payment->updated_at)->format('M d, Y');
        $payment->updated_time = Carbon::parse($payment->updated_at)->setTimezone(self::TIMEZONE)->format('g:i a');
        $payment->formatted_date = Carbon::parse($payment->date)->format('M d, Y');

        // Get the creator, updator, and payer of the payment
        $payment->creator_user = User::find($payment->creator);
        $payment->updator_user = User::find($payment->updator);
        $payment->payer_user = User::find($payment->payer);

        $payment->is_settle_all_balances = $payment->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES;

        $payment->group = $payment->groups->first();

        $payment->payee = ExpenseParticipant::where('expense_id', $payment->id)
            ->join('users', 'expense_participants.user_id', 'users.id')
            ->select('users.*', 'expense_participants.share')
            ->first();

        return view('payments.show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Displays the update payment form.
     */
    public function edit(Expense $payment): View
    {
        $current_user = auth()->user();

        $today = Carbon::now()->isoFormat('YYYY-MM-DD');
        $formatted_today = Carbon::now()->isoFormat('MMMM D, YYYY');

        $default_group = Group::where('id', Group::DEFAULT_GROUP)->first();

        $users_selection = $current_user->friends()
            ->select('users.*', DB::raw('SUM(balances.balance) as total_balance'))
            ->join('balances', 'users.id', 'balances.friend')
            ->where('balances.user_id', $current_user->id)
            ->groupBy('users.id')
            ->orderBy('users.username', 'asc')
            ->get();

        $balances_selection = $current_user->friends()
            ->select('groups.name as group_name', 'balances.*')
            ->join('balances', 'users.id', 'balances.friend')
            ->join('groups', 'balances.group_id', 'groups.id')
            ->where('balances.user_id', $current_user->id)
            ->orderBy('users.username', 'asc')
            ->orderByRaw("
                CASE
                    WHEN groups.id = ? THEN 0
                    ELSE 1
                END, groups.name ASC
            ", [Group::DEFAULT_GROUP])
            ->get();

        $payment->formatted_date = Carbon::parse($payment->date)->isoFormat('MMMM DD, YYYY');
        $payment->payer_user = User::find($payment->payer);
        $payment->recipient_user = User::find($payment->participants->first()->id);

        return view('payments.edit', [
            'payment' => $payment,
            'today' => $today,
            'formatted_today' => $formatted_today,
            'default_group' => $default_group,
            'users_selection' => $users_selection,
            'balances_selection' => $balances_selection,
            'group' => null,
            'friend' => null,
        ]);
    }

    /**
     * Updates the payment details.
     */
    public function update(CreatePaymentRequest $request, Expense $payment): RedirectResponse
    {


        // Update the payments's "updated_at" timestamp in case only the payee was updated,
        // and not the payment itself
        $payment->touch();

        return Redirect::route('payments.show', $payment->id)->with('status', 'payment-updated');
    }

    /**
     * Returns the payments.partials.payment-balances view with all of the current user's balances 
     * with the user specified in the request.
     */
    public function getBalancesWithUser(Request $request): View
    {
        $payment = Expense::find($request->input('payment_id'));
        $group = $request->input('group_id') ? Group::find($request->input('group_id')) : null;
        $friend_user_id = $request->input('friend_user_id');

        $current_user = auth()->user();

        $users_selection = $current_user->friends()
            ->select('users.*', DB::raw('SUM(balances.balance) as total_balance'))
            ->join('balances', 'users.id', 'balances.friend')
            ->where('balances.user_id', $current_user->id)
            ->where('balances.friend', $friend_user_id)
            ->groupBy('users.id')
            ->orderBy('users.username', 'asc')
            ->get();

        $balances_selection = Balance::select('groups.name as group_name', 'balances.*')
            ->join('groups', 'balances.group_id', 'groups.id')
            ->where('balances.user_id', $current_user->id)
            ->where('balances.friend', $friend_user_id)
            ->orderByRaw("
                CASE
                    WHEN groups.id = ? THEN 0
                    ELSE 1
                END, groups.name ASC
            ", [Group::DEFAULT_GROUP])
            ->get();

        return view('payments.partials.payment-balances', [
            'payment' => $payment,
            'group' => $group,
            'users_selection' => $users_selection,
            'balances_selection' => $balances_selection,
        ]);
    }

    /**
     * Confirm that the payment was received. Update the notifications and adjust the balances.
     */
    public function confirmPayment(Request $request)
    {
        $payee_notification = Notification::find($request->input('notification_id'));

        $payment = Expense::find($payee_notification->attributes()->value('expense_id'));

        // Update the expenses.is_confirmed field
        $payment->update([
            'is_confirmed' => 1,
        ]);

        // Update the balances
        Expense::updateBalances($payment, $payee_notification->recipient, $payment->amount);

        // Update notifications

        $payer_notification = Notification::updateOrCreate(
            [
                'notification_type_id' => NotificationType::PAYMENT,
                'creator' => $payment->payer,
                'sender' => $payment->payer,
                'recipient' => $payment->payer,
            ],
            [
                'notification_type_id' => NotificationType::PAYMENT_CONFIRMED,
            ],
        );

        if ($payer_notification->wasRecentlyCreated) {
            NotificationAttribute::create([
                'notification_id' => $payer_notification->id,
                'expense_id' => $payment->id,
            ]);
        }

        $payee_notification->update([
            'notification_type_id' => NotificationType::PAYMENT_CONFIRMED,
        ]);
    }

    /**
     * The payment was rejected. Update the notifications.
     */
    public function rejectPayment(Request $request)
    {
        // TODO
    }

    /**
     * Deletes the payment.
     */
    public function destroy(Expense $payment): RedirectResponse
    {
        $payment->delete();

        return Redirect::route('expenses')->with('status', 'payment-deleted');
    }
}
