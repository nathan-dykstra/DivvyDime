<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGroupRequest;
use App\Models\Friend;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Notification as ModelsNotification;
use App\Models\NotificationAttribute;
use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class GroupsController extends Controller
{
    /**
     * Displays the user's Groups
     */
    public function index()
    {
        $current_user = auth()->user();

        $groups = $current_user->groups()->get();

        return view('groups.groups-list', [
            'groups' => $groups,
        ]);
    }

    public function create(): View
    {
        return view('groups.create', ['group' => null]);
    }

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

        return Redirect::route('groups.show', $group->id)->with('status', 'group-created');
    }

    public function show($group_id): View
    {
        $current_user = auth()->user();

        $group = Group::where('id', $group_id)->first();

        if ($group === null) {
            return view('groups.does-not-exist');
        } else if (!in_array($current_user->id, $group->members()->pluck('users.id')->toArray())) {
            return view('groups.not-allowed');
        }

        return view('groups.show', [
            'group' => $group,
        ]);
    }

    /**
     * Displays the group settings.
     */
    public function settings(Group $group): View
    {
        $friends = auth()->user()->friends()->orderBy('username', 'asc')->get();

        return view('groups.group-settings', [
            'group' => $group,
            'friends' => $friends,
        ]);
    }

    /**
     * Updates the group detauls.
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
    public function invite(Request $request, Group $group)
    {
        $inviter = $request->user();

        $user_emails = $request->input('emails');

        $invite_errors = 0;

        // TODO: email validation fix
        $rules = [
            'email' => ['string', 'lowercase', 'email', 'max:255']
        ];

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

                $group_invite_sender = ModelsNotification::create([
                    'notification_type_id' => NotificationType::INVITED_TO_GROUP,
                    'creator' => $inviter->id,
                    'sender' => $existing_user->id,
                    'recipient' => $inviter->id,
                ]);

                $group_invite_attributes_sender = NotificationAttribute::create([
                    'notification_id' => $group_invite_sender->id,
                    'group_id' => $group->id,
                ]);

                $group_invite_recipient = ModelsNotification::create([
                    'notification_type_id' => NotificationType::INVITED_TO_GROUP,
                    'creator' => $inviter->id,
                    'sender' => $inviter->id,
                    'recipient' => $existing_user->id,
                ]);

                $group_invite_attributes_recipient = NotificationAttribute::create([
                    'notification_id' => $group_invite_recipient->id,
                    'group_id' => $group->id,
                ]);
            } else {
                // TODO: create email invite to app (creating account through the link automatically joins group)
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
    public function accept(Request $request)
    {
        $notification_id = $request->input('notification_id');
        $group_id = $request->input('group_id');

        $recipient_notification = ModelsNotification::where('id', $notification_id)->first();

        $sender_id = $recipient_notification->sender;
        $recipient_id = $recipient_notification->recipient;

        $group = Group::where('id', $group_id)->first();
        $invited_user = User::where('id', $recipient_id)->first();

        foreach ($group->members()->get() as $group_member) {
            // Add member friends (if necessary)

            $existing_friend = in_array($group_member->id, $invited_user->friends()->pluck('users.id')->toArray());

            if (!$existing_friend) {
                $new_friend = Friend::create([
                    'user1_id' => $recipient_id,
                    'user2_id' => $group_member->id,
                ]);
            }
        }

        $new_member = GroupMember::firstOrCreate([
            'group_id' => $group_id,
            'user_id' => $recipient_id,
        ]);

        $sender_notification = ModelsNotification::where('notification_type_id', NotificationType::INVITED_TO_GROUP)
            ->where('sender', $recipient_id)
            ->where('recipient', $sender_id)
            ->first();

        $recipient_notification_update = $recipient_notification->update([
            'notification_type_id' => NotificationType::JOINED_GROUP,
        ]);

        $sender_notification_update = $sender_notification->update([
            'notification_type_id' => NotificationType::JOINED_GROUP,
        ]);

        if ($new_member && $recipient_notification_update && $sender_notification_update) {
            return response()->json([
                'message' => 'Friend request accepted!',
            ]);
        } else {
            return response()->json([
                'message' => 'Error occured!',
            ], 500);
        }
    }

    /**
     * Reject a group invite.
     */
    public function reject(Request $request)
    {
        $notification_id = $request->input('notification_id');

        $recipient_notification = ModelsNotification::where('id', $notification_id)->first();
        $recipient_notification_attributes = NotificationAttribute::where('notification_id', $notification_id)->first();

        $sender_id = $recipient_notification->sender;
        $recipient_id = $recipient_notification->recipient;

        $sender_notification = ModelsNotification::where('notification_type_id', NotificationType::INVITED_TO_GROUP)
            ->where('sender', $recipient_id)
            ->where('recipient', $sender_id)
            ->first();
        $sender_notification_attributes = NotificationAttribute::where('notification_id', $sender_notification->id)->first();

        $recipient_notification_attributes->delete();
        $recipient_notification->delete();
        $sender_notification_attributes->delete();
        $sender_notification->delete();

        return response()->json([
            'message' => 'Friend request denied!',
        ]);
    }

    /**
     * Filters the Groups list.
     */
    public function search(Request $request): View
    {
        $current_user = auth()->user();

        $search_string = $request->input('search_string');

        $groups = $current_user->groups()
            ->join('users', 'group_members.user_id', 'users.id')
            ->where(function ($query) use ($search_string) {
                $query->whereRaw('users.username LIKE ?', ["%$search_string%"])
                    ->orWhereRaw('users.email LIKE ?', ["%$search_string%"])
                    ->orWhereRaw('groups.name LIKE ?', ["%$search_string%"]);
            })
            ->get();

        return view('groups.partials.groups', ['groups' => $groups]);
    }
}
