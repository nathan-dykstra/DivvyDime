<?php

namespace App\Listeners;

use App\Events\UserDeleting;
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

        $group_ids = GroupMember::where('user_id', $event->user->id)->pluck('group_id')->toArray();

        // Delete all received notifications involving the User

        $notifications_to_delete = Notification::where('recipient', $event->user->id)->get();

        // Delete Notifications one at a time (mass deletion does not trigger deleting event listener)
        foreach ($notifications_to_delete as $notification) {
            $notification->delete();
        }

        // Change all sent notifications involving the User to "DivvyDime User"

        Notification::where('sender', $event->user->id)->update([
            'sender' => null,
        ]);

        Notification::where('creator', $event->user->id)->update([
            'creator' => null,
        ]);

        // Remove User from Groups

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

                // TODO: Send "DivvyDime User" left group notification ?

                GroupMember::where('group_id', $group_id)->where('user_id', $event->user->id)->delete();
            } else {
                $group->delete();
            }
        }

        // Delete UserPreferences
        UserPreference::where('user_id', $event->user->id)->delete();
    }
}
