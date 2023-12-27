<a>
    @if ($notification->creator === $notification->recipient) <!-- Current User accepted the friend request -->
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div>You accepted a friend request from <span class="notification-username">{{ $notification->username }}</span>.</div> <!-- TODO: Show user profile image in notification, link to user profile -->
                    <div class="text-shy">{{ $notification->formatted_date }}, {{ $notification->formatted_time }}</div>
                </div>
            </div>

            <div class="delete-notification-btn-container">
                <div class="tooltip tooltip-left">
                    <i class="fa-solid fa-trash-can delete-notification-btn" onclick="deleteNotification($(this), {{ $notification->id }})"></i>
                    <span class="tooltip-text">{{ __('Delete Notification') }}</span>
                </div>
            </div>
        </div>
    @else <!-- Current User sent the friend request -->
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div><span class="notification-username">{{ $notification->username }}</span> accepted your friend request.</div> <!-- TODO: Show user profile image in notification, link to user profile -->
                    <div class="text-shy">{{ $notification->formatted_date }}, {{ $notification->formatted_time }}</div>
                </div>
            </div>

            <div class="delete-notification-btn-container">
                <div class="tooltip tooltip-left">
                    <i class="fa-solid fa-trash-can delete-notification-btn" onclick="deleteNotification($(this), {{ $notification->id }})"></i>
                    <span class="tooltip-text">{{ __('Delete Notification') }}</span>
                </div>
            </div>
        </div>
    @endif
</a>
