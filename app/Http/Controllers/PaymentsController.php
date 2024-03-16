<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePaymentRequest;
use App\Models\Balance;
use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseParticipant;
use App\Models\ExpenseType;
use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class PaymentsController extends Controller
{
    /**
     * Displays the create Payment form.
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

    public function store(CreatePaymentRequest $request): RedirectResponse
    {
        $current_user = auth()->user();

        $payment_validated = $request->validated();

        // Create the Payment

        // Get the group from the payment balance
        $payment_group = Balance::find($payment_validated['payment-balance'])->value('group_id');

        // Get the User id of the payee
        $payee_id = $payment_validated['payment-payee'];

        $payment_data = [
            'amount' => $payment_validated['payment-amount'],
            'payer' => $current_user->id,
            'group_id' => $payment_group,
            'expense_type_id' => ExpenseType::PAYMENT,
            'category_id' => Category::PAYMENT_CATEGORY,
            'note' => $payment_validated['payment-note'],
            'date' => $payment_validated['payment-date'],
            'creator' => $current_user->id,
            'updator' => $current_user->id,
        ];

        $payment = Expense::create($payment_data);

        ExpenseParticipant::create([
            'expense_id' => $payment->id,
            'user_id' => $payee_id,
            'share' => $payment->amount,
            'percentage' => null,
            'shares' => null,
            'adjustment' => null,
            'is_settled' => 0,
        ]);

        Expense::updateBalances($payment, $payee_id, $payment->amount);

        return Redirect::route('expenses')->with('status', 'payment-created');
    }


    /**
     * Deletes the Payment.
     */
    public function destroy(Expense $payment): RedirectResponse
    {
        $payment->delete();

        return Redirect::route('expenses')->with('status', 'payment-deleted');
    }
}
