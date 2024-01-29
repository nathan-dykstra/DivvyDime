<div class="notification">
    @if ($notification->creator === $notification->recipient) <!-- Current User sent the friend request -->
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div>{{ __('You sent ') }}<span class="bold-username">{{ $notification->username }}</span>{{ __(' a friend request') }}</div>
                    
                    <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $notification->date . ' at ' . $notification->formatted_time }}">
                        <div class="text-shy width-content">{{ $notification->formatted_date }}</div>
                    </x-tooltip>
                </div>
                <div class="text-warning"><i class="fa-solid fa-triangle-exclamation fa-sm icon"></i>{{ __('This request is pending.') }}</div>
            </div>

            <div class="delete-notification-btn-container">
                <x-tooltip side="left" tooltip="{{ __('Delete Notification') }}">
                    <i class="fa-solid fa-trash-can delete-notification-btn" onclick="deleteNotification(event, {{ $notification->id }})"></i>
                </x-tooltip>
            </div>
        </div>
    @else <!-- Current User receiving the friend request -->
        <div class="notification-content">
            <div>
                <div><span class="bold-username">{{ $notification->username }}</span>{{ __(' sent you a friend request') }}</div>
                
                <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $notification->date . ' at ' . $notification->formatted_time }}">
                    <div class="text-shy width-content">{{ $notification->formatted_date }}</div>
                </x-tooltip>
            </div>
            <div class="btn-container-start">
                <x-primary-button class="primary-color-btn" onclick="acceptFriendRequest({{ $notification->id }})">{{ __('Accept') }}</x-primary-button>
                <x-secondary-button onclick="denyFriendRequest($(this), {{ $notification->id }})">{{ __('Reject') }}</x-secondary-button>
            </div>
        </div>
    @endif
</div>
