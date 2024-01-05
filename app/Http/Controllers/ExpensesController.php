<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExpenseRequest;
use App\Models\Expense;
use App\Models\ExpenseParticipant;
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
        $users = auth()->user()->friends()
            ->union(
                User::where('id', auth()->user()->id)
            )
            ->orderByRaw("
                CASE
                    WHEN id = ? THEN 0
                    ELSE 1
                END, username ASC
            ", [auth()->user()->id])
            ->get();

        return view('expenses.create', [
            'expense' => null,
            'users' => $users,
        ]);
    }

    /**
     * Saves the new Expense.
     */
    public function store(CreateExpenseRequest $request): RedirectResponse
    {
        /*$current_user = auth()->user();

        $group = Group::create($request->validated());

        $group->owner = $current_user->id;
        $group->save();

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $current_user->id,
        ]);

        return Redirect::route('groups.show', $group->id)->with('status', 'group-created');*/
    }

    public function show($expense): View
    {

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
