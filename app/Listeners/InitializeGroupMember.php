<?php

namespace App\Listeners;

use App\Events\GroupMemberCreated;
use App\Models\Balance;
use App\Models\Friend;
use App\Models\Group;
use App\Models\Notification;
use App\Models\NotificationAttribute;
use App\Models\NotificationType;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class InitializeGroupMember
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
    public function handle(GroupMemberCreated $event): void
    {
        $group = Group::where('id', $event->member->group_id)->first();
        $new_member = User::where('id', $event->member->user_id)->first();

        // For each group member (excluding the newly created member): 
        //    1. Establish friendship on the app
        //    2. Send a "Joined Group" notification
        //    3. Initialize Group Balance
        foreach ($group->members()->get() as $group_member) {
            if ($group_member->id !== $new_member->id) {
                // Add group member as a friend (if necessary)

                $existing_friend = in_array($group_member->id, $new_member->friends()->pluck('users.id')->toArray());

                if (!$existing_friend) {
                    Friend::create([
                        'user1_id' => $new_member->id,
                        'user2_id' => $group_member->id,
                    ]);
                }

                // Send Group member a "Joined Group" Notification

                $joined_group_notification = Notification::updateOrCreate(
                    [
                        'notification_type_id' => NotificationType::INVITED_TO_GROUP,
                        'sender' => $new_member->id,
                        'recipient' => $group_member->id,
                    ],
                    [
                        'notification_type_id' => NotificationType::JOINED_GROUP,
                        'creator' => $new_member->id,
                    ],
                );

                NotificationAttribute::firstOrCreate(
                    [
                        'notification_id' => $joined_group_notification->id,
                        'group_id' => $group->id,
                    ]
                );

                // Initialize the Balances between the Group member and new member

                Balance::create([
                    'user_id' => $new_member->id,
                    'friend' => $group_member->id,
                    'group_id' => $group->id,
                    'balance' => 0.00,
                ]);

                Balance::create([
                    'user_id' => $group_member->id,
                    'friend' => $new_member->id,
                    'group_id' => $group->id,
                    'balance' => 0.00,
                ]);
            }
        }
    }
}
