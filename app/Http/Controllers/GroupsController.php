<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGroupRequest;
use App\Models\Balance;
use App\Models\Expense;
use App\Models\ExpenseParticipant;
use App\Models\ExpenseType;
use App\Models\Friend;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\GroupMember;
use App\Models\Notification as ModelsNotification;
use App\Models\NotificationAttribute;
use App\Models\NotificationType;
use App\Models\User;
use App\Notifications\GroupInviteNotification;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GroupsController extends Controller
{
    const TIMEZONE = 'America/Toronto'; // TODO: make this a user setting
    const MAX_BALANCES_SHOWN = 3;
    const BLANACES_LIMITED = 2;

    /**
     * Displays the user's groups list.
     */
    public function index(): View
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

        $groups = $this->augmentGroups($groups);

        return view('groups.groups-list', [
            'groups' => $groups,
        ]);
    }

    /**
     * Displays the create Group form.
     */
    public function create(): View
    {
        return view('groups.create', ['group' => null]);
    }

    /**
     * Saves the new Group.
     */
    public function store(CreateGroupRequest $request): RedirectResponse
    {
        $current_user = auth()->user();

        $group = Group::create($request->validated());

        $group->owner = $current_user->id;
        $group->save();

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $current_user->id,
        ]);

        $group->createDefaultGroupImage();

        return Redirect::route('groups.settings', $group)->with('status', 'group-created');
    }

    /**
     * Displays the group.
     */
    public function show($group_id): View
    {
        $current_user = auth()->user();

        $group = Group::where('id', $group_id)->first();
        $group->is_default = $group->id === Group::DEFAULT_GROUP;

        $expenses = $group->expenses();

        if ($group->id === Group::DEFAULT_GROUP) {
            $expenses = $expenses->where(function ($query) use ($current_user) {
                    $query->where('expenses.payer', $current_user->id)
                        ->orWhereHas('participants', function ($query) use ($current_user) {
                            $query->where('users.id', $current_user->id);
                        });
                });
        }

        $expenses = $expenses->orderBy('date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->get();

        $expenses = $this->augmentExpenses($expenses);

        $overall_balance = Balance::where('group_id', $group->id)
            ->where('user_id', $current_user->id)
            ->sum('balance');

        if ($overall_balance == 0) {
            $group->is_settled_up = !Balance::where('user_id', $current_user->id)
                ->where('group_id', $group->id)
                ->whereNot('balance', 0)
                ->exists();
        } else {
            $group->is_settled_up = false;
        }

        $balances_count = Balance::where('balances.group_id', $group->id)
            ->where('balances.user_id', $current_user->id)
            ->count();

        $balances_shown_limit = static::MAX_BALANCES_SHOWN;

        if ($balances_count > static::MAX_BALANCES_SHOWN) {
            $balances_shown_limit = static::BLANACES_LIMITED;
            $hidden_balances_count = $balances_count - $balances_shown_limit;
        } else {
            $hidden_balances_count = 0;
        }

        $individual_balances = Balance::join('users', 'balances.friend', 'users.id')
            ->select('balances.balance', 'users.username')
            ->where('balances.group_id', $group->id)
            ->where('balances.user_id', $current_user->id)
            ->limit($balances_shown_limit)
            ->orderByRaw("
                CASE 
                    WHEN balance = 0 THEN 1
                    ELSE 0
                END, 
                balances.balance ASC
            ")
            ->get();

        return view('groups.show', [
            'group' => $group,
            'expenses' => $expenses,
            'overall_balance' => $overall_balance,
            'individual_balances' => $individual_balances,
            'hidden_balances_count' => $hidden_balances_count,
        ]);
    }

    /**
     * Displays the group settings.
     */
    public function settings(Group $group): View
    {
        $current_user = auth()->user();

        $group_members = $group->members()
            ->orderByRaw("
                CASE
                    WHEN users.id = ? THEN 0
                    ELSE 1
                END, users.username ASC
            ", [$current_user->id])
            ->get();

        $friends = $current_user->friends()->orderBy('username', 'asc')->get();

        return view('groups.group-settings', [
            'group' => $group,
            'group_members' => $group_members,
            'friends' => $friends,
        ]);
    }

    /**
     * Updates the group details.
     */
    public function update(CreateGroupRequest $request, Group $group): RedirectResponse
    {
        $group->update($request->validated());

        return Redirect::route('groups.settings', $group->id)->with('status', 'group-updated');
    }

    /**
     * Filters the friends list on the "Add Members" modal.
     */
    public function searchFriendsToInvite(Request $request, Group $group): View
    {
        $search_string = $request->input('search_string');

        $friends = auth()->user()->friends()
            ->where(function ($query) use ($search_string) {
                $query->whereRaw('users.username LIKE ?', ["%$search_string%"])
                    ->orWhereRaw('users.email LIKE ?', ["%$search_string%"]);
            })
            ->orderBy('username', 'asc')
            ->get();

        return view('groups.partials.friends-to-invite', [
            'group' => $group,
            'friends' => $friends,
        ]);
    }

    /**
     * Send an invite to a group.
     */
    public function invite(Request $request, Group $group): JsonResponse
    {
        $inviter = $request->user();

        $user_emails = $request->input('emails');

        // TODO: email validation fix
        $rules = [
            'email' => ['string', 'lowercase', 'email', 'max:255']
        ];

        $invite_errors = 0;

        foreach ($user_emails as $email) {
            $validator = Validator::make(['email' => $email], $rules);

            if ($validator->fails()) {
                return back()->withErrors($validator);
            }

            $existing_user = User::where('email', $email)->first();

            if ($existing_user) {
                // Check if the user is already in the group or if a pending invite exists.

                $self_request = $existing_user->id === $inviter->id;

                if ($self_request) {
                    $invite_errors++;
                    continue;
                }

                $existing_member = in_array($existing_user->id, $group->members()->pluck('users.id')->toArray());

                if ($existing_member) {
                    $invite_errors++;
                    continue;
                }

                $existing_group_invite = ModelsNotification::where('notification_type_id', NotificationType::INVITED_TO_GROUP)
                    ->where('creator', $inviter->id)
                    ->where('recipient', $existing_user->id)
                    ->exists();

                if ($existing_group_invite) {
                    $invite_errors++;
                    continue;
                }

                // Create Group Invite notifications for both parties

                $inviter_notification = ModelsNotification::create([
                    'notification_type_id' => NotificationType::INVITED_TO_GROUP,
                    'creator' => $inviter->id,
                    'sender' => $existing_user->id,
                    'recipient' => $inviter->id,
                ]);

                NotificationAttribute::create([
                    'notification_id' => $inviter_notification->id,
                    'group_id' => $group->id,
                ]);

                $invitee_notification = ModelsNotification::create([
                    'notification_type_id' => NotificationType::INVITED_TO_GROUP,
                    'creator' => $inviter->id,
                    'sender' => $inviter->id,
                    'recipient' => $existing_user->id,
                    'requires_action' => 1,
                ]);

                NotificationAttribute::create([
                    'notification_id' => $invitee_notification->id,
                    'group_id' => $group->id,
                ]);
            } else {
                // Send an invite to app email

                do {
                    $token = Str::random(20);
                } while (GroupInvite::where('token', $token)->first());

                GroupInvite::create([
                    'token' => $token,
                    'email' => $email,
                    'group_id' => $group->id,
                ]);

                $url = URL::temporarySignedRoute(
                    'register.from-group-invite', now()->addMinutes(Config::get('auth.invite.expire', 120)), ['token' => $token]
                );

                Notification::route('mail', $email)->notify(new GroupInviteNotification($url, $inviter->username, $group->name));
            }
        }

        if ($invite_errors === 0) {
            Session::flash('status', 'invite-sent');
        } else if ($invite_errors === count($user_emails)) {
            Session::flash('status', 'invite-errors');
        } else {
            Session::flash('status', 'invite-sent-with-errors');
        }

        return response()->json([
            'message' => 'Invite sent successfully!',
            'redirect' => route('groups.settings', $group),
        ]);
    }

    /**
     * Accept a group invite.
     */
    public function accept(Request $request): JsonResponse
    {
        $invitee_notification_id = $request->input('notification_id');
        $invitee_notification = ModelsNotification::find($invitee_notification_id);

        $group_id = $request->input('group_id');

        $invitee_id = $invitee_notification->recipient;

        // Update the invitee's notification
        $invitee_notification->update([
            'notification_type_id' => NotificationType::JOINED_GROUP,
            'creator' => $invitee_id,
            'requires_action' => 0,
        ]);

        // Add invitee to group
        GroupMember::firstOrCreate([
            'group_id' => $group_id,
            'user_id' => $invitee_id,
        ]);

        return response()->json([
            'message' => 'Added to group!',
        ]);
    }

    /**
     * Reject a group invite.
     */
    public function reject(Request $request): JsonResponse
    {
        // Delete inviter's and invitee's notifications

        $invitee_notification_id = $request->input('notification_id');
        $invitee_notification = ModelsNotification::find($invitee_notification_id);

        $inviter_id = $invitee_notification->sender;
        $invitee_id = $invitee_notification->recipient;

        $inviter_notification = ModelsNotification::where('notification_type_id', NotificationType::INVITED_TO_GROUP)
            ->where('sender', $invitee_id)
            ->where('recipient', $inviter_id)
            ->first();

        $invitee_notification->delete();

        if ($inviter_notification) {
            $inviter_notification->delete();
        }

        return response()->json([
            'message' => 'Friend request denied!',
        ]);
    }

    /**
     * Remove a member from the group.
     */
    public function removeMember(Request $request, Group $group):JsonResponse
    {
        $member = User::find($request->input('member_id'));

        // Update expenses to default user and delete balances
        $this->updateExpensesOnGroupExit($group, $member);

        GroupMember::where('group_id', $group->id)->where('user_id', $member->id)->delete();

        Session::flash('status', 'member-removed');

        return response()->json([
            'message' => 'Member removed successfully!',
            'redirect' => route('groups.settings', $group),
        ]);
    }

    /**
     * Removes the current user from the group.
     */
    public function leaveGroup(Request $request, Group $group)
    {
        $current_user = $request->user();

        if ($group->owner === $current_user->id) {
            // Group ownership needs to change
            if ($group->members()->count() > 1 ) {
                // Group owner can be assigned to another member

                $new_owner = GroupMember::where('group_id', $group->id)
                    ->whereNot('user_id', $current_user->id)
                    ->orderBy('created_at', 'asc')
                    ->pluck('user_id')
                    ->first();

                $group->owner = $new_owner;
                $group->save();

                // Send Group members a "user left group" notification
                foreach ($group->members()->pluck('users.id')->toArray() as $member_id) {
                    $left_group_notification = ModelsNotification::create([
                        'notification_type_id' => NotificationType::LEFT_GROUP,
                        'creator' => $current_user->id,
                        'sender' => $current_user->id,
                        'recipient' => $member_id,
                    ]);

                    NotificationAttribute::create([
                        'notification_id' => $left_group_notification->id,
                        'group_id' => $group->id,
                    ]);
                }

                GroupMember::where('group_id', $group->id)->where('user_id', $current_user->id)->delete();
            } else {
                // Current user is the only member so the group is deleted

                // TODO: Create group deleted notification ?

                $group->delete();
            }
        } else {
            // Send group members a "user left group" notification
            foreach ($group->members()->pluck('users.id')->toArray() as $member_id) {
                $left_group_notification = ModelsNotification::create([
                    'notification_type_id' => NotificationType::LEFT_GROUP,
                    'creator' => $current_user->id,
                    'sender' => $current_user->id,
                    'recipient' => $member_id,
                ]);

                NotificationAttribute::create([
                    'notification_id' => $left_group_notification->id,
                    'group_id' => $group->id,
                ]);
            }

            GroupMember::where('group_id', $group->id)->where('user_id', $current_user->id)->delete();
        }

        // Update expenses to default user and delete balances
        $this->updateExpensesOnGroupExit($group, $current_user);

        Session::flash('status', 'left-group');

        return response()->json([
            'message' => 'Left group successfully!',
            'redirect' => route('groups'),
        ]);
    }

    /**
     * Deletes the group.
     */
    public function destroy(Request $request, Group $group): JsonResponse
    {
        $group->deleteGroupImage();

        $group->delete();

        Session::flash('status', 'group-deleted');

        return response()->json([
            'message' => 'Group deleted successfully!',
            'redirect' => route('groups'),
        ]);
    }

    /**
     * Filters the groups list.
     */
    public function search(Request $request): View
    {
        $current_user = auth()->user();

        $search_string = $request->input('search_string');

        $groups_query = $current_user->groups();

        if ($search_string) {
            $groups_query = $groups_query->select('groups.*')
                ->join('group_members AS gm', 'groups.id', 'gm.group_id')
                ->join('users', 'gm.user_id', 'users.id')
                ->where(function ($query) use ($search_string) {
                    $query->whereRaw('users.username LIKE ?', ["%$search_string%"])
                        ->orWhereRaw('groups.name LIKE ?', ["%$search_string%"]);
                })
                ->distinct();
        }

        $groups = $groups_query->orderByRaw("
            CASE
                WHEN groups.id = ? THEN 0
                ELSE 1
            END, groups.name ASC
        ", [Group::DEFAULT_GROUP])
        ->get();

        $groups = $this->augmentGroups($groups);

        return view('groups.partials.groups', ['groups' => $groups]);
    }

    /**
     * Change all of $user's expenses in $group to the default DivvyDime user,
     * and delete all their group balances.
     */
    protected function updateExpensesOnGroupExit($group, $user)
    {
        // Update all of the user's group expenses to the default DivvyDime user

        // Expenses (payer)
        Expense::whereHas('groups', function ($query) use ($group) {
                $query->where('groups.id', $group->id);
            })
            ->where('payer', $user->id)
            ->update([
                'payer' => User::DEFAULT_USER,
            ], ['timestamps' => false]);

        // Expenses (creator)
        Expense::whereHas('groups', function ($query) use ($group) {
                $query->where('groups.id', $group->id);
            })
            ->where('creator', $user->id)
            ->update([
                'creator' => User::DEFAULT_USER,
            ], ['timestamps' => false]);

        // Expenses (updator)
        Expense::whereHas('groups', function ($query) use ($group) {
                $query->where('groups.id', $group->id);
            })
            ->where('updator', $user->id)
            ->update([
                'updator' => User::DEFAULT_USER,
            ], ['timestamps' => false]);

        // Expenses (participant)
        ExpenseParticipant::whereHas('expense.groups', function ($query) use ($group) {
                $query->where('groups.id', $group->id);
            })
            ->where('user_id', $user->id)
            ->update([
                'user_id' => User::DEFAULT_USER,
            ], ['timestamps' => false]);

        // Delete all of the user's group balances
        Balance::where('group_id', $group->id)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('friend', $user->id);
            })
            ->delete();
    }

    /**
     * Add additional information about default group and group balances
     * to the groups.
     */
    protected function augmentGroups($groups)
    {
        $groups = $groups->map(function ($group) {
            $group->is_default = $group->id === Group::DEFAULT_GROUP;

            $group->overall_balance = Balance::where('group_id', $group->id)
                ->where('user_id', auth()->user()->id)
                ->sum('balance');

            if ($group->overall_balance == 0) {
                $group->is_settled_up = !Balance::where('user_id', auth()->user()->id)
                    ->where('group_id', $group->id)
                    ->whereNot('balance', 0)
                    ->exists();
            } else {
                $group->is_settled_up = false;
            }

            return $group;
        });

        return $groups;
    }

    /**
     * Add additional information such as dates/times, lent/borrowed amounts, 
     * and group info to the expenses
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

            if ($expense->payer === $current_user->id) {
                $expense->lent = number_format($expense->amount - $current_user_share, 2);
            }
            if ($current_user_share) {
                $expense->borrowed = number_format($current_user_share, 2);
            }
            $expense->amount = number_format($expense->amount, 2);

            $expense->group = $expense->groups->first();

            $expense->is_reimbursement = $expense->expense_type_id === ExpenseType::REIMBURSEMENT;
            $expense->is_settle_all_balances = $expense->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES;
            $expense->is_payment = ($expense->expense_type_id === ExpenseType::PAYMENT || $expense->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES);
            $expense->payee = $expense->is_payment ? $expense->participants()->first() : null;

            return $expense;
        });

        return $expenses;
    }
}
