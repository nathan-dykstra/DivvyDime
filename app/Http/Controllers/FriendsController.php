<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\ExpenseParticipant;
use App\Models\ExpenseType;
use App\Models\Friend;
use App\Models\Group;
use App\Models\Invite;
use App\Models\Notification as ModelsNotification;
use App\Models\NotificationType;
use App\Models\User;
use App\Notifications\InviteNotification;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class FriendsController extends Controller
{
    const TIMEZONE = 'America/Toronto'; // TODO: make this a user setting

    /**
     * Display the user's friends.
     */
    public function index(): View
    {
        return view('friends.friends-list');
    }

    /**
     * Paginates the user's friends, with an optional search query to filter.
     */
    public function getFriends(Request $request): JsonResponse
    {
        $current_user = auth()->user();

        $search_query = $request->input('query');

        $friends = $current_user->friends();

        if ($search_query) {
            $friends = $friends->where(function ($query) use ($search_query) {
                $query->whereRaw('username LIKE ?', ["%$search_query%"])
                    ->orWhereRaw('email LIKE ?', ["%$search_query%"]);
                });
        }

        $friends = $friends->distinct()
            ->orderBy('username', 'ASC')
            ->paginate(20);

        $is_last_page = !$friends->hasMorePages();
        $current_page = $friends->currentPage();

        $friends = $this->augmentFriends($friends);

        $html = view('friends.partials.friends', [
            'friends' => $friends,
        ])->render();

        return response()->json([
            'html' => $html,
            'is_last_page' => $is_last_page,
            'current_page' => $current_page,
        ]);
    }

    /**
     * Displays a friend's profile.
     */
    public function show($friend_id): View
    {
        $current_user = auth()->user();

        $friend = User::where('id', $friend_id)->first();

        $overall_balance = Balance::where('user_id', $current_user->id)
            ->where('friend', $friend->id)
            ->sum('balance');

        if ($overall_balance == 0) {
            $friend->is_settled_up = !Balance::where('user_id', $current_user->id)
                ->where('friend', $friend->id)
                ->whereNot('balance', 0)
                ->exists();
        } else {
            $friend->is_settled_up = false;
        }

        $group_balances = Balance::select('groups.name', 'groups.id as group_id', 'balances.*')
            ->join('groups', 'balances.group_id', 'groups.id')
            ->where('balances.user_id', $current_user->id)
            ->where('balances.friend', $friend->id)
            ->orderByRaw("
                CASE 
                    WHEN balance = 0 THEN 1
                    ELSE 0
                END, 
                balances.balance ASC
            ")
            ->get();

        return view('friends.friend-profile', [
            'friend' => $friend,
            'overall_balance' => $overall_balance,
            'group_balances' => $group_balances,
        ]); 
    }

    /**
     * Paginates the friend's expenses, with an optional search query to filter.
     */
    public function getFriendExpenses(Request $request, $friend_id): JsonResponse
    {
        $current_user = $request->user();
        $friend = User::find($friend_id);

        $search_query = $request->input('query');

        $expenses = $friend->expenses()
            ->where(function ($query) use ($current_user, $friend) {
                $query->where(function ($query) use ($current_user, $friend) { // Expenses where current User paid and friend was a participant
                        $query->where('payer', $current_user->id)
                            ->whereHas('participants', function ($query) use ($friend) {
                                $query->where('users.id', $friend->id);
                            });
                    })
                    ->orWhere(function ($query) use ($current_user, $friend) { // Expenses where friend paid and current User was a participant
                        $query->where('payer', $friend->id)
                            ->whereHas('participants', function ($query) use ($current_user) {
                                $query->where('users.id', $current_user->id);
                            });
                    });
            });

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
                });
        }

        $expenses = $expenses->orderBy('date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        $is_last_page = !$expenses->hasMorePages();
        $current_page = $expenses->currentPage();

        $expenses = $this->augmentExpenses($expenses, $friend->id);

        $html = view('friends.partials.expenses', [
            'expenses' => $expenses,
        ])->render();

        return response()->json([
            'html' => $html,
            'is_last_page' => $is_last_page,
            'current_page' => $current_page,
        ]);
    }

    /**
     * Send a friend request.
     */
    public function invite(Request $request): RedirectResponse
    {
        $request->validateWithBag('friendInvite', [
            'friend_email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);

        $inviter = $request->user();

        $invitee = User::where('email', $request->input('friend_email'))->first();

        if ($invitee) {
            // Check if the users are already friends or if a pending request already exists

            $self_request = $invitee->id === $inviter->id;

            if ($self_request) {
                return Redirect::route('friends')->with('status', 'self-request');
            }

            $existing_friend = in_array($invitee->id, $inviter->friends()->pluck('users.id')->toArray());

            if ($existing_friend) {
                return Redirect::route('friends')->with('status', 'existing-friend');
            }

            $existing_friend_request = ModelsNotification::where('notification_type_id', NotificationType::FRIEND_REQUEST)
                ->where('creator', $inviter->id)
                ->exists();

            if ($existing_friend_request) {
                return Redirect::route('friends')->with('status', 'existing-request');
            }

            $pending_friend_request = ModelsNotification::where('notification_type_id', NotificationType::FRIEND_REQUEST)
                ->where('creator', $invitee->id)
                ->exists();

            if ($pending_friend_request) {
                return Redirect::route('friends')->with('status', 'pending-request');
            }

            // Create Friend Request notifications for both parties

            ModelsNotification::create([
                'notification_type_id' => NotificationType::FRIEND_REQUEST,
                'creator' => $inviter->id,
                'sender' => $invitee->id,
                'recipient' => $inviter->id,
            ]);

            ModelsNotification::create([
                'notification_type_id' => NotificationType::FRIEND_REQUEST,
                'creator' => $inviter->id,
                'sender' => $inviter->id,
                'recipient' => $invitee->id,
                'requires_action' => 1,
            ]);
        } else {
            // Send an invite to app email

            do {
                $token = Str::random(20);
            } while (Invite::where('token', $token)->first());

            Invite::create([
                'token' => $token,
                'email' => $request->input('friend_email'),
                'inviter' => $inviter->id,
            ]);

            $url = URL::temporarySignedRoute(
                'register.frominvite', now()->addMinutes(Config::get('auth.invite.expire', 120)), ['token' => $token]
            );

            Notification::route('mail', $request->input('friend_email'))->notify(new InviteNotification($url, $inviter->username));
        }

        return Redirect::route('friends')->with('status', 'invite-sent');
    }

    /**
     * Accept a friend request.
     */
    public function accept(Request $request, $notification_id): JsonResponse
    {
        $invitee_notification = ModelsNotification::where('id', $notification_id)->first();

        $inviter_id = $invitee_notification->sender;
        $invitee_id = $invitee_notification->recipient;

        // TODO(temp?)
        $inviter = User::find($inviter_id);
        $already_friends = $inviter->friends()->where('users.id', $invitee_id)->exists();

        if ($already_friends) {
            return response()->json([
                'message' => 'Users are already friends!',
            ]);
        }

        Friend::create([
            'user1_id' => $inviter_id,
            'user2_id' => $invitee_id,
        ]);

        // Update the inviter's and invitee's notifications

        $invitee_notification->update([
            'notification_type_id' => NotificationType::FRIEND_REQUEST_ACCEPTED,
            'creator' => $invitee_id,
            'requires_action' => 0,
        ]);

        ModelsNotification::updateorCreate(
            [
                'notification_type_id' => NotificationType::FRIEND_REQUEST,
                'creator' => $inviter_id,
                'sender' => $invitee_id,
                'recipient' => $inviter_id,
            ],
            [
                'notification_type_id' => NotificationType::FRIEND_REQUEST_ACCEPTED,
                'creator' => $invitee_id,
            ],
        );

        return response()->json([
            'message' => 'Friend request accepted!',
        ]);
    }

    /**
     * Deny a friend request.
     */
    public function deny(Request $request, $notification_id): JsonResponse
    {
        $invitee_notification = ModelsNotification::where('id', $notification_id)->first();

        $inviter_id = $invitee_notification->sender;
        $invitee_id = $invitee_notification->recipient;

        $inviter_notification = ModelsNotification::where('notification_type_id', NotificationType::FRIEND_REQUEST)
            ->where('sender', $invitee_id)
            ->where('recipient', $inviter_id)
            ->first();

        // Delete inviter's and invitee's notifications

        $invitee_notification->delete();

        if ($inviter_notification) {
            $inviter_notification->delete();
        }

        return response()->json([
            'message' => 'Friend request denied!',
        ]);
    }

    /**
     * Add balances information to the Friends.
     */
    protected function augmentFriends($friends)
    {
        $friends = $friends->map(function ($friend) {
            $friend->overall_balance = Balance::where('user_id', auth()->user()->id)
                ->where('friend', $friend->id)
                ->sum('balance');

            if ($friend->overall_balance == 0) {
                $friend->is_settled_up = !Balance::where('user_id', auth()->user()->id)
                    ->where('friend', $friend->id)
                    ->whereNot('balance', 0)
                    ->exists();
            } else {
                $friend->is_settled_up = false;
            }

            $friend->group_balances = Balance::select('groups.name', 'balances.*')
                ->join('groups', 'balances.group_id', 'groups.id')
                ->where('balances.user_id', auth()->user()->id)
                ->where('balances.friend', $friend->id)
                ->whereNot('balances.balance', 0)
                ->orderByRaw("
                    CASE
                        WHEN groups.id = ? THEN 0
                        ELSE 1
                    END, groups.name ASC
                ", [Group::DEFAULT_GROUP])
                ->get();

            return $friend;
        });

        return $friends;
    }

    protected function augmentExpenses($expenses, $friend_id)
    {
        $current_user = auth()->user();

        $expenses = $expenses->map(function ($expense) use ($current_user, $friend_id) {
            $expense->payer_user = User::where('id', $expense->payer)->first();

            $expense->formatted_date = Carbon::parse($expense->date)->isoFormat('MMM DD, YYYY');

            $current_user_share = ExpenseParticipant::where('expense_id', $expense->id)
                ->where('user_id', $current_user->id)
                ->value('share');

            $friend_share = ExpenseParticipant::where('expense_id', $expense->id)
                ->where('user_id', $friend_id)
                ->value('share');

            if ($expense->payer === $current_user->id) {
                $expense->lent = number_format($friend_share, 2);
            }
            if ($current_user_share) {
                $expense->borrowed = number_format($current_user_share, 2);
            }
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
