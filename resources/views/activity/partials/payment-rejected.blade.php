<div class="notification notification-link" data-notification-id="{{ $notification->id }}" onclick="openLink('{{ route('payments.show', $notification->expense->id) }}')">        
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div>
                        @if ($notification->creator === $notification->recipient) <!-- Current user added the payment -->
                            {{ __('You') }}
                        @else
                            <span class="bold-username">{{ $notification->sender_username }}</span>
                        @endif
                        {{ __(' paid ') }}
                        @if ($notification->recipient === auth()->user()->id)
                            {{ __('you') }}
                        @else
                            <span class="bold-username">{{ $notification->payee->username }}</span>
                        @endif
                        {{ __(' $') . number_format($notification->expense->amount, 2) }}
                        @if ($notification->is_settle_all_balances)
                            {{ __(' to settle all balances') }}
                        @else
                            {{ __(' in ') }}<span class="bold-username">{{ $notification->group->name }}</span>
                        @endif
                    </div>

                    <div class="text-sm text-warning"><i class="fa-solid fa-triangle-exclamation fa-sm icon"></i>{{ __('This payment was rejected') }}</div>

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
</div>
