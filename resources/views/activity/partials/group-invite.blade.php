<div class="notification">
    @if ($notification->creator === $notification->recipient) <!-- Current User sent the invite -->
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div>{{ __('You sent ') }}<span class="notification-username">{{ $notification->username }}</span>{{ __(' an invite to ') }}<span class="notification-username">{{ $notification->group->name }}</span></div>

                    <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $notification->date . ' at ' . $notification->formatted_time }}">
                        <div class="text-shy width-content">{{ $notification->formatted_date }}</div>
                    </x-tooltip>
                </div>

                <div class="text-warning"><i class="fa-solid fa-triangle-exclamation fa-sm icon"></i>{{ __('This invite is pending.') }}</div>
            </div>

            <div class="delete-notification-btn-container">
                <x-tooltip side="left" tooltip="{{ __('Delete Notification') }}">
                    <i class="fa-solid fa-trash-can delete-notification-btn" onclick="deleteNotification(event, {{ $notification->id }})"></i>
                </x-tooltip>
            </div>
        </div>
    @else <!-- Current User receiving the invite -->
        <div class="notification-content">
            <div>
                <div><span class="notification-username">{{ $notification->username }}</span>{{ __(' sent you an invite to ') }}<span class="notification-username">{{ $notification->group->name }}</span></div>

                <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $notification->date . ' at ' . $notification->formatted_time }}">
                    <div class="text-shy width-content">{{ $notification->formatted_date }}</div>
                </x-tooltip>
            </div>

            <div class="btn-container-start">
                <x-primary-button class="primary-color-btn" onclick="acceptGroupInvite({{ $notification->id }}, {{ $notification->group->id }})">{{ __('Accept') }}</x-primary-button>
                <x-secondary-button onclick="rejectGroupInvite($(this), {{ $notification->id }})">{{ __('Reject') }}</x-secondary-button>
            </div>
        </div>
    @endif
</div>
