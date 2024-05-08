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

        return view('payments.create', [
            'expense' => null,
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

        // Create the payment

        // Get the group from the payment balance
        $payment_group = Balance::find($payment_validated['payment-balance'])->value('group_id');

        // Get the user_id of the payee
        $payee_id = $payment_validated['payment-payee'];

        $payment_data = [
            'amount' => $payment_validated['payment-amount'],
            'payer' => $current_user->id,
            'group_id' => $payment_group, // TODO: remove this when group_id is removed from the expenses table
            'expense_type_id' => ExpenseType::PAYMENT,
            'category_id' => Category::PAYMENT_CATEGORY,
            'note' => $payment_validated['payment-note'],
            'date' => $payment_validated['payment-date'],
            'is_confirmed' => 0,
            'creator' => $current_user->id,
            'updator' => $current_user->id,
        ];

        $payment = Expense::create($payment_data);

        // Add the expense group
        ExpenseGroup::create([
            'expense_id' => $payment->id,
            'group_id' => $payment_group,
        ]);

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

        return Redirect::route('expenses')->with('status', 'payment-created');
    }

    /**
     * Displays the payment page.
     */
    public function show($expense_id): View
    {
        $payment = Expense::where('id', $expense_id)->first();

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

    // TODO: update payment

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
