<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Notification;
use App\Models\NotificationAttribute;
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
        NotificationAttribute::where('notification_id', $notification_id)->delete();
        Notification::where('id', $notification_id)->delete();

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

        $notifications = Notification::select('notifications.*', 'users.username')
            ->join('users', 'notifications.sender', 'users.id')
            ->join('notification_types', 'notifications.notification_type_id', 'notification_types.id')
            ->where('notifications.recipient', $current_user->id)
            // Filter the results further based on the search
            ->where(function ($query) use ($search_string) {
                $query->whereRaw('users.username LIKE ?', ["%$search_string%"])
                    ->orWhereRaw('notification_types.type LIKE ?', ["%$search_string%"]);
                })
            ->orderBy('notifications.updated_at', 'desc')
            ->get();

        $notifications = $this->augmentNotifications($notifications);

        return view('activity.partials.notifications', [
            'notification_types' => self::NOTIFICATION_TYPES,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Returns the activity/partials/notificaitons view.
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
            $notification->formatted_date = Carbon::parse($notification->updated_at)->isAfter(Carbon::now()->subWeek())
                ? Carbon::parse($notification->updated_at)->diffForHumans()
                : Carbon::parse($notification->updated_at)->format('M d');
            $notification->formatted_time = Carbon::parse($notification->updated_at)->setTimezone(self::TIMEZONE)->format('g:i a');

            $notification->group = Group::where('id', $notification->attributes->group_id)->first();
        
            return $notification;
        });

        return $notifications;
    }
}
