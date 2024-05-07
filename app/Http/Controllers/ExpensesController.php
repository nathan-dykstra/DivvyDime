<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExpenseRequest;
use App\Models\Balance;
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
use Illuminate\Support\Str;

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
            ->orderBy('created_at', 'DESC')
            ->get();

        $expenses = $this->augmentExpenses($expenses);

        return view('expenses.expenses-list', [
            'expenses' => $expenses,
        ]);
    }

    /**
     * Displays the create Expense form.
     */
    public function create(Request $request): View
    {
        $current_user = auth()->user();

        $groups = $current_user->groups()
            ->orderByRaw("
                CASE
                    WHEN groups.id = ? THEN 0
                    ELSE 1
                END, groups.name ASC
            ", [Group::DEFAULT_GROUP])
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

        // Get additional paramters from the route if the Expense was created from a Group or Friend

        $group = $request->input('group') ? Group::find($request->input('group')) : null;
        $friend = $request->input('friend') ? User::find($request->input('friend')) : null;

        if ($group) {
            $group->group_members = $group->members()->orderByRaw("
                CASE
                    WHEN users.id = ? THEN 0
                    ELSE 1
                END, users.username ASC
            ", [$current_user->id])
            ->get();
        }

        return view('expenses.create', [
            'expense' => null,
            'groups' => $groups,
            'default_group' => $default_group,
            'today' => $today,
            'formatted_today' => $formatted_today,
            'default_expense_type' => $default_expense_type,
            'expense_type_names' => $expense_type_names,
            'expense_type_ids' => $expense_type_ids,
            'group' => $group,
            'friend' => $friend,
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
            'group_id' => $expense_validated['expense-group'], // TODO: Remove this when group_id is removed from expenses table
            'expense_type_id' => $expense_validated['expense-split'],
            /*'category_id' => $expense_validated['expense-category'],*/
            'note' => $expense_validated['expense-note'],
            'date' => $expense_validated['expense-date'],
            'creator' => $current_user->id,
            'updator' => $current_user->id,
        ];

        $expense = Expense::create($expense_data);

        // Add the expense group
        ExpenseGroup::create([
            'expense_id' => $expense->id,
            'group_id' => $expense_validated['expense-group'],
        ]);

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

                // Update the group Balances between the expense payer and participant
                if ($participants[$i] !== $expense->payer) {
                    Expense::updateBalances($expense, $participants[$i], $expense_participant->share);
                }

                $remaining_amount -= $amount_per_participant;
            }
        } else if ((int)$expense_data['expense_type_id'] === ExpenseType::AMOUNT) {
            foreach ($request->all() as $key => $value) {
                if (Str::startsWith($key, 'split-amount-item-') && (float)$value != 0) {
                    $user_id = (int)Str::after($key, 'split-amount-item-');

                    $expense_participant = ExpenseParticipant::create([
                        'expense_id' => $expense->id,
                        'user_id' => $user_id,
                        'share' => $value,
                        'percentage' => null,
                        'shares' => null,
                        'adjustment' => null,
                        'is_settled' => 0,
                    ]);

                    // Update the group Balances between the expense payer and participant
                    if ($user_id !== $expense->payer) {
                        Expense::updateBalances($expense, $user_id, $value);
                    }
                }
            }
        } else if ((int)$expense_data['expense_type_id'] === ExpenseType::PERCENTAGE) {
        } else if ((int)$expense_data['expense_type_id'] === ExpenseType::SHARE) {
        } else if ((int)$expense_data['expense_type_id'] === ExpenseType::ADJUSTMENT) {
        } else if ((int)$expense_data['expense_type_id'] === ExpenseType::REIMBURSEMENT) {
            $participants = array_map('intval', $request->input('split-reimbursement-user', []));

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

                // Update the balances between the expense payer and participant
                if ($participants[$i] !== $expense->payer) {
                    Expense::updateBalances($expense, $participants[$i], $expense_participant->share);
                }

                $remaining_amount -= $amount_per_participant;
            }
        } else if ((int)$expense_data['expense_type_id'] === ExpenseType::ITEMIZED) {
        }

        // Send Expense Notifications
        $expense->sendExpenseNotifications();

        return Redirect::route('expenses.show', $expense->id)->with('status', 'expense-created');
    }

    /**
     * Displays the Expense page.
     */
    public function show($expense_id): View
    {
        $current_user = auth()->user();
        $expense = Expense::where('id', $expense_id)->first();

        // Get formatted dates and times
        $expense->formatted_created_date = Carbon::parse($expense->created_at)->diffForHumans();
        $expense->created_date = Carbon::parse($expense->created_at)->format('M d, Y');
        $expense->created_time = Carbon::parse($expense->created_at)->setTimezone(self::TIMEZONE)->format('g:i a');
        $expense->formatted_updated_date = Carbon::parse($expense->updated_at)->diffForHumans();
        $expense->updated_date = Carbon::parse($expense->updated_at)->format('M d, Y');
        $expense->updated_time = Carbon::parse($expense->updated_at)->setTimezone(self::TIMEZONE)->format('g:i a');

        // Get the creator and payer of the Expense
        $expense->creator_user = User::where('id', $expense->creator)->first();
        $expense->payer_user = User::where('id', $expense->payer)->first();

        $expense->is_reimbursement = $expense->expense_type_id === ExpenseType::REIMBURSEMENT;

        $participants = ExpenseParticipant::where('expense_id', $expense->id)
            ->join('users', 'expense_participants.user_id', 'users.id')
            ->select('users.*', 'expense_participants.share')
            ->orderByRaw("
                CASE
                    WHEN users.id = ? THEN 0
                    ELSE 1
                END, users.username ASC
            ", [$current_user->id])
            ->get();

        // If this Expense is a Payment, display the Payment screen rather than the Expense screen
        if ($expense->expense_type_id === -5) { // TODO: Update this with the payment type
            return view('payments.show', [
                'expense' => $expense,
                'participant' => $participants[0],
            ]);
        } else {
            return view('expenses.show', [
                'expense' => $expense,
                'participants' => $participants,
            ]);
        }
    }

    /**
     * Displays the update Expense form.
     */
    public function edit(Expense $expense): View
    {
        $current_user = auth()->user();

        $groups = $current_user->groups()
            ->orderByRaw("
                CASE
                    WHEN groups.id = ? THEN 0
                    ELSE 1
                END, groups.name ASC
            ", [Group::DEFAULT_GROUP])
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

        // Undo the Balance adjustments from the initial state of the expense
        $expense->undoBalanceAdjustments();

        // Update the Expense

        $expense_data = [
            'name' => $expense_validated['expense-name'],
            'amount' => $expense_validated['expense-amount'],
            'payer' => $expense_validated['expense-paid'],
            'group_id' => $expense_validated['expense-group'], // TODO: Remove this when group_id is removed from expenses table
            'expense_type_id' => $expense_validated['expense-split'],
            /*'category_id' => $expense_validated['expense-category'],*/
            'note' => $expense_validated['expense-note'],
            'date' => $expense_validated['expense-date'],
            'updator' => auth()->user()->id,
        ];

        $expense->update($expense_data);

        // Update the expense group
        ExpenseGroup::where('expense_id', $expense->id)->update([
            'group_id' => $expense_validated['expense-group'],
        ]);

        // Update the ExpensePartcipants and Balances

        // Keep track of updated participants to remove old participants later
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

                // Update the group Balances between the expense payer and participant
                if ($participants[$i] !== $expense->payer) {
                    Expense::updateBalances($expense, $participants[$i], $expense_participant->share);
                }

                $remaining_amount -= $amount_per_participant;
            }
        } else if ((int)$expense_data['expense_type_id'] === ExpenseType::AMOUNT) {
            foreach ($request->all() as $key => $value) {
                if (Str::startsWith($key, 'split-amount-item-') && (float)$value != 0) {
                    $user_id = (int)Str::after($key, 'split-amount-item-');

                    $updated_participants[] = $user_id;

                    $expense_participant = ExpenseParticipant::updateOrCreate(
                        [
                            'expense_id' => $expense->id,
                            'user_id' => $user_id
                        ],
                        [
                            'share' => $value,
                            'percentage' => null,
                            'shares' => null,
                            'adjustment' => null,
                            'is_settled' => 0,
                        ]
                    );

                    // Update the group Balances between the expense payer and participant
                    if ($user_id !== $expense->payer) {
                        Expense::updateBalances($expense, $user_id, $value);
                    }
                }
            }
        } else if ((int)$expense_data['expense_type_id'] === ExpenseType::PERCENTAGE) {
        } else if ((int)$expense_data['expense_type_id'] === ExpenseType::SHARE) {
        } else if ((int)$expense_data['expense_type_id'] === ExpenseType::ADJUSTMENT) {
        } else if ((int)$expense_data['expense_type_id'] === ExpenseType::REIMBURSEMENT) {
            $participants = array_map('intval', $request->input('split-reimbursement-user', []));
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

                // Update the balances between the expense payer and participant
                if ($participants[$i] !== $expense->payer) {
                    Expense::updateBalances($expense, $participants[$i], $expense_participant->share);
                }

                $remaining_amount -= $amount_per_participant;
            }
        } else if ((int)$expense_data['expense_type_id'] === ExpenseType::ITEMIZED) {
        }

        // Delete any old participants who were removed from the expense

        $expense_participants = ExpenseParticipant::where('expense_id', $expense->id)->get();

        foreach ($expense_participants as $expense_participant) {
            if (!in_array($expense_participant->user_id, $updated_participants)) {
                $expense_participant->delete();
            }
        }

        // Update the Expense's "updated_at" timestamp in case only the ExpenseParticipants were updated,
        // and not the Expense itself
        $expense->touch();

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
        $group_id = (int)$request->input('group_id');
        $current_user = auth()->user();

        /*$users_base_query = auth()->user()->friends()
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
        $users->setBindings($new_bindings);*/

        $users = User::whereHas('groups', function ($query) use ($group_id) {
                $query->where('groups.id', $group_id);
            })
            ->where(function ($query) use ($search_string) {
                $query->whereRaw('users.username LIKE ?', ["%$search_string%"])
                    ->orWhereRaw('users.email LIKE ?', ["%$search_string%"]);
            });

        if ($group_id === Group::DEFAULT_GROUP) { // Restrict search results to only the current user's friends in this case
            $searchable_group_members = $current_user->friends()->pluck('id')->toArray();
            array_push($searchable_group_members, $current_user->id);
            $users = $users->whereIn('id', $searchable_group_members);
        }

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
                //->join('groups', 'expenses.group_id', 'groups.id')
                ->where(function ($query) use ($search_string) {
                    $query->whereRaw('participant_users.username LIKE ?', ["%$search_string%"])
                        ->orWhereRaw('payer_users.username LIKE ?', ["%$search_string%"])
                        //->orWhereRaw('groups.name LIKE ?', ["%$search_string%"])
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

            $current_user_share = ExpenseParticipant::where('expense_id', $expense->id)
                ->where('user_id', $current_user->id)
                ->value('share');

            $expense->lent = number_format($expense->amount - $current_user_share, 2);
            $expense->borrowed = number_format($current_user_share, 2);
            $expense->amount = number_format($expense->amount, 2);

            $expense->group = Group::find($expense->group_id);

            $expense->is_reimbursement = $expense->expense_type_id === ExpenseType::REIMBURSEMENT;
            $expense->is_settle_all_balances = $expense->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES;
            $expense->is_payment = $expense->expense_type_id === ExpenseType::PAYMENT;
            $expense->payee = $expense->is_payment ? $expense->participants()->first() : null;

            return $expense;
        });

        return $expenses;
    }
}
