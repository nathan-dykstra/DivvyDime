@foreach ($notifications as $notification)
    @if ($notification->notification_type_id === \App\Models\NotificationType::EXPENSE)
        @include('activity.partials.expense')
    @elseif ($notification->notification_type_id === \App\Models\NotificationType::REIMBURSEMENT)
        <!-- TODO: re-evaluate if this is needed -->
    @elseif ($notification->notification_type_id === \App\Models\NotificationType::REMINDER)
        <!-- TODO (notification type reminder)-->
    @elseif ($notification->notification_type_id === \App\Models\NotificationType::PAYMENT)
        @include('activity.partials.payment')
    @elseif ($notification->notification_type_id === \App\Models\NotificationType::PAYMENT_CONFIRMED)
        @include('activity.partials.payment-confirmed')
    @elseif ($notification->notification_type_id === \App\Models\NotificationType::PAYMENT_REJECTED)
        @include('activity.partials.payment-rejected')
    @elseif ($notification->notification_type_id === \App\Models\NotificationType::BALANCE_SETTLED)
        <!-- TODO: re-evaluate if this is needed -->
    @elseif ($notification->notification_type_id === \App\Models\NotificationType::FRIEND_REQUEST)
        @include('activity.partials.friend-request')
    @elseif ($notification->notification_type_id === \App\Models\NotificationType::FRIEND_REQUEST_ACCEPTED)
        @include('activity.partials.friend-request-accepted')
    @elseif ($notification->notification_type_id === \App\Models\NotificationType::INVITED_TO_GROUP)
        @include('activity.partials.group-invite')
    @elseif ($notification->notification_type_id === \App\Models\NotificationType::JOINED_GROUP)
        @include('activity.partials.joined-group')
    @elseif ($notification->notification_type_id === \App\Models\NotificationType::LEFT_GROUP)
        @include('activity.partials.left-group')
    @elseif ($notification->notification_type_id === \App\Models\NotificationType::REMOVED_FROM_GROUP)
        <!-- TODO -->
    @endif
@endforeach
