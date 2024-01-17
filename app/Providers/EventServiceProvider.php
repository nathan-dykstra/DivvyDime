<?php

namespace App\Providers;

use App\Events\ExpenseDeleting;
use App\Events\GroupDeleting;
use App\Events\GroupMemberCreated;
use App\Events\NotificationDeleting;
use App\Events\UserDeleting;
use App\Listeners\DeleteExpenseDependents;
use App\Listeners\DeleteGroupDependents;
use App\Listeners\DeleteNotificationDependents;
use App\Listeners\DeleteUserDependents;
use App\Listeners\InitializeGroupMember;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        GroupDeleting::class => [
            DeleteGroupDependents::class,
        ],
        NotificationDeleting::class => [
            DeleteNotificationDependents::class,
        ],
        UserDeleting::class => [
            DeleteUserDependents::class,
        ],
        ExpenseDeleting::class => [
            DeleteExpenseDependents::class,
        ],
        GroupMemberCreated::class => [
            InitializeGroupMember::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
