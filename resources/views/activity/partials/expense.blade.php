<div class="notification notification-link" onclick="openLink('{{ route('expenses.show', $notification->expense->id) }}')">
    @if ($notification->sender === $notification->recipient) <!-- You paid for the Expense -->
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div>{{ __('You added "') . $notification->expense->name . __('" in ') }}<span class="bold-username">{{ $notification->group->name }}</span></div>

                    @if ($notification->is_reimbursement_expense)
                        <div class="text-warning text-small">{{ __('You owe $') . number_format($notification->amount_lent, 2) }}</div>
                    @else
                        <div class="text-success text-small">{{ __('You lent $') . number_format($notification->amount_lent, 2) }}</div>
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
    @else <!-- A Friend or Group member paid for the Expense -->
        <div class="notification-grid">
            <div class="notification-content">
                <div>
                    <div><span class="bold-username">{{ $notification->payer_username }}</span> {{ __(' added "') . $notification->expense->name . __('" in ')}}<span class="bold-username">{{ $notification->group->name }}</span></div>

                    @if ($notification->amount_borrowed == 0)
                        <div class="text-shy">{{ __('Not involved') }}</div>
                    @else
                        @if ($notification->is_reimbursement_expense)
                            <div class="text-success text-small">{{ __('You received $') . number_format($notification->amount_borrowed, 2) }}</div>
                        @else
                            <div class="text-warning text-small">{{ __('You borrowed $') . number_format($notification->amount_borrowed, 2) }}</div>
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
    @endif
</div>
