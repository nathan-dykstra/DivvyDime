<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExpenseRequest;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class ExpensesController extends Controller
{
    const TIMEZONE = 'America/Toronto'; // TODO: make this a user setting

    /**
     * Displays the User's Expenses.
     */
    public function index(): View
    {
        $current_user = auth()->user();

        $expenses = $current_user->expenses()
            ->orderBy('date', 'DESC')
            ->get();

        $expenses = $expenses->map(function ($expense) use ($current_user) {
            // Get formatted dates and times

            $expense->formatted_date = Carbon::parse($expense->date)->diffForHumans();

            $expense->date = Carbon::parse($expense->date)->format('M d, Y');

            $expense->formatted_time = Carbon::parse($expense->date)->setTimezone(self::TIMEZONE)->format('g:i a');

            // Get the User who paid for the Expense
            $expense->payer_user = User::where('id', $expense->payer)->first();

            // Get the current User's share, lent, and borrowed amounts

            $current_user_share = ExpenseParticipant::where('expense_id', $expense->id)
                ->where('user_id', $current_user->id)
                ->value('share');

            if ($expense->payer === $current_user->id) {
                $expense->lent = $expense->amount - $current_user_share;
            }

            if ($current_user_share) {
                $expense->borrowed = $current_user_share;
            }

            // Get the Expense's Group
            $expense->group = Group::where('id', $expense->group_id)->first();

            return $expense;
        });

        return view('expenses.expenses-list', [
            'expenses' => $expenses,
        ]);
    }

    /**
     * Displays the create Expense form.
     */
    public function create(): View
    {
        $current_user = auth()->user();

        $groups = $current_user->groups()
            ->whereNot('groups.id', Group::DEFAULT_GROUP)
            ->orderBy('groups.name', 'ASC')
            ->get();

        $default_group = Group::where('id', Group::DEFAULT_GROUP)->first();

        $default_expense_type = ExpenseType::EQUAL;

        $expense_type_names = [
            ExpenseType::EQUAL => __('Equal'),
            ExpenseType::AMOUNT => __('Amount'),
            ExpenseType::PERCENTAGE => __('Percentage'),
            ExpenseType::SHARE => __('Share'),
            ExpenseType::ADJUSTMENT => __('Adjustment'),
            ExpenseType::REIMBURSEMENT => __('Reimbursement'),
            ExpenseType::ITEMIZED => __('Itemized'),
        ];

        $expense_type_ids = [
            'equal' => ExpenseType::EQUAL,
            'amount' => ExpenseType::AMOUNT,
            'percentage' => ExpenseType::PERCENTAGE,
            'share' => ExpenseType::SHARE,
            'adjustment' => ExpenseType::ADJUSTMENT,
            'reimbursement' => ExpenseType::REIMBURSEMENT,
            'itemized' => ExpenseType::ITEMIZED,
        ];

        $today = Carbon::now()->format('Y-m-d');

        $formatted_today = Carbon::now()->format('F j, Y');

        return view('expenses.create', [
            'expense' => null,
            'groups' => $groups,
            'default_group' => $default_group,
            'today' => $today,
            'formatted_today' => $formatted_today,
            'default_expense_type' => $default_expense_type,
            'expense_type_names' => $expense_type_names,
            'expense_type_ids' => $expense_type_ids,
        ]);
    }

    /**
     * Saves the new Expense.
     */
    public function store(CreateExpenseRequest $request): RedirectResponse
    {
        $current_user = auth()->user();

        $expense_validated = $request->validated();

        // Create the Expense

        $expense_data = [
            'name' => $expense_validated['expense-name'],
            'amount' => $expense_validated['expense-amount'],
            'payer' => $expense_validated['expense-paid'],
            'group_id' => $expense_validated['expense-group'],
            'expense_type_id' => $expense_validated['expense-split'],
            /*'category_id' => $expense_validated['expense-category'],*/
            'note' => $expense_validated['expense-note'],
            'date' => $expense_validated['expense-date'],
            'creator' => $current_user->id,
        ];

        $expense = Expense::create($expense_data);

        // Create the ExpenseParticipants

        if ((int)$expense_data['expense_type_id'] === ExpenseType::EQUAL) {
            $participants = array_map('intval', $request->input('split-equal-user', []));

            $amount_per_participant = round((float)$expense_data['amount'] / count($participants), 2);
            $remaining_amount = (float)$expense_data['amount'];

            for ($i = 0; $i < count($participants); $i++) {
                if ($i === count($participants) - 1) {
                    $expense_participant = ExpenseParticipant::create([
                        'expense_id' => $expense->id,
                        'user_id' => $participants[$i],
                        'share' => $remaining_amount,
                        'percentage' => null,
                        'shares' => null,
                        'adjustment' => null,
                        'is_settled' => 0,
                    ]);
                } else {
                    $expense_participant = ExpenseParticipant::create([
                        'expense_id' => $expense->id,
                        'user_id' => $participants[$i],
                        'share' => $amount_per_participant,
                        'percentage' => null,
                        'shares' => null,
                        'adjustment' => null,
                        'is_settled' => 0,
                    ]);
                }

                $remaining_amount -= $amount_per_participant;
            }
        } else if ((int)$expense_data['expense_type_id'] === ExpenseType::AMOUNT) {
        }

        return Redirect::route('expenses.show', $expense->id)->with('status', 'expense-created');
    }

    /**
     * Displays the Expense page.
     */
    public function show(Expense $expense): View
    {
        return view('expenses.show', [
            'expense' => $expense,
        ]);
    }

    /**
     * Displays the update Expense form.
     */
    public function edit(Expense $expense): View
    {
        $current_user = auth()->user();

        $groups = $current_user->groups()
            ->whereNot('groups.id', Group::DEFAULT_GROUP)
            ->orderBy('groups.name', 'ASC')
            ->get();

        $default_group = Group::where('id', Group::DEFAULT_GROUP)->first();

        $default_expense_type = ExpenseType::EQUAL;

        $expense_type_names = [
            ExpenseType::EQUAL => __('Equal'),
            ExpenseType::AMOUNT => __('Amount'),
            ExpenseType::PERCENTAGE => __('Percentage'),
            ExpenseType::SHARE => __('Share'),
            ExpenseType::ADJUSTMENT => __('Adjustment'),
            ExpenseType::REIMBURSEMENT => __('Reimbursement'),
            ExpenseType::ITEMIZED => __('Itemized'),
        ];

        $expense_type_ids = [
            'equal' => ExpenseType::EQUAL,
            'amount' => ExpenseType::AMOUNT,
            'percentage' => ExpenseType::PERCENTAGE,
            'share' => ExpenseType::SHARE,
            'adjustment' => ExpenseType::ADJUSTMENT,
            'reimbursement' => ExpenseType::REIMBURSEMENT,
            'itemized' => ExpenseType::ITEMIZED,
        ];

        $today = Carbon::now()->isoFormat('YYYY-MM-DD');

        $formatted_today = Carbon::now()->isoFormat('MMMM D, YYYY');

        $expense->formatted_date = Carbon::parse($expense->date)->isoFormat('MMMM D, YYYY');
        $expense->payer_username = User::where('id', $expense->payer)->first()->username;

        return view('expenses.edit', [
            'expense' => $expense,
            'groups' => $groups,
            'default_group' => $default_group,
            'today' => $today,
            'formatted_today' => $formatted_today,
            'default_expense_type' => $default_expense_type,
            'expense_type_names' => $expense_type_names,
            'expense_type_ids' => $expense_type_ids,
        ]);
    }

    /**
     * Updates the Expense details.
     */
    public function update(CreateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $expense_validated = $request->validated();

        // Update the Expense

        $expense_data = [
            'name' => $expense_validated['expense-name'],
            'amount' => $expense_validated['expense-amount'],
            'payer' => $expense_validated['expense-paid'],
            'group_id' => $expense_validated['expense-group'],
            'expense_type_id' => $expense_validated['expense-split'],
            /*'category_id' => $expense_validated['expense-category'],*/
            'note' => $expense_validated['expense-note'],
            'date' => $expense_validated['expense-date'],
        ];

        $expense->update($expense_data);

        // Update the ExpensePartcipants

        $updated_participants = [];

        if ((int)$expense_data['expense_type_id'] === ExpenseType::EQUAL) {
            $participants = array_map('intval', $request->input('split-equal-user', []));
            $updated_participants = $participants;

            $amount_per_participant = round((float)$expense_data['amount'] / count($participants), 2);
            $remaining_amount = (float)$expense_data['amount'];

            for ($i = 0; $i < count($participants); $i++) {
                if ($i === count($participants) - 1) {
                    $expense_participant = ExpenseParticipant::updateOrCreate(
                        [
                            'expense_id' => $expense->id,
                            'user_id' => $participants[$i]
                        ],
                        [
                            'share' => $remaining_amount,
                            'percentage' => null,
                            'shares' => null,
                            'adjustment' => null,
                            'is_settled' => 0,
                        ]
                    );
                } else {
                    $expense_participant = ExpenseParticipant::updateOrCreate(
                        [
                            'expense_id' => $expense->id,
                            'user_id' => $participants[$i]
                        ],
                        [
                            'share' => $amount_per_participant,
                            'percentage' => null,
                            'shares' => null,
                            'adjustment' => null,
                            'is_settled' => 0,
                        ]
                    );
                }

                $remaining_amount -= $amount_per_participant;
            }
        } else if ((int)$expense_data['expense_type_id'] === ExpenseType::AMOUNT) {
        }

        // Delete any old participants who were removed from the expense

        $expense_participants = ExpenseParticipant::where('expense_id', $expense->id)->get();

        foreach ($expense_participants as $expense_participant) {
            if (!in_array($expense_participant->user_id, $updated_participants)) {
                $expense_participant->delete();
            }
        }

        return Redirect::route('expenses.show', $expense->id)->with('status', 'expense-updated');
    }

    /**
     * Deletes the Expense.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return Redirect::route('expenses')->with('status', 'expense-deleted');
    }

    /**
     * Filters the "Who was involved?" Friends list.
     */
    public function searchFriendsToInclude(Request $request)
    {
        $search_string = $request->input('search_string');

        $users_base_query = auth()->user()->friends()
            ->union(
                User::where('id', auth()->user()->id)
            );

        $users = DB::table(DB::raw("({$users_base_query->toSql()}) as users"))
            ->select('users.*')
            ->where(function ($query) use ($search_string) {
                $query->whereRaw('users.username LIKE ?', ["%$search_string%"])
                    ->orWhereRaw('users.email LIKE ?', ["%$search_string%"]);
            });

        $new_bindings = array_merge($users_base_query->getBindings(), $users->getBindings());
        $users->setBindings($new_bindings);

        $users = $users->orderByRaw("
                CASE
                    WHEN users.id = ? THEN 0
                    ELSE 1
                END, users.username ASC
            ", [auth()->user()->id])
            ->get();

        return response()->json($users);
    }

    /**
     * Filters the Expenses list.
     */
    public function search(Request $request): View
    {
        $current_user = auth()->user();

        $search_string = $request->input('search_string');

        $expenses_query = $current_user->expenses();

        $base_query_bindings = $expenses_query->getBindings();

        if ($search_string) {
            $expenses_query = DB::table(DB::raw("({$expenses_query->toSql()}) as expenses"))
                ->select('expenses.*')
                ->join('expense_participants AS ep', 'expenses.id', 'ep.expense_id')
                ->join('users AS participant_users', 'ep.user_id', 'participant_users.id')
                ->join('users AS payer_users', 'expenses.payer', 'payer_users.id')
                ->join('groups', 'expenses.group_id', 'groups.id')
                ->where(function ($query) use ($search_string) {
                    $query->whereRaw('participant_users.username LIKE ?', ["%$search_string%"])
                        ->orWhereRaw('participant_users.email LIKE ?', ["%$search_string%"])
                        ->orWhereRaw('payer_users.username LIKE ?', ["%$search_string%"])
                        ->orWhereRaw('payer_users.email LIKE ?', ["%$search_string%"])
                        ->orWhereRaw('groups.name LIKE ?', ["%$search_string%"])
                        ->orWhereRaw('expenses.name LIKE ?', ["%$search_string%"]);
                })
                ->distinct();

            $new_bindings = array_merge($base_query_bindings, $expenses_query->getBindings());
            $expenses_query->setBindings($new_bindings);
        }

        $expenses = $expenses_query->orderBy('date', 'DESC')->get();

        $expenses = $this->augmentExpenses($expenses);

        return view('expenses.partials.expenses', ['expenses' => $expenses]);
    }

    /**
     * Adds formatted dates/times, current User's lent/borrowed amounts, and group information to the expenses.
     */
    protected function augmentExpenses($expenses)
    {
        $current_user = auth()->user();

        $expenses = $expenses->map(function ($expense) use ($current_user) {
            $expense->payer_user = User::where('id', $expense->payer)->first();

            $expense->formatted_date = Carbon::parse($expense->date)->diffForHumans();

            $expense->date = Carbon::parse($expense->date)->format('M d, Y');

            $expense->formatted_time = Carbon::parse($expense->date)->setTimezone(self::TIMEZONE)->format('g:i a');

            $current_user_share = ExpenseParticipant::where('expense_id', $expense->id)
                ->where('user_id', $current_user->id)
                ->value('share');

            if ($expense->payer === $current_user->id) {
                $expense->lent = $expense->amount - $current_user_share;
            }

            if ($current_user_share) {
                $expense->borrowed = $current_user_share;
            }

            $expense->group = Group::where('id', $expense->group_id)->first();

            return $expense;
        });

        return $expenses;
    }
}
