<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExpenseRequest;
use App\Models\Category;
use App\Models\CategoryGroup;
use App\Models\Expense;
use App\Models\ExpenseParticipant;
use App\Models\ExpenseType;
use App\Models\Group;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ExpensesController extends Controller
{
    const TIMEZONE = 'America/Toronto'; // TODO: make this a user setting

    /**
     * Displays the user's expenses list.
     */
    public function index(): View
    {
        return view('expenses.expenses-list');
    }

    /**
     * Paginates the user's expenses, with an optional search query to filter.
     */
    public function getExpenses(Request $request): JsonResponse
    {
        $current_user = auth()->user();

        $search_query = $request->input('query');

        $expenses = $current_user->expenses();

        if ($search_query) {
            $expenses = $expenses->join('expense_participants AS ep', 'expenses.id', 'ep.expense_id')
                ->join('users AS participant_users', 'ep.user_id', 'participant_users.id')
                ->join('users AS payer_users', 'expenses.payer', 'payer_users.id')
                ->where(function ($query) use ($search_query) {
                    $query->whereRaw('participant_users.username LIKE ?', ["%$search_query%"])
                        ->orWhereRaw('payer_users.username LIKE ?', ["%$search_query%"])
                        ->orWhereRaw('expenses.name LIKE ?', ["%$search_query%"])
                        ->orWhereRaw('expenses.amount LIKE ?', ["$search_query%"])
                        ->orWhere('expenses.amount', $search_query);
                        /*->orWhereHas('groups', function ($query) use ($search_query) {
                            $query->whereRaw('groups.name LIKE ?', ["%$search_query%"]);
                        });*/
                });
        }

        $expenses = $expenses->orderBy('date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        $is_last_page = !$expenses->hasMorePages();
        $current_page = $expenses->currentPage();

        $expenses = $this->augmentExpenses($expenses);

        $html = view('expenses.partials.expenses', [
            'expenses' => $expenses,
        ])->render();

        return response()->json([
            'html' => $html,
            'is_last_page' => $is_last_page,
            'current_page' => $current_page,
        ]);
    }

    /**
     * Displays the create Expense form.
     */
    public function create(Request $request): View
    {
        $current_user = $request->user();

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
            $group->group_members = $group->members()
                ->wherePivot('is_active', true)
                ->orderByRaw("
                    CASE
                        WHEN users.id = ? THEN 0
                        ELSE 1
                    END, users.username ASC
                ", [$current_user->id])
                ->get();
        }

        $category_groups = [
            'groups' => CategoryGroup::whereNot('id', CategoryGroup::OTHER)->orderBy('group', 'ASC')->get(),
            'other_group' => CategoryGroup::find(CategoryGroup::OTHER)
        ];

        foreach ($category_groups['groups'] as $category_group) {
            $category_group->categories = Category::where('category_group_id', $category_group->id)
                ->whereNotIn('id', Category::OTHER_CATEGORY_IDS)
                ->orderBy('category', 'ASC')
                ->get();

            $category_group->other_category = Category::where('category_group_id', $category_group->id)
                ->whereIn('id', Category::OTHER_CATEGORY_IDS)
                ->first();
        }

        $category_groups['other_group']->categories = Category::where('category_group_id', $category_groups['other_group']->id)
            ->whereNot('id', Category::PAYMENT_CATEGORY)
            ->orderBy('category', 'ASC')
            ->get();

        return view('expenses.create', [
            'expense' => null,
            'groups' => $groups,
            'default_group' => $default_group,
            'today' => $today,
            'formatted_today' => $formatted_today,
            'default_expense_type' => $default_expense_type,
            'expense_type_names' => $expense_type_names,
            'expense_type_ids' => $expense_type_ids,
            'current_user' => $current_user,
            'group' => $group,
            'friend' => $friend,
            'category_groups' => $category_groups,
            'default_category_id' => Category::DEFAULT_CATEGORY
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
            'expense_type_id' => $expense_validated['expense-split'],
            'category_id' => $expense_validated['expense-category'],
            'note' => $expense_validated['expense-note'],
            'date' => $expense_validated['expense-date'],
            'creator' => $current_user->id,
            'updator' => $current_user->id,
        ];

        $expense = Expense::create($expense_data);

        // Add the expense group
        $expense->groups()->attach($expense_validated['expense-group']);

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
     * Displays the expense page.
     */
    public function show($expense_id)
    {
        $current_user = auth()->user();
        $expense = Expense::find($expense_id);

        // Handle user trying to view a payment as an expense
        if ($expense->expense_type_id === ExpenseType::PAYMENT || $expense->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES) {
            return Redirect::route('payments.show', $expense_id);
        }

        // Get formatted dates and times
        $expense->formatted_created_date = Carbon::parse($expense->created_at)->diffForHumans();
        $expense->created_date = Carbon::parse($expense->created_at)->format('M d, Y');
        $expense->created_time = Carbon::parse($expense->created_at)->setTimezone(self::TIMEZONE)->format('g:i a');
        $expense->formatted_updated_date = Carbon::parse($expense->updated_at)->diffForHumans();
        $expense->updated_date = Carbon::parse($expense->updated_at)->format('M d, Y');
        $expense->updated_time = Carbon::parse($expense->updated_at)->setTimezone(self::TIMEZONE)->format('g:i a');
        $expense->formatted_date = Carbon::parse($expense->date)->format('M d, Y');

        // Get the creator, updator, and payer of the expense
        $expense->creator_user = User::find($expense->creator);
        $expense->updator_user = User::find($expense->updator);
        $expense->payer_user = User::find($expense->payer);

        $expense->is_reimbursement = $expense->expense_type_id === ExpenseType::REIMBURSEMENT;

        $expense->group = $expense->groups->first();

        $expense->category = $expense->category()->first();
        $expense->category->category_group = $expense->category->categoryGroup()->first();

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

        $expense_images = $expense->images()
            ->orderBy('created_at', 'ASC')
            ->get();

        $category_groups = [
            'groups' => CategoryGroup::whereNot('id', CategoryGroup::OTHER)->orderBy('group', 'ASC')->get(),
            'other_group' => CategoryGroup::find(CategoryGroup::OTHER)
        ];

        foreach ($category_groups['groups'] as $category_group) {
            $category_group->categories = Category::where('category_group_id', $category_group->id)
                ->whereNotIn('id', Category::OTHER_CATEGORY_IDS)
                ->orderBy('category', 'ASC')
                ->get();

            $category_group->other_category = Category::where('category_group_id', $category_group->id)
                ->whereIn('id', Category::OTHER_CATEGORY_IDS)
                ->first();
        }

        $category_groups['other_group']->categories = Category::where('category_group_id', $category_groups['other_group']->id)
            ->whereNot('id', Category::PAYMENT_CATEGORY)
            ->orderBy('category', 'ASC')
            ->get();

        return view('expenses.show', [
            'expense' => $expense,
            'participants' => $participants,
            'max_images_allowed' => Expense::MAX_IMAGES_ALLOWED,
            'expense_images' => $expense_images,
            'category_groups' => $category_groups,
        ]);
    }

    /**
     * Displays the update expense form.
     */
    public function edit(Request $request, Expense $expense)
    {
        // Handle user trying to edit a payment as an expense
        if ($expense->expense_type_id === ExpenseType::PAYMENT || $expense->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES) {
            return Redirect::route('payments.edit', $expense);
        }

        $current_user = $request->user();

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

        $category_groups = [
            'groups' => CategoryGroup::whereNot('id', CategoryGroup::OTHER)->orderBy('group', 'ASC')->get(),
            'other_group' => CategoryGroup::find(CategoryGroup::OTHER)
        ];

        foreach ($category_groups['groups'] as $category_group) {
            $category_group->categories = Category::where('category_group_id', $category_group->id)
                ->whereNotIn('id', Category::OTHER_CATEGORY_IDS)
                ->orderBy('category', 'ASC')
                ->get();

            $category_group->other_category = Category::where('category_group_id', $category_group->id)
                ->whereIn('id', Category::OTHER_CATEGORY_IDS)
                ->first();
        }

        $category_groups['other_group']->categories = Category::where('category_group_id', $category_groups['other_group']->id)
            ->whereNot('id', Category::PAYMENT_CATEGORY)
            ->orderBy('category', 'ASC')
            ->get();

        $category = $expense->category()->first();
        $expense->category = [
            'icon_class' => $category->icon_class,
            'colour_class' => $category->categoryGroup()->first()->colour_class,
        ];

        return view('expenses.edit', [
            'expense' => $expense,
            'groups' => $groups,
            'default_group' => $default_group,
            'today' => $today,
            'formatted_today' => $formatted_today,
            'default_expense_type' => $default_expense_type,
            'expense_type_names' => $expense_type_names,
            'expense_type_ids' => $expense_type_ids,
            'current_user' => $current_user,
            'category_groups' => $category_groups,
            'default_category_id' => Category::DEFAULT_CATEGORY,
        ]);
    }

    /**
     * Updates the expense details.
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
            'expense_type_id' => $expense_validated['expense-split'],
            'category_id' => $expense_validated['expense-category'],
            'note' => $expense_validated['expense-note'],
            'date' => $expense_validated['expense-date'],
            'updator' => auth()->user()->id,
        ];

        $expense->update($expense_data);

        // Update the expense group
        $expense->groups()->sync([$expense_validated['expense-group']]);
        $expense->load('groups'); // Refresh the relationship to avoid problems with old cached data

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

        // Update the expense's "updated_at" timestamp in case only the expense participants were updated,
        // and not the expense itself
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
            ", [$current_user->id])
            ->get();

        foreach ($users as $user) {
            $user->profile_image_url = $user->getProfileImageUrlAttribute();
        }

        $groups = Group::whereHas('members', function ($query) use ($current_user) {
                $query->where('users.id', $current_user->id);
            })
            ->whereNot('id', Group::DEFAULT_GROUP)
            ->whereRaw('name LIKE ?', ["%$search_string%"])
            ->orderBy('name', 'ASC')
            ->get();

        foreach ($groups as $group) {
            $group->group_image_url = $group->getGroupImageUrlAttribute();
            $group->group_members = $group->members()
                ->orderByRaw("
                    CASE
                        WHEN users.id = ? THEN 0
                        ELSE 1
                    END, users.username ASC
                ", [$current_user->id])
                ->get();

            foreach ($group->group_members as $member) {
                $member->profile_image_url = $member->getProfileImageUrlAttribute();
            }
        }

        $response = [
            'groups' => $groups,
            'users' => $users,
        ];

        return response()->json($response);
    }
    
    /**
     * Returns the members and "is default" status of the selected group.
     */
    public function getExpenseGroupDetails(Request $request): JsonResponse
    {
        $group_id = (int)$request->input('group_id');

        $group_members = Group::find($group_id)->members()->pluck('users.id')->toArray();
        $group_is_default = $group_id === Group::DEFAULT_GROUP;

        return response()->json([
            'group_members' => $group_members,
            'group_is_default' => $group_is_default,
            'current_user_id' => $request->user()->id,
        ]);
    }

    /**
     * Updates the expenses.note field.
     */
    public function updateNote(Request $request, Expense $expense)
    {
        $request->validate([
            'expense-note' => ['nullable', 'string', 'max:65535'],
        ]);

        $expense_note_input = $request->input('expense-note');

        $expense->note = $expense_note_input;
        $expense->updator = $request->user()->id;
        $expense->save();
        $expense->touch(); // Make sure timestamp is updated if the updator doesn't change

        return Redirect::route('expenses.show', $expense->id)->with('status', 'expense-note-updated');
    }

    /**
     * Updates the expenses.category_id field.
     */
    public function updateCategory(Request $request, Expense $expense)
    {
        $request->validate([
            'expense-category' => ['required', 'int', Rule::exists('categories', 'id')],
        ]);

        $category_id = (int)$request->input('expense-category');

        $expense->category_id = $category_id;
        $expense->updator = $request->user()->id;
        $expense->save();
        $expense->touch(); // Make sure timestamp is updated if the updator doesn't change

        return Redirect::route('expenses.show', $expense->id)->with('status', 'expense-category-updated');
    }

    /**
     * Adds formatted dates/times, current User's lent/borrowed amounts, and group information to the expenses.
     */
    protected function augmentExpenses($expenses)
    {
        $current_user = auth()->user();

        $expenses = $expenses->map(function ($expense) use ($current_user) {
            $expense->payer_user = User::where('id', $expense->payer)->first();

            $expense->formatted_date = Carbon::parse($expense->date)->isoFormat('MMM DD, YYYY');

            $current_user_share = ExpenseParticipant::where('expense_id', $expense->id)
                ->where('user_id', $current_user->id)
                ->value('share');

            $expense->lent = number_format($expense->amount - $current_user_share, 2);
            $expense->borrowed = number_format($current_user_share, 2);
            $expense->amount = number_format($expense->amount, 2);

            $expense->group = $expense->groups->first();

            $category = $expense->category()->first();
            $expense->category = [
                'icon_class' => $category->icon_class,
                'colour_class' => $category->categoryGroup()->first()->colour_class,
            ];

            $expense->is_reimbursement = $expense->expense_type_id === ExpenseType::REIMBURSEMENT;
            $expense->is_settle_all_balances = $expense->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES;
            $expense->is_payment = ($expense->expense_type_id === ExpenseType::PAYMENT || $expense->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES);
            $expense->payee = $expense->is_payment ? $expense->participants()->first() : null;

            return $expense;
        });

        return $expenses;
    }
}
