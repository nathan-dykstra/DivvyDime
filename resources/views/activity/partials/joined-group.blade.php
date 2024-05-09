<div class="notification notification-link" onclick="openLink('{{ route('groups.show', $notification->group->id) }}')">
    @if ($notification->creator === $notification->recipient) <!-- Current User accepted the invite (joined the Group) -->
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div>{{ __('You joined ') }}<span class="bold-username">{{ $notification->group->name }}</span></div>

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
    @else <!-- A new member joined the current User's group -->
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div><span class="bold-username">{{ $notification->sender_username }}</span>{{ __(' joined ') }}<span class="bold-username">{{ $notification->group->name }}</span></div>

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
