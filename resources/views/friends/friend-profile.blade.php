<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ $friend->username }}</h2>
            <div class="btn-container-end">
                <x-primary-button icon="fa-solid fa-receipt icon" :href="route('expenses.create', ['friend' => $friend->id])">{{ __('Add Expense') }}</x-primary-button>
                <x-primary-button icon="fa-solid fa-scale-balanced icon">{{ __('Settle Up') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    <div class="metrics-container margin-bottom-lg">
        @if ($overall_balance > 0)
            <div class="metric-container text-success">
                <span class="text-small">{{ __('Overall, ') }}<span class="bold-username">{{ $friend->username }}</span>{{ __(' owes you') }}</span>
                <span class="metric-number">{{ __('$') . number_format($overall_balance, 2) }}</span>
            </div>
        @elseif ($overall_balance < 0)
            <div class="metric-container text-warning">
                <span class="text-small">{{ __('Overall, you owe ') }}<span class="bold-username">{{ $friend->username }}</span></span>
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
                    <span class="text-primary text-small"><span class="bold-username">{{ $friend->username }}</span>{{ __(' owes you') }}</span>
                    <span class="text-success metric-number">{{ __('$') . number_format($group_balance->balance, 2) }}</span>
                </div>
            @elseif ($group_balance->balance < 0)
                <div class="metric-container">
                    <a href="{{ route('groups.show', $group_balance->group_id) }}" class="metric-group">{{ $group_balance->name }}</a>
                    <span class="text-primary text-small">{{ __('You owe ') }}<span class="bold-username">{{ $friend->username }}</span></span>
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

    <div class="inline-expenses-list">
        @foreach ($expenses as $expense)
            @if ($expense->payer === auth()->user()->id) <!-- Current User paid for the expense -->
                <div class="expense" onclick="openLink('{{ route('expenses.show', $expense->id) }}')">
                    <div>
                        <div class="expense-name">
                            @if ($expense->is_payment)
                                <div class="expense-amount text-small">{{ __('You paid ') }}<span class="bold-username">{{ $expense->payee->username }}</span>{{ __(' $') . $expense->amount }}</div>
                            @else
                                <h4>{{ $expense->name }}</h4>
                            @endif
                            <a class="metric-group" href="{{ route('groups.show', $expense->group->id) }}">{{ $expense->group->name }}</a>
                        </div>

                        @if (!$expense->is_payment)
                            <div class="expense-amount text-small">{{ ($expense->is_reimbursement ? __('You received $') : __('You paid $')) . $expense->amount }}</div>
                        @endif

                        <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $expense->date }}">
                            <div class="text-shy width-content">{{ $expense->formatted_date }}</div>
                        </x-tooltip>
                    </div>

                    @if ($expense->is_reimbursement)
                        <div class="user-amount text-warning">
                            <div class="text-small">{{ __('You owe') }}</div>
                            <div class="user-amount-value">{{ __('$') . $expense->lent }}</div>
                        </div>
                    @elseif ($expense->is_payment)
                        <div class="user-amount text-success">
                            <div class="text-small">{{ __('You paid') }}</div>
                            <div class="user-amount-value">{{ __('$') . $expense->lent }}</div>
                        </div>
                    @else
                        <div class="user-amount text-success">
                            <div class="text-small">{{ __('You lent') }}</div>
                            <div class="user-amount-value">{{ __('$') . $expense->lent }}</div>
                        </div>
                    @endif
                </div>
            @else <!-- Friend paid for the expense -->
                <div class="expense" onclick="openLink('{{ route('expenses.show', $expense->id) }}')">
                    <div>
                        <div class="expense-name">
                            @if ($expense->is_payment)
                                <div class="expense-amount text-small">
                                    <span class="bold-username">{{ $expense->payer_user->username }}</span>
                                    {{ __(' paid ') }}
                                    @if ($expense->payee->id === auth()->user()->id)
                                        {{ __('you') }}
                                    @else
                                        <span class="bold-username">{{ $expense->payee->username }}</span>
                                    @endif
                                    {{ __(' $') . $expense->amount }}
                                </div>
                            @else
                                <h4>{{ $expense->name }}</h4>
                            @endif
                            <a class="metric-group" href="{{ route('groups.show', $expense->group->id) }}">{{ $expense->group->name }}</a>
                        </div>

                        @if (!$expense->is_payment)
                            <div class="expense-amount text-small">
                                <span class="bold-username">{{ $expense->payer_user->username }}</span>{{ ($expense->is_reimbursement ? __(' received $') : __(' paid $')) . $expense->amount }}
                            </div>
                        @endif

                        <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $expense->date }}">
                            <div class="text-shy width-content">{{ $expense->formatted_date }}</div>
                        </x-tooltip>
                    </div>

                    @if ($expense->is_reimbursement)
                        <div class="user-amount text-success">
                            <div class="text-small">{{ __('You receive') }}</div>
                            <div class="user-amount-value">{{ __('$') . $expense->borrowed }}</div>
                        </div>
                    @elseif ($expense->is_payment)
                        <div class="user-amount text-warning">
                            <div class="text-small">{{ __('You received') }}</div>
                            <div class="user-amount-value">{{ __('$') . $expense->borrowed }}</div>
                        </div>
                    @else
                        <div class="user-amount text-warning">
                            <div class="text-small">{{ __('You borrowed') }}</div>
                            <div class="user-amount-value">{{ __('$') . $expense->borrowed }}</div>
                        </div>
                    @endif
                </div>
            @endif
        @endforeach
    </div>
</x-app-layout>
