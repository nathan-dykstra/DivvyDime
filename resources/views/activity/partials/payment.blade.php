<div class="notification notification-link" onclick="openLink('{{ route('payments.show', $notification->expense->id) }}')">        
    @if ($notification->payee->id === $notification->recipient) <!-- Current user receiving the payment -->
        <div class="notification-content">
            <div>
                <div><span class="bold-username">{{ $notification->sender_username }}</span>{{ __(' paid you $') . number_format($notification->expense->amount, 2) . __(' in ') }}<span class="bold-username">{{ $notification->group->name }}</span></div>

                <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $notification->date . ' at ' . $notification->formatted_time }}">
                    <div class="text-shy width-content">{{ $notification->formatted_date }}</div>
                </x-tooltip>
            </div>

            <div>{{ __('Did you receive this payment?') }}</div>

            <div class="btn-container-start">
                <x-primary-button class="primary-color-btn" onclick="confirmPayment(event, {{ $notification->id }})">{{ __('Confirm') }}</x-primary-button>
                <x-secondary-button onclick="rejectPayment(event, $(this), {{ $notification->id }})">{{ __('Reject') }}</x-secondary-button>
            </div>
        </div>
    @else <!-- Current user added the payment or is not involved -->
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div>
                        @if ($notification->creator === $notification->recipient) <!-- Current user added the payment -->
                            {{ __('You') }}
                        @else
                            <span class="bold-username">{{ $notification->sender_username }}</span>
                        @endif
                        {{ __(' paid ') }}<span class="bold-username">{{ $notification->payee->username }}</span>{{ __(' $') . number_format($notification->expense->amount, 2) . __(' in ') }}<span class="bold-username">{{ $notification->group->name }}</span>
                    </div>

                    <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $notification->date . ' at ' . $notification->formatted_time }}">
                        <div class="text-shy width-content">{{ $notification->formatted_date }}</div>
                    </x-tooltip>
                </div>
                <div class="text-warning"><i class="fa-solid fa-triangle-exclamation fa-sm icon"></i>{{ __('This payment is pending.') }}</div>
            </div>

            <div class="delete-notification-btn-container">
                <x-tooltip side="left" tooltip="{{ __('Delete Notification') }}">
                    <i class="fa-solid fa-trash-can delete-notification-btn" onclick="deleteNotification(event, {{ $notification->id }})"></i>
                </x-tooltip>
            </div>
        </div>
    @endif
</div>
