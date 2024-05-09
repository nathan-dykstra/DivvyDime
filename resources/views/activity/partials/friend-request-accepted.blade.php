<div class="notification notification-link" onclick="openLink('{{ route('friends.show', $notification->sender) }}')">
    @if ($notification->creator === $notification->recipient) <!-- Current User accepted the friend request -->
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div>{{ __('You accepted a friend request from ') }}<span class="bold-username">{{ $notification->sender_username }}</span></div>

                    <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $notification->date . ' at ' . $notification->formatted_time }}">
                        <div class="text-shy width-content">{{ $notification->formatted_date }}</div>
                    </x-tooltip>
                </div>
            </div>

            <div class="delete-notification-btn-container">
                <x-tooltip side="left" tooltip="{{ __('Delete Notification') }}">
                    <i class="fa-solid fa-trash-can delete-notification-btn" onclick="deleteNotification(event, {{ $notification->id }})"></i>
                </x-tooltip>
            </div>
        </div>
    @else <!-- Current User sent the friend request -->
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div><span class="bold-username">{{ $notification->sender_username }}</span>{{ __(' accepted your friend request') }}</div>

                    <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $notification->date . ' at ' . $notification->formatted_time }}">
                        <div class="text-shy width-content">{{ $notification->formatted_date }}</div>
                    </x-tooltip>
                </div>
            </div>

            <div class="delete-notification-btn-container">
                <x-tooltip side="left" tooltip="{{ __('Delete Notification') }}">
                    <i class="fa-solid fa-trash-can delete-notification-btn" onclick="deleteNotification(event, {{ $notification->id }})"></i>
                </x-tooltip>
            </div>
        </div>
    @endif
</div>
