<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseParticipant;
use App\Models\Group;
use App\Models\Notification;
use App\Models\NotificationType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    const TIMEZONE = 'America/Toronto'; // TODO: make this a user setting
    const NOTIFICATION_TYPES = [
        'expense' => NotificationType::EXPENSE,
        'reimbursement' => NotificationType::REIMBURSEMENT,
        'reminder' => NotificationType::REMINDER,
        'payment' => NotificationType::PAYMENT,
        'payment_confirmed' => NotificationType::PAYMENT_CONFIRMED,
        'balance_settled' => NotificationType::BALANCE_SETTLED,
        'friend_request' => NotificationType::FRIEND_REQUEST,
        'friend_request_accepted' => NotificationType::FRIEND_REQUEST_ACCEPTED,
        'invited_to_group' => NotificationType::INVITED_TO_GROUP,
        'joined_group' => NotificationType::JOINED_GROUP,
        'left_group' => NotificationType::LEFT_GROUP,
        'removed_from_group' => NotificationType::REMOVED_FROM_GROUP,
    ];

    /**
     * Display the user's notifications.
     */
    public function index(): View
    {
        $current_user = auth()->user();

        $notifications = Notification::select('notifications.*', 'users.username')
            ->join('users', 'notifications.sender', 'users.id')
            ->where('notifications.recipient', $current_user->id)
            ->orderBy('notifications.updated_at', 'desc')
            ->get();

        $notifications = $this->augmentNotifications($notifications);

        return view('activity.activity-list', [
            'notifications' => $notifications,
            'notification_types' => self::NOTIFICATION_TYPES,
        ]);
    }

    /**
     * Delete a notification.
     */
    public function delete(Request $request, $notification_id) 
    {
        Notification::where('id', $notification_id)->first()->delete();

        return response()->json([
            'message' => 'Notification deleted!',
        ]);
    }

    /**
     * Filters the notifications in the Activity section.
     */
    public function search(Request $request): View
    {
        $current_user = auth()->user();
        $search_string = $request->input('search_string');

        $notifications_query = Notification::select('notifications.*', 'users.username')
            ->join('users', 'notifications.sender', 'users.id')
            ->where('notifications.recipient', $current_user->id);

        if ($search_string) {
            $notifications_query = $notifications_query->leftJoin('notification_attributes', 'notifications.id', 'notification_attributes.notification_id')
                ->leftJoin('groups', 'notification_attributes.group_id', 'groups.id')
                ->leftJoin('expenses', 'notification_attributes.expense_id', 'expenses.id')
                ->where(function ($query) use ($search_string) {
                    $query->whereRaw('users.username LIKE ?', ["%$search_string%"])
                        ->orWhereRaw('groups.name LIKE ?', ["%$search_string%"])
                        ->orWhereRaw('expenses.name LIKE ?', ["%$search_string%"]);
                });
        }

        $notifications = $notifications_query->orderBy('notifications.updated_at', 'desc')->get();

        $notifications = $this->augmentNotifications($notifications);

        return view('activity.partials.notifications', [
            'notification_types' => self::NOTIFICATION_TYPES,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Returns the activity.partials.notifications view.
     */
    public function getUpdatedNotifications(Request $request): View
    {
        $current_user = auth()->user();

        $notifications = Notification::select('notifications.*', 'users.username')
            ->join('users', 'notifications.sender', 'users.id')
            ->where('notifications.recipient', $current_user->id)
            ->orderBy('notifications.updated_at', 'desc')
            ->get();

        $notifications = $this->augmentNotifications($notifications);

        return view('activity.partials.notifications', [
            'notification_types' => self::NOTIFICATION_TYPES,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Add datetime formatting and notification attributes to the notifications.
     */
    protected function augmentNotifications($notifications) {
        $notifications = $notifications->map(function ($notification) {
            // Notification timestamps

            $notification->formatted_date = Carbon::parse($notification->updated_at)->diffForHumans();

            $notification->date = Carbon::parse($notification->updated_at)->format('M d, Y');
            
            $notification->formatted_time = Carbon::parse($notification->updated_at)->setTimezone(self::TIMEZONE)->format('g:i a');

            // Notification Group
            if ($notification->attributes?->group_id) {
                $notification->group = Group::where('id', $notification->attributes->group_id)->first();
            }

            // Notification Expense
            if ($notification->attributes?->expense_id) {
                $notification->expense = Expense::where('id', $notification->attributes->expense_id)->first();
                $notification->payer_username = User::where('id', $notification->expense->payer)->value('username');

                $current_user_share = ExpenseParticipant::where('expense_id', $notification->expense->id)
                    ->where('user_id', auth()->user()->id)
                    ->value('share');

                if ($notification->expense->payer === auth()->user()->id) {
                    $notification->amount_lent = $notification->expense->amount - $current_user_share;
                }

                if ($current_user_share) {
                    $notification->amount_borrowed = $current_user_share;
                }
            }

            return $notification;
        });

        return $notifications;
    }
}
