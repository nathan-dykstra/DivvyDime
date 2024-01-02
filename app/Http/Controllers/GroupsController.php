<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGroupRequest;
use App\Models\Friend;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\GroupMember;
use App\Models\Notification as ModelsNotification;
use App\Models\NotificationAttribute;
use App\Models\NotificationType;
use App\Models\User;
use App\Notifications\GroupInviteNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GroupsController extends Controller
{
    /**
     * Displays the user's Groups
     */
    public function index()
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

        Log::info($groups);

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

        return Redirect::route('groups.show', $group->id)->with('status', 'group-created');
    }

    /**
     * Displays the Group.
     */
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
                    'register.from-group-invite', now()->addMinutes(300), ['token' => $token]
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
    public function accept(Request $request)
    {
        $invitee_notification_id = $request->input('notification_id');
        $invitee_notification = ModelsNotification::where('id', $invitee_notification_id)->first();

        $group_id = $request->input('group_id');
        $group = Group::where('id', $group_id)->first();

        $inviter_id = $invitee_notification->sender;
        $invitee_id = $invitee_notification->recipient;
        $invitee = User::where('id', $invitee_id)->first();

        foreach ($group->members()->get() as $group_member) {
            // Add group member as a friend (if necessary)

            $existing_friend = in_array($group_member->id, $invitee->friends()->pluck('users.id')->toArray());

            if (!$existing_friend) {
                Friend::create([
                    'user1_id' => $invitee_id,
                    'user2_id' => $group_member->id,
                ]);
            }

            // Send group member a notification that the invitee joined the group

            if ($group_member->id !== $inviter_id) {
                $member_notification = ModelsNotification::create([
                    'notification_type_id' => NotificationType::JOINED_GROUP,
                    'creator' => $invitee_id,
                    'sender' => $invitee_id,
                    'recipient' => $group_member->id,
                ]);

                NotificationAttribute::create([
                    'notification_id' => $member_notification->id,
                    'group_id' => $group_id,
                ]);
            }
        }

        // Update the inviter's and invitee's notifications

        $inviter_notification_update = ModelsNotification::updateOrCreate(
                [
                    'notification_type_id' => NotificationType::INVITED_TO_GROUP,
                    'creator' => $inviter_id,
                    'sender' => $invitee_id,
                    'recipient' => $inviter_id,
                ],
                [
                    'notification_type_id' => NotificationType::JOINED_GROUP,
                    'creator' => $invitee_id,
                ],
            );

        NotificationAttribute::firstOrCreate(
            [
                'notification_id' => $inviter_notification_update->id,
                'group_id' => $group->id,
            ]
        );

        $invitee_notification->update([
            'notification_type_id' => NotificationType::JOINED_GROUP,
            'creator' => $invitee_id,
        ]);

        // Add invitee to group
        GroupMember::firstOrCreate([
            'group_id' => $group_id,
            'user_id' => $invitee_id,
        ]);

        return response()->json([
            'message' => 'Friend request accepted!',
        ]);
    }

    /**
     * Reject a group invite.
     */
    public function reject(Request $request)
    {
        // Delete inviter's and invitee's notifications

        $invitee_notification_id = $request->input('notification_id');
        $invitee_notification = ModelsNotification::where('id', $invitee_notification_id)->first();

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
     * Remove a member from the Group.
     */
    public function removeMember(Request $request, Group $group)
    {
        $member_id = $request->input('member_id');

        // TODO: Change all member's group expenses to "DivvyDime User"

        GroupMember::where('group_id', $group->id)->where('user_id', $member_id)->delete();

        Session::flash('status', 'member-removed');

        return response()->json([
            'message' => 'Member removed successfully!',
            'redirect' => route('groups.settings', $group),
        ]);
    }

    /**
     * Removes the current user from the Group.
     */
    public function leaveGroup(Request $request, Group $group)
    {
        $current_user = auth()->user();

        // TODO: Change all of the current user's group expenses to "DivvyDime User"

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
        }

        Session::flash('status', 'left-group');

        return response()->json([
            'message' => 'Left group successfully!',
            'redirect' => route('groups'),
        ]);
    }

    /**
     * Deletes the Group.
     */
    public function destroy(Request $request, Group $group)
    {
        $group->delete();

        Session::flash('status', 'group-deleted');

        return response()->json([
            'message' => 'Group deleted successfully!',
            'redirect' => route('groups'),
        ]);
    }

    /**
     * Filters the Groups list.
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
                        ->orWhereRaw('users.email LIKE ?', ["%$search_string%"])
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
     * Add default Group information to the Groups.
     */
    protected function augmentGroups($groups)
    {
        $groups = $groups->map(function ($group) {
            $group->is_default = $group->id === Group::DEFAULT_GROUP;

            return $group;
        });

        return $groups;
    }
}


