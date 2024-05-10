<div class="notification notification-link" data-notification-id="{{ $notification->id }}" onclick="openLink('{{ route('expenses.show', $notification->expense->id) }}')">
    <div class="notification-grid">
        <div class="notification-content">
            <div>
                <div>
                    @if ($notification->sender === $notification->recipient) <!-- Current user added the expense -->
                        {{  __('You') }}
                    @else
                        <span class="bold-username">{{ $notification->sender_username }}</span>
                    @endif
                    {{ __(' added "') . $notification->expense->name . __('" in ')}}<span class="bold-username">{{ $notification->group->name }}</span>
                </div>

                @if ($notification->recipient === $notification->expense->payer) <!-- Current user paid for the expense -->
                    @if ($notification->is_reimbursement)
                        <div class="text-warning text-small">{{ __('You owe $') . $notification->amount_lent }}</div>
                    @else
                        <div class="text-success text-small">{{ __('You lent $') . $notification->amount_lent }}</div>
                    @endif
                @else
                    @if ($notification->amount_borrowed == 0)
                        <div class="text-shy">{{ __('Not involved') }}</div>
                    @else
                        @if ($notification->is_reimbursement)
                            <div class="text-success text-small">{{ __('You are owed $') . $notification->amount_borrowed }}</div>
                        @else
                            <div class="text-warning text-small">{{ __('You borrowed $') . $notification->amount_borrowed }}</div>
                        @endif
                    @endif
                @endif

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
