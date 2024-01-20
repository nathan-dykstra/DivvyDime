<?php

namespace App\Listeners;

use App\Events\FriendCreated;
use App\Models\Balance;
use App\Models\Group;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class InitializeFriend
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
    public function handle(FriendCreated $event): void
    {
        // Initialize the Balances between the Friends

        $user1_id = $event->friend->user1_id;
        $user2_id = $event->friend->user2_id;

        Balance::create([
            'user_id' => $user1_id,
            'friend' => $user2_id,
            'group_id' => Group::DEFAULT_GROUP,
            'balance' => 0.00,
        ]);

        Balance::create([
            'user_id' => $user2_id,
            'friend' => $user1_id,
            'group_id' => Group::DEFAULT_GROUP,
            'balance' => 0.00,
        ]);
    }
}
