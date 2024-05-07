<?php

namespace App\Listeners;

use App\Events\UserDeleting;
use App\Models\Balance;
use App\Models\Friend;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\UserPreference;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteUserDependents
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserDeleting $event): void
    {
        // TODO: Change all expenses involving the User to "DivvyDime User"
        // TODO: Delete any non-group expenses where the other User is already deleted (i.e. the other User is now "DivvyDime User")

        // Delete all of the user's received notifications

        $notifications_to_delete = Notification::where('recipient', $event->user->id)->get();

        // Delete notifications one at a time (mass deletion does not trigger deleting event listener)
        foreach ($notifications_to_delete as $notification) {
            $notification->delete();
        }

        // Change all of the user's sent notifications to "DivvyDime User"

        Notification::where('sender', $event->user->id)->update([
            'sender' => 1, // TODO: Change this from "1" to "DivvyDime User"
        ]);

        Notification::where('creator', $event->user->id)->update([
            'creator' => 1, // TODO: Change this from "1" to "DivvyDime User"
        ]);

        // Delete user's friendships
        Friend::where('user1_id', $event->user->id)->orWhere('user2_id', $event->user->id)->delete();

        // Remove user from groups

        $group_ids = GroupMember::where('user_id', $event->user->id)->pluck('group_id')->toArray();

        foreach ($group_ids as $group_id) {
            $group = Group::where('id', $group_id)->first();

            if ($group->members()->count() > 1) {
                if ($group->owner === $event->user->id) {
                    $new_owner = GroupMember::where('group_id', $group->id)
                        ->whereNot('user_id', $event->user->id)
                        ->orderBy('created_at', 'asc')
                        ->pluck('user_id')
                        ->first();

                    $group->owner = $new_owner;
                    $group->save();
                }

                GroupMember::where('group_id', $group_id)->where('user_id', $event->user->id)->delete(); // TODO: create event listener for GroupMember deleting to send remaining members a "left group" notification ?

                // Delete the group balances involving this user
                Balance::where('group_id', $group->id)
                    ->where(function ($query) use ($event) {
                        $query->where('user_id', $event->user->id)
                            ->orWhere('friend', $event->user->id);
                    })
                    ->delete();
            } else {
                $group->delete();
            }
        }

        // Delete user preferences
        UserPreference::where('user_id', $event->user->id)->delete();
    }
}
