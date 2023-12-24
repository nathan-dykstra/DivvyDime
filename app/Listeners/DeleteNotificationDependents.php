<?php

namespace App\Listeners;

use App\Events\NotificationDeleting;
use App\Models\NotificationAttribute;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DeleteNotificationDependents
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
    public function handle(NotificationDeleting $event): void
    {
        // Delete NotificationAttributes
        NotificationAttribute::where('notification_id', $event->notification->id)->delete();
    }
}
