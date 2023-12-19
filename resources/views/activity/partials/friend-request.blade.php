<a>
    @if ($notification->creator === $notification->recipient) <!-- Current User sent the friend request -->
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div>You sent <span class="notification-username">{{ $notification->username }}</span> a friend request.</div> <!-- TODO: (maybe) Show user profile image in notification, link to user profile -->
                    <div class="text-shy">{{ $notification->formatted_date }}, {{ $notification->formatted_time }}</div>
                </div>
                <div class="text-warning"><i class="fa-solid fa-triangle-exclamation fa-sm icon"></i>This request is pending.</div>
            </div>

            <div class="delete-notification-btn-container">
                <div class="tooltip tooltip-left">
                    <i class="fa-solid fa-trash-can delete-notification-btn" onclick="deleteNotification($(this), {{ $notification->id }})"></i>
                    <span class="tooltip-text">Delete Notification</span>
                </div>
            </div>
        </div>
    @else <!-- Current User receiving the friend request -->
        <div class="notification-content">
            <div>
                <div><span class="notification-username">{{ $notification->username }}</span> sent you a friend request.</div> <!-- TODO: Show user profile image in notification, link to user profile -->
                <div class="text-shy">{{ $notification->formatted_date }}, {{ $notification->formatted_time }}</div>
            </div>
            <div class="btn-container-start">
                <x-primary-button class="primary-color-btn" onclick="acceptFriendRequest({{ $notification->id }})">Accept</x-primary-button>
                <x-secondary-button onclick="denyFriendRequest($(this), {{ $notification->id }})">Reject</x-secondary-button>
            </div>
        </div>
    @endif
</a>
