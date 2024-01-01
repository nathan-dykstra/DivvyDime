<a>
    @if ($notification->creator === $notification->recipient) <!-- Current User accepted the friend request -->
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div>You accepted a friend request from <span class="notification-username">{{ $notification->username }}</span>.</div> <!-- TODO: Show user profile image in notification, link to user profile -->

                    <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $notification->date . ' at ' . $notification->formatted_time }}">
                        <div class="text-shy width-content">{{ $notification->formatted_date }}</div>
                    </x-tooltip>
                </div>
            </div>

            <div class="delete-notification-btn-container">
                <x-tooltip side="left" tooltip="{{ __('Delete Notification') }}">
                    <i class="fa-solid fa-trash-can delete-notification-btn" onclick="deleteNotification($(this), {{ $notification->id }})"></i>
                </x-tooltip>
            </div>
        </div>
    @else <!-- Current User sent the friend request -->
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div><span class="notification-username">{{ $notification->username }}</span> accepted your friend request.</div> <!-- TODO: Show user profile image in notification, link to user profile -->

                    <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $notification->date . ' at ' . $notification->formatted_time }}">
                        <div class="text-shy width-content">{{ $notification->formatted_date }}</div>
                    </x-tooltip>
                </div>
            </div>

            <div class="delete-notification-btn-container">
                <x-tooltip side="left" tooltip="{{ __('Delete Notification') }}">
                    <i class="fa-solid fa-trash-can delete-notification-btn" onclick="deleteNotification($(this), {{ $notification->id }})"></i>
                </x-tooltip>
            </div>
        </div>
    @endif
</a>
