<?php

namespace App\Listeners;

use App\Events\GroupDeleting;
use App\Models\GroupMember;
use App\Models\Notification;
use App\Models\NotificationAttribute;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteGroupDependents
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
    public function handle(GroupDeleting $event): void
    {
        // TODO: Delete all group expenses

        // Delete Group-related Notifications

        $notifications_to_delete = Notification::whereHas('attributes', function ($query) use ($event) {
            $query->where('group_id', $event->group->id);
        })->get();

        // Delete Notifications one at a time (mass deletion does not trigger deleting event listener)
        foreach ($notifications_to_delete as $notification) {
            $notification->delete();
        }

        // TODO: Create "deleted group" notification ?

        // Delete Group members
        GroupMember::where('group_id', $event->group->id)->delete();
    }
}
