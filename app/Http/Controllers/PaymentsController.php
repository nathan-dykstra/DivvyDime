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
use PhpParser\Node\Stmt\Nop;

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

            $balances_selection = $this->getDisplayBalances($balances_selection);

            $total_balance = $balances_selection->sum('display_balance');
        } else {
            $balances_selection = [];

            $total_balance = 0;
        }

        return view('payments.create', [
            'payment' => null,
            'group' => $group,
            'friend' => $friend,
            'today' => $today,
            'formatted_today' => $formatted_today,
            'default_group' => $default_group,
            'users_selection' => $users_selection,
            'total_balance' => $total_balance,
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
    public function show($payment_id)
    {
        $payment = Expense::find($payment_id);

        // Handle user trying to view an expense as a payment
        if (!($payment->expense_type_id === ExpenseType::PAYMENT || $payment->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES)) {
            return Redirect::route('expenses.show', $payment_id);
        }

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

        $payment_payee_id = ExpenseParticipant::where('expense_id', $payment->id)->value('user_id');
        $payment->payee = User::find($payment_payee_id);

        $payment_images = $payment->images()
            ->orderBy('created_at', 'ASC')
            ->get();

        return view('payments.show', [
            'payment' => $payment,
            'max_images_allowed' => Expense::MAX_IMAGES_ALLOWED,
            'payment_images' => $payment_images,
        ]);
    }

    /**
     * Displays the update payment form.
     */
    public function edit(Expense $payment)
    {
        // Handle user trying to edit an expense as a payment
        if (!($payment->expense_type_id === ExpenseType::PAYMENT || $payment->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES)) {
            return Redirect::route('expenses.edit', $payment);
        }

        $current_user = auth()->user();

        $today = Carbon::now()->isoFormat('YYYY-MM-DD');
        $formatted_today = Carbon::now()->isoFormat('MMMM D, YYYY');

        $default_group = Group::where('id', Group::DEFAULT_GROUP)->first();

        $payee = $payment->participants->first();

        $payment->formatted_date = Carbon::parse($payment->date)->isoFormat('MMMM DD, YYYY');
        $payment->is_settle_all_balances = $payment->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES;
        $payment->payer_user = User::find($payment->payer);
        $payment->recipient_user = User::find($payee->id);

        $users_selection = $current_user->friends()
            ->select('users.*', DB::raw('SUM(balances.balance) as total_balance'))
            ->join('balances', 'users.id', 'balances.friend')
            ->where('balances.user_id', $current_user->id)
            ->groupBy('users.id')
            ->orderBy('users.username', 'asc')
            ->get();

        $balances_selection = Balance::select('groups.name as group_name', 'balances.*')
            ->join('groups', 'balances.group_id', 'groups.id')
            ->where('balances.user_id', $current_user->id)
            ->where('balances.friend', $payee->id)
            ->orderByRaw("
                CASE
                    WHEN groups.id = ? THEN 0
                    ELSE 1
                END, groups.name ASC
            ", [Group::DEFAULT_GROUP])
            ->get();

        // Display balances as they were before the payment was created
        $balances_selection = $this->getDisplayBalances($balances_selection, $payment);

        $total_balance = $balances_selection->sum('display_balance');

        // Ensure total balance displayed for the current payee is based on the display balances,
        // not the actual balances
        foreach($users_selection as $user) {
            if ($user->id === $payee->id) {
                $user->total_balance = $total_balance;
                break;
            }
        }

        return view('payments.edit', [
            'payment' => $payment,
            'today' => $today,
            'formatted_today' => $formatted_today,
            'default_group' => $default_group,
            'users_selection' => $users_selection,
            'total_balance' => $total_balance,
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
        $current_user = auth()->user();

        $payment_validated = $request->validated();

        // Undo the balance adjustments from the initial state of the payment
        if ($payment->is_confirmed) {
            $payment->undoBalanceAdjustments();
        }

        // Get the user_id of the payee and balance_id of the balance
        $payee_id = $payment_validated['payment-payee'];
        $payment_balance_id = (int)$payment_validated['payment-balance'];

        // Determine whether the payment will need to be re-confirmed
        $resend_confirmation = false;
        if ($payee_id != $payment->participants->first()->id || $payment_validated['payment-amount'] != $payment->amount) {
            $resend_confirmation = true;
        }

        // Update the payment

        $payment_data = [
            'amount' => $payment_validated['payment-amount'],
            'payer' => $current_user->id,
            'expense_type_id' => $payment_balance_id === static::SETTLE_ALL_BALANCES ? ExpenseType::SETTLE_ALL_BALANCES : ExpenseType::PAYMENT,
            'category_id' => Category::PAYMENT_CATEGORY,
            'note' => $payment_validated['payment-note'],
            'date' => $payment_validated['payment-date'],
            'creator' => $current_user->id,
            'updator' => $current_user->id,
        ];

        $payment->update($payment_data);

        // Update the payment group(s)

        $payment_groups = [];

        if ($payment_balance_id === static::SETTLE_ALL_BALANCES) {
            $payment_balances = Balance::where('user_id', $current_user->id)
                ->where('friend', $payee_id)
                ->get();

            foreach($payment_balances as $balance) {
                $payment_groups[] = [
                    'group_id' => $balance->group_id,
                    'group_amount' => -1 * $balance->balance
                ];
            }
        } else {
            $payment_groups = [
                [
                    'group_id' => Balance::find($payment_validated['payment-balance'])->group_id,
                    'group_amount' => null,
                ]
            ];
        }

        $payment->groups()->sync($payment_groups);
        $payment->load('groups'); // Refresh the relationship to avoid problems with old cached data

        // Update the payee
        ExpenseParticipant::where('expense_id', $payment->id)->update([
            'user_id' => $payee_id,
            'share' => $payment->amount,
        ]);

        if ($resend_confirmation) {
            // Set the payment as unconfirmed
            $payment->update(['is_confirmed' => 0]);

            // Delete the old payment notifications

            $notifications_to_delete = Notification::whereHas('attributes', function ($query) use ($payment) {
                $query->where('expense_id', $payment->id);
            })->get();

            foreach ($notifications_to_delete as $notification) {
                $notification->delete();
            }

            // Send the updated payment notifications
            $payment->sendExpenseNotifications();
        } else {
            if ($payment->is_confirmed) {
                // The payee and amount were not changed, so the balances can be update immediately
                Expense::updateBalances($payment, $payment->participants->first()->id, $payment->amount);
            }
        }

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
        $current_user = auth()->user();

        $payment = Expense::find($request->input('payment_id'));
        if ($payment) {
            $payment->is_settle_all_balances = $payment->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES;
        }

        $group = $request->input('group_id') ? Group::find($request->input('group_id')) : null;
        $friend_user_id = $request->input('friend_user_id');

        $users_selection = $current_user->friends()
            ->select('users.*')
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

        $balances_selection = $this->getDisplayBalances($balances_selection, $payment);

        $total_balance = $balances_selection->sum('display_balance');

        return view('payments.partials.payment-balances', [
            'payment' => $payment,
            'group' => $group,
            'users_selection' => $users_selection,
            'total_balance' => $total_balance,
            'balances_selection' => $balances_selection,
        ]);
    }

    /**
     * Confirm that the payment was received. Update the notifications and adjust the balances.
     */
    public function confirmPayment(Request $request)
    {
        if ($request->has('notification_id')) {
            $payee_notification = Notification::find($request->input('notification_id'));

            $payment = Expense::find($payee_notification->attributes()->value('expense_id'));

            $this->confirmPaymentLogic($payment, $payee_notification);

            return response()->json([
                'message' => 'Payment confirmed successfully!',
            ]);
        } else {
            $payment = Expense::find($request->input('payment_id'));

            $payee_notification = Notification::where('notification_type_id', NotificationType::PAYMENT)
                ->where('creator', $payment->payer)
                ->where('sender', $payment->payer)
                ->where('recipient', $payment->participants->first()->id)
                ->first();

            $this->confirmPaymentLogic($payment, $payee_notification);

            return Redirect::route('payments.show', $payment->id)->with('status', 'payment-confirmed');
        }
    }

    /**
     * The payment was rejected. Update the notifications.
     */
    public function rejectPayment(Request $request)
    {
        if ($request->has('notification_id')) {
            $payee_notification = Notification::find($request->input('notification_id'));

            $payment = Expense::find($payee_notification->attributes()->value('expense_id'));

            $this->rejectPaymentLogic($payment, $payee_notification);

            return response()->json([
                'message' => 'Payment rejected successfully!',
            ]);
        } else {
            $payment = Expense::find($request->input('payment_id'));

            $payee_notification = Notification::where('notification_type_id', NotificationType::PAYMENT)
                ->where('creator', $payment->payer)
                ->where('sender', $payment->payer)
                ->where('recipient', $payment->participants->first()->id)
                ->first();

            $this->rejectPaymentLogic($payment, $payee_notification);

            return Redirect::route('payments.show', $payment->id)->with('status', 'payment-rejected');
        }
    }

    /**
     * Deletes the payment.
     */
    public function destroy(Expense $payment): RedirectResponse
    {
        $payment->delete();

        return Redirect::route('expenses')->with('status', 'payment-deleted');
    }

    /**
     * Updates the expenses.note field.
     */
    public function updateNote(Request $request, Expense $payment)
    {
        $request->validate([
            'payment-note' => ['nullable', 'string', 'max:65535'],
        ]);

        $payment_note_input = $request->input('payment-note');

        $payment->note = $payment_note_input;
        $payment->updator = $request->user()->id;
        $payment->save();
        $payment->touch(); // Make sure timestamp is updated if the updator doesn't change

        return Redirect::route('payments.show', $payment->id)->with('status', 'payment-note-updated');
    }

    /**
     * If the payment doesn't exist or exists but is not confirmed, then return the actual
     * payer-to-payee balances. If the payment does exist and is confirmed, then return the 
     * state of the payer-to-payee balances before the payment.
     */
    protected function getDisplayBalances($balances, $payment = null)
    {
        $balances = $balances->map(function ($balance) use ($payment) {
            if ($payment && $payment?->is_confirmed && in_array($balance->group_id, $payment->groups->pluck('id')->toArray())) {
                if ($payment->is_settle_all_balances) {
                    $group_amount = ExpenseGroup::where('expense_id', $payment->id)->where('group_id', $balance->group_id)->value('group_amount');
                    $balance->display_balance = $balance->balance - $group_amount;
                } else {
                    $balance->display_balance = $balance->balance - $payment->amount;
                }
            } else {
                $balance->display_balance = $balance->balance;
            }

            $balance->group_img = Group::find($balance->group_id)->getGroupImageUrlAttribute();

            return $balance;
        });

        return $balances;
    }

    /**
     * confirmPayment() accepts requests from the payment notification and the payment page. 
     * It then passes the necessary details to this function to actually confirm the payment.
     */
    protected function confirmPaymentLogic($payment, $payee_notification)
    {
        // Update notifications

        if ($payment->is_rejected) {
            $payer_notification = Notification::updateOrCreate(
                [
                    'notification_type_id' => NotificationType::PAYMENT_REJECTED,
                    'creator' => $payment->payer,
                    'sender' => $payment->payer,
                    'recipient' => $payment->payer,
                ],
                [
                    'notification_type_id' => NotificationType::PAYMENT_CONFIRMED,
                ],
            );

            $payee_notification = Notification::updateOrCreate(
                [
                    'notification_type_id' => NotificationType::PAYMENT_REJECTED,
                    'creator' => $payment->payer,
                    'sender' => $payment->payer,
                    'recipient' => $payment->participants->first()->id,
                ],
                [
                    'notification_type_id' => NotificationType::PAYMENT_CONFIRMED,
                ],
            );

            if ($payee_notification->wasRecentlyCreated) {
                NotificationAttribute::create([
                    'notification_id' => $payee_notification->id,
                    'expense_id' => $payment->id,
                ]);
            }
        } else {
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

            $payee_notification->update([
                'notification_type_id' => NotificationType::PAYMENT_CONFIRMED,
            ]);
        }

        if ($payer_notification->wasRecentlyCreated) {
            NotificationAttribute::create([
                'notification_id' => $payer_notification->id,
                'expense_id' => $payment->id,
            ]);
        }

        // Update the payment confirmation/rejection fields
        $payment->update([
            'is_confirmed' => 1,
            'is_rejected' => 0,
        ]);

        // Update the balances
        Expense::updateBalances($payment, $payment->participants->first()->id, $payment->amount);
    }

    /**
     * rejectPayment() accepts requests from the payment notification and the payment page. 
     * It then passes the necessary details to this function to actually reject the payment.
     */
    protected function rejectPaymentLogic($payment, $payee_notification)
    {
        // Update the payment confirmation/rejection fields
        $payment->update([
            'is_confirmed' => 0,
            'is_rejected' => 1,
        ]);

        // Update notifications

        $payer_notification = Notification::updateOrCreate(
            [
                'notification_type_id' => NotificationType::PAYMENT,
                'creator' => $payment->payer,
                'sender' => $payment->payer,
                'recipient' => $payment->payer,
            ],
            [
                'notification_type_id' => NotificationType::PAYMENT_REJECTED,
            ],
        );

        if ($payer_notification->wasRecentlyCreated) {
            NotificationAttribute::create([
                'notification_id' => $payer_notification->id,
                'expense_id' => $payment->id,
            ]);
        }

        $payee_notification->update([
            'notification_type_id' => NotificationType::PAYMENT_REJECTED,
        ]);
    }
}
