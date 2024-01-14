<?php

namespace App\Listeners;

use App\Events\ExpenseDeleting;
use App\Models\ExpenseParticipant;
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
    }
}
