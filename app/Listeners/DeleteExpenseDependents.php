<?php

namespace App\Listeners;

use App\Events\ExpenseDeleting;
use App\Models\ExpenseParticipant;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteExpenseDependents
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
    public function handle(ExpenseDeleting $event): void
    {
        // Delete ExpenseParticipants
        ExpenseParticipant::where('expense_id', $event->expense->id)->delete();

        // Delete Expense Notifications

        $notifications_to_delete = Notification::whereHas('attributes', function ($query) use ($event) {
            $query->where('expense_id', $event->expense->id);
        })->get();

        // Delete Notifications one at a time (mass deletion does not trigger deleting event listener)
        foreach ($notifications_to_delete as $notification) {
            $notification->delete();
        }
    }
}
