<?php

namespace App\Listeners;

use App\Events\UserDeleting;
use App\Models\Balance;
use App\Models\Expense;
use App\Models\ExpenseParticipant;
use App\Models\Friend;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Notification;
use App\Models\User;
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
        // Delete all of the user's non-group expenses that are no longer associated with
        // any other DivvyDime user

        $expenses_to_delete = Expense::whereHas('groups', function ($query) {
                $query->where('groups.id', Group::DEFAULT_GROUP);
            })
            ->where(function ($query) use ($event) {
                $query->where('expenses.payer', $event->user->id)
                    ->whereDoesntHave('participants', function ($query) {
                        $query->where('users.id', '!=', User::DEFAULT_USER);
                    });
            })
            ->orWhere(function ($query) use ($event) {
                $query->whereHas('participants', function ($query) use ($event) {
                        $query->where('users.id', $event->user->id);
                    })
                    ->where('expenses.payer', User::DEFAULT_USER)
                    ->whereDoesntHave('participants', function ($query) use ($event) {
                        $query->where('users.id', '!=', $event->user->id)
                              ->where('users.id', '!=', User::DEFAULT_USER);
                    });
            })
            ->get();

        foreach ($expenses_to_delete as $expense) {
            $expense->delete();
        }

        // Update all of the user's remaining expenses to the default DivvyDime user

        // Expenses (payer)
        Expense::where('payer', $event->user->id)->update([
            'payer' => User::DEFAULT_USER,
        ], ['timestamps' => false]);

        // Expenses (creator)
        Expense::where('creator', $event->user->id)->update([
            'creator' => User::DEFAULT_USER,
        ], ['timestamps' => false]);

        // Expenses (updator)
        Expense::where('updator', $event->user->id)->update([
            'updator' => User::DEFAULT_USER,
        ], ['timestamps' => false]);

        // Expenses (participant)
        ExpenseParticipant::where('user_id', $event->user->id)->update([
            'user_id' => User::DEFAULT_USER,
        ], ['timestamps' => false]);

        // Delete all of the user's received notifications

        $notifications_to_delete = Notification::where('recipient', $event->user->id)->get();

        // Delete notifications one at a time (mass deletion does not trigger deleting event listener)
        foreach ($notifications_to_delete as $notification) {
            $notification->delete();
        }

        // Change all of the user's sent notifications to "DivvyDime User"

        Notification::where('sender', $event->user->id)->update([
            'sender' => User::DEFAULT_USER,
        ], ['timestamps' => false]);

        Notification::where('creator', $event->user->id)->update([
            'creator' => User::DEFAULT_USER,
        ], ['timestamps' => false]);

        // Delete user's friendships
        Friend::where('user1_id', $event->user->id)->orWhere('user2_id', $event->user->id)->delete();

        // Remove user from groups

        $group_ids = GroupMember::where('user_id', $event->user->id)->pluck('group_id')->toArray();

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

                GroupMember::where('group_id', $group_id)->where('user_id', $event->user->id)->delete(); // TODO: create event listener for GroupMember deleting to send remaining members a "left group" notification ?

                // Delete the group balances involving this user
                Balance::where('group_id', $group->id)
                    ->where(function ($query) use ($event) {
                        $query->where('user_id', $event->user->id)
                            ->orWhere('friend', $event->user->id);
                    })
                    ->delete();
            } else {
                $group->delete();
            }
        }

        // Delete user preferences
        UserPreference::where('user_id', $event->user->id)->delete();
    }
}
