<div class="friend-name-amount">
    <h4>{{ $friend->username }}</h4>

    @if ($friend->overall_balance > 0)
        <div class="user-amount text-success">
            <div class="text-small">{{ __('You are owed') }}</div>
            <div class="user-amount-value">{{ __('$') . number_format($friend->overall_balance, 2) }}</div>
        </div>
    @elseif ($friend->overall_balance < 0)
        <div class="user-amount text-warning">
            <div class="text-small">{{ __('You owe') }}</div>
            <div class="user-amount-value">{{ __('$') . number_format(abs($friend->overall_balance), 2) }}</div>
        </div>
    @else
        <span class="text-shy">{{ __('Settled up') }}</span>
    @endif
</div>
