<?php

namespace App\Listeners;

use App\Events\ExpenseDeleting;
use App\Models\ExpenseGroup;
use App\Models\ExpenseParticipant;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

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
        // Adjust balances (ignore unconfirmed expenses - i.e. payments - as these have not updated any balances yet)
        if ($event->expense->is_confirmed) {
            $event->expense->undoBalanceAdjustments();
        }

        // Delete expense participants
        ExpenseParticipant::where('expense_id', $event->expense->id)->delete();

        // Delete expense groups
        ExpenseGroup::where('expense_id', $event->expense->id)->delete();

        // Delete expense notifications

        $notifications_to_delete = Notification::whereHas('attributes', function ($query) use ($event) {
            $query->where('expense_id', $event->expense->id);
        })->get();

        // Delete the notifications one at a time (mass deletion does not trigger deleting event listener)
        foreach ($notifications_to_delete as $notification) {
            $notification->delete();
        }

        // Delete expense images
        $event->expense->deleteImages();

        $event->expense->images()->delete();
    }
}
