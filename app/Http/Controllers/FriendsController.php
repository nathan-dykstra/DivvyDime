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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $current_user = auth()->user();

        $friends = $current_user->friends()->orderBy('username', 'asc')->get();

        $friends = $this->augmentFriends($friends);

        return view('friends.friends-list', [
            'friends' => $friends,
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

        $group_balances = Balance::select('groups.name', 'groups.id as group_id', 'balances.*')
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
            })
            ->orderBy('date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->get();

        $expenses = $expenses->map(function ($expense) use ($current_user, $friend_id) {
            $expense->payer_user = User::where('id', $expense->payer)->first();

            $expense->formatted_date = Carbon::parse($expense->date)->diffForHumans();

            $expense->date = Carbon::parse($expense->date)->format('M d, Y');

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
            
            $expense->is_reimbursement = $expense->expense_type_id === ExpenseType::REIMBURSEMENT;
            $expense->is_settle_all_balances = $expense->expense_type_id === ExpenseType::SETTLE_ALL_BALANCES;
            $expense->is_payment = $expense->expense_type_id === ExpenseType::PAYMENT;
            $expense->payee = $expense->is_payment ? $expense->participants()->first() : null;

            return $expense;
        });

        return view('friends.friend-profile', [
            'friend' => $friend,
            'expenses' => $expenses,
            'overall_balance' => $overall_balance,
            'group_balances' => $group_balances,
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
                'register.frominvite', now()->addMinutes(300), ['token' => $token]
            );
    
            Notification::route('mail', $request->input('friend_email'))->notify(new InviteNotification($url, $inviter->username));
        }

        return Redirect::route('friends')->with('status', 'invite-sent');
    }

    /**
     * Accept a friend request.
     */
    public function accept(Request $request, $notification_id)
    {
        $invitee_notification = ModelsNotification::where('id', $notification_id)->first();

        $inviter_id = $invitee_notification->sender;
        $invitee_id = $invitee_notification->recipient;

        Friend::create([
            'user1_id' => $inviter_id,
            'user2_id' => $invitee_id,
        ]);

        // Update the inviter's and invitee's notifications

        $invitee_notification->update([
            'notification_type_id' => NotificationType::FRIEND_REQUEST_ACCEPTED,
            'creator' => $invitee_id
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
    public function deny(Request $request, $notification_id)
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
     * Filters the friends list in the Friends section.
     */
    public function search(Request $request): View
    {
        $current_user = auth()->user();
        $search_string = $request->input('search_string');

        $friend_ids = Friend::select('user2_id AS friend_id')
            ->where('user1_id', $current_user->id)
            ->union(
                Friend::select('user1_id AS friend_id')
                    ->where('user2_id', $current_user->id)
            )
            ->get()->toArray();

        $friends_query = User::whereIn('id', $friend_ids);

        if ($search_string) {
            $friends_query = $friends_query->where(function ($query) use ($search_string) {
                $query->whereRaw('username LIKE ?', ["%$search_string%"])
                    ->orWhereRaw('email LIKE ?', ["%$search_string%"]);
                });
        }

        $friends = $friends_query->orderBy('username', 'asc')->get();

        $friends = $this->augmentFriends($friends);

        return view('friends.partials.friends', ['friends' => $friends]);
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
}
