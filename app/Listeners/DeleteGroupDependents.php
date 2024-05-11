<?php

namespace App\Listeners;

use App\Events\GroupDeleting;
use App\Models\Balance;
use App\Models\GroupMember;
use App\Models\Notification;
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
        // Delete group expenses

        $expenses_to_delete = $event->group->expenses;

        // Delete expenses one at a time (mass deletion does not trigger deleting event listener)
        foreach ($expenses_to_delete as $expense) {
            $expense->delete();
        }

        // Delete group-related notifications

        $notifications_to_delete = Notification::whereHas('attributes', function ($query) use ($event) {
            $query->where('group_id', $event->group->id);
        })->get();

        // Delete notifications one at a time (mass deletion does not trigger deleting event listener)
        foreach ($notifications_to_delete as $notification) {
            $notification->delete();
        }

        // Delete group balances
        Balance::where('group_id', $event->group->id)->delete();

        // TODO: Create "deleted group" notification ?

        // Delete Group members
        GroupMember::where('group_id', $event->group->id)->delete();
    }
}
