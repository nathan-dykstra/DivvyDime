<div class="notifications">
    @foreach ($notifications as $notification)
        @if ($notification->notification_type_id === $notification_types['expense'])
            @include('activity.partials.expense')
        @elseif ($notification->notification_type_id === $notification_types['reimbursement'])

        @elseif ($notification->notification_type_id === $notification_types['reminder'])

        @elseif ($notification->notification_type_id === $notification_types['payment'])
            @include('activity.partials.payment')
        @elseif ($notification->notification_type_id === $notification_types['payment_confirmed'])
            @include('activity.partials.payment-confirmed')
        @elseif ($notification->notification_type_id === $notification_types['balance_settled'])

        @elseif ($notification->notification_type_id === $notification_types['friend_request'])
            @include('activity.partials.friend-request')
        @elseif ($notification->notification_type_id === $notification_types['friend_request_accepted'])
            @include('activity.partials.friend-request-accepted')
        @elseif ($notification->notification_type_id === $notification_types['invited_to_group'])
            @include('activity.partials.group-invite')
        @elseif ($notification->notification_type_id === $notification_types['joined_group'])
            @include('activity.partials.joined-group')
        @elseif ($notification->notification_type_id === $notification_types['left_group'])
            @include('activity.partials.left-group')
        @elseif ($notification->notification_type_id === $notification_types['removed_from_group'])

        @endif
    @endforeach
</div>
