<div>
    @if ($notification->creator === $notification->recipient) <!-- Current User sent the invite -->
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div>You sent <span class="notification-username">{{ $notification->username }}</span> an invite to <span class="notification-username">{{ $notification->group->name }}</span>.</div> <!-- TODO: (maybe) Show user profile image in notification, link to user profile -->
                    <div class="text-shy">{{ $notification->formatted_date }}, {{ $notification->formatted_time }}</div>
                </div>
                <div class="text-warning"><i class="fa-solid fa-triangle-exclamation fa-sm icon"></i>{{ __('This invite is pending.') }}</div>
            </div>

            <div class="delete-notification-btn-container">
                <div class="tooltip tooltip-left">
                    <i class="fa-solid fa-trash-can delete-notification-btn" onclick="deleteNotification($(this), {{ $notification->id }})"></i> <!-- TODO: modify delete notification to also delete the notification attributes (if they exist) -->
                    <span class="tooltip-text">{{ __('Delete Notification') }}</span>
                </div>
            </div>
        </div>
    @else <!-- Current User receiving the invite -->
        <div class="notification-content">
            <div>
                <div><span class="notification-username">{{ $notification->username }}</span> sent you an invite to <span class="notification-username">{{ $notification->group->name }}</span>.</div> <!-- TODO: Show user profile image in notification, link to user profile -->
                <div class="text-shy">{{ $notification->formatted_date }}, {{ $notification->formatted_time }}</div>
            </div>
            <div class="btn-container-start">
                <x-primary-button class="primary-color-btn" onclick="acceptGroupInvite({{ $notification->id }}, {{ $notification->group->id }})">{{ __('Accept') }}</x-primary-button>
                <x-secondary-button onclick="rejectGroupInvite($(this), {{ $notification->id }})">{{ __('Reject') }}</x-secondary-button>
            </div>
        </div>
    @endif
</div>
