<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ $friend->username }}</h2>
            <div class="btn-container-end">
            </div>
        </div>
    </x-slot>

    <div class="metrics-container margin-bottom-lg">
        @if ($overall_balance > 0)
            <div class="metric-container text-success">
                <span class="text-small">{{ __('Overall, ') . $friend->username . __(' owes you') }}</span>
                <span class="metric-number">{{ __('$') . number_format($overall_balance, 2) }}</span>
            </div>
        @elseif ($overall_balance < 0)
            <div class="metric-container text-warning">
                <span class="text-small">{{ __('Overall, you owe ') . $friend->username }}</span>
                <span class="metric-number">{{ __('$') . number_format(abs($overall_balance), 2) }}</span>
            </div>
        @else
            <div class="metric-container text-success">
                <span class="text-small">{{ __('Your balances are settled') }}</span>
            </div>
        @endif

        @foreach ($group_balances as $group_balance)
            @if ($group_balance->balance > 0)
                <div class="metric-container">
                    <a href="{{ route('groups.show', $group_balance->group_id) }}" class="metric-group">{{ $group_balance->name }}</a>
                    <span class="text-primary text-small">{{ $friend->username . __(' owes you') }}</span>
                    <span class="text-success metric-number">{{ __('$') . number_format($group_balance->balance, 2) }}</span>
                </div>
            @elseif ($group_balance->balance < 0)
                <div class="metric-container">
                    <a href="{{ route('groups.show', $group_balance->group_id) }}" class="metric-group">{{ $group_balance->name }}</a>
                    <span class="text-primary text-small">{{ __('You owe ') . $friend->username }}</span>
                    <span class="text-warning metric-number">{{ __('$') . number_format(abs($group_balance->balance), 2) }}</span>
                </div>
            @else
                <div class="metric-container">
                    <a href="{{ route('groups.show', $group_balance->group_id) }}" class="metric-group">{{ $group_balance->name }}</a>
                    <span class="text-primary text-small">{{ __('You are settled up') }}</span>
                </div>
            @endif
        @endforeach
    </div>

    @foreach ($expenses as $expense)
        @if ($expense->payer === auth()->user()->id) <!-- Current User paid for the expense -->
            <div class="expense" onclick="openLink('{{ route('expenses.show', $expense->id) }}')">
                <div>
                    <div class="expense-name">
                        <h4>{{ $expense->name }}</h4>
                        <a class="metric-group" href="{{ route('groups.show', $expense->group->id) }}">{{ $expense->group->name }}</a>
                    </div>

                    <div class="expense-amount text-small">{{ __('You paid $') . $expense->amount }}</div>

                    <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $expense->date }}">
                        <div class="text-shy width-content">{{ $expense->formatted_date }}</div>
                    </x-tooltip>
                </div>

                <div class="user-amount text-success">
                    <div class="text-small">{{ __('You lent') }}</div>
                    <div class="user-amount-value">{{ __('$') . number_format($expense->lent, 2) }}</div>
                </div>
            </div>
        @else <!-- Friend paid for the expense -->
            <div class="expense" onclick="openLink('{{ route('expenses.show', $expense->id) }}')">
                <div>
                    <div class="expense-name">
                        <h4 class="expense-name-text">{{ $expense->name }}</h4>
                        <a class="metric-group" href="{{ route('groups.show', $expense->group->id) }}">{{ $expense->group->name }}</a>
                    </div>

                    <div class="expense-amount text-small">
                        @if ($friend->id === $expense->payer) 
                            <span class="notification-username">{{ $expense->payer_user->username }}</span>
                        @else
                            <a class="notification-username notification-username-link" href="{{ route('friends.show', $expense->payer) }}">{{ $expense->payer_user->username }}</a>
                        @endif
                        {{ __(' paid $') . $expense->amount }}
                    </div> 

                    <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $expense->date }}">
                        <div class="text-shy width-content">{{ $expense->formatted_date }}</div>
                    </x-tooltip>
                </div>

                <div class="user-amount text-warning">
                    <div class="text-small">{{ __('You borrowed') }}</div>
                    <div class="user-amount-value">{{ __('$') . number_format($expense->borrowed, 2) }}</div>
                </div>
            </div>
        @endif
    @endforeach
</x-app-layout>
