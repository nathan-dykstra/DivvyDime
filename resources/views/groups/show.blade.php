<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ $group->name }}</h2>
            <div class="btn-container-end">
                <x-primary-button icon="fa-solid fa-receipt icon" :href="route('expenses.create', $group->is_default ? '' : ['group' => $group->id])">{{ __('Add Expense') }}</x-primary-button>
                <x-primary-button icon="fa-solid fa-scale-balanced icon">{{ __('Settle Up') }}</x-primary-button>

                <x-dropdown>
                    <x-slot name="trigger">
                        <x-primary-button icon="fa-solid fa-ellipsis-vertical" />
                    </x-slot>

                    <x-slot name="content">
                        <a class="dropdown-item">
                            <i class="fa-solid fa-scale-unbalanced"></i>
                            <div>{{ __('Balances') }}</div>
                        </a>
                        <a class="dropdown-item">
                            <i class="fa-solid fa-calculator"></i>
                            <div>{{ __('Totals') }}</div>
                        </a>
                        @if (!$group->is_default)
                            <a class="dropdown-item" href="{{ route('groups.settings', $group) }}">
                                <i class="fa-solid fa-gear"></i>
                                <div>{{ __('Settings') }}</div>
                            </a>
                        @endif
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'group-created')
        <x-session-status>{{ __('Group created.') }}</x-session-status>
    @endif

    <div class="metrics-container margin-bottom-lg">
        @if ($overall_balance > 0)
            <div class="metric-container text-success">
                <span class="text-small">{{ __('Overall, you are owed') }}</span>
                <span class="metric-number">{{ __('$') . number_format($overall_balance, 2) }}</span>
            </div>
        @elseif ($overall_balance < 0)
            <div class="metric-container text-warning">
                <span class="text-small">{{ __('Overall, you owe') }}</span>
                <span class="metric-number">{{ __('$') . number_format(abs($overall_balance), 2) }}</span>
            </div>
        @else
            <div class="metric-container text-success">
                <span class="text-small">{{ __('Overall, you owe') }}</span>
                <span class="metric-number">{{ __('$') . number_format(abs($overall_balance), 2) }}</span>
            </div>
        @endif
        
        @foreach ($individual_balances as $individual_balance)
            @if ($individual_balance->balance > 0)
                <div class="metric-container">
                    <span class="text-primary text-small"><span class="bold-username">{{ $individual_balance->username }}</span>{{ __(' owes you') }}</span>
                    <span class="text-success metric-number">{{ __('$') . number_format($individual_balance->balance, 2) }}</span>
                </div>
            @elseif ($individual_balance->balance < 0)
                <div class="metric-container">
                    <span class="text-primary text-small">{{ __('You owe ') }}<span class="bold-username">{{ $individual_balance->username }}</span></span>
                    <span class="text-warning metric-number">{{ __('$') . number_format(abs($individual_balance->balance), 2) }}</span>
                </div>   
            @else
                <div class="metric-container">
                    <span class="text-primary text-small">{{ __('You and ') }}<span class="bold-username">{{ $individual_balance->username }}</span>{{ __(' are') }}</span>
                    <span class="text-success metric-number">{{ __('Settled Up!') }}</span>
                </div>
            @endif
        @endforeach

        @if ($hidden_balances_count > 0)
            <div class="metric-container">
                <span class="text-primary text-small">{{ __('Plus ') . $hidden_balances_count . __(' other ') }} {{ $hidden_balances_count > 1 ? __('balances') : __('balance') }}</span>
                <x-link-button class="width-content" :href="route('groups.show', $group)">{{ __('View all') }}</x-link-button> <!-- TODO: Change this to link to the Group Balances page -->
            </div>
        @endif
    </div>

    <div class="inline-expenses-list">
        @foreach ($expenses as $expense)
            @if ($expense->payer === auth()->user()->id) <!-- Current User paid for the expense -->
                <div class="expense" onclick="openLink('{{ route('expenses.show', $expense->id) }}')">
                    <div>
                        @if ($expense->is_payment || $expense->is_settle_all_balances)
                            <div class="expense-amount text-small">{{ __('You paid ') }}<span class="bold-username">{{ $expense->payee->username }}</span>{{ __(' $') . $expense->amount }}</div>
                        @else
                            <h4>{{ $expense->name }}</h4>
                        @endif

                        @if (!($expense->is_payment  || $expense->is_settle_all_balances))
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
                    @elseif ($expense->is_payment || $expense->is_settle_all_balances)
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
                        @if ($expense->is_payment || $expense->is_settle_all_balances)
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

                        @if (!($expense->is_payment || $expense->is_settle_all_balances))
                            <div class="expense-amount text-small"><span class="bold-username">{{ $expense->payer_user->username }}</span>{{ ($expense->is_reimbursement ? __(' received $') : __(' paid $')) . $expense->amount }}</div> 
                        @endif

                        <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $expense->date }}">
                            <div class="text-shy width-content">{{ $expense->formatted_date }}</div>
                        </x-tooltip>
                    </div>

                    @if ($expense->borrowed == 0)
                        <div class="user-amount text-shy text-small">{{ __('Not involved') }}</div>
                    @else
                        @if ($expense->is_reimbursement)
                            <div class="user-amount text-success">
                                <div class="text-small">{{ __('You receive') }}</div>
                                <div class="user-amount-value">{{ __('$') . $expense->borrowed }}</div>
                            </div>
                        @elseif ($expense->is_payment || $expense->is_settle_all_balances)
                            <div class="user-amount text-warning">
                                <div class="text-small">{{ __('You receieved') }}</div>
                                <div class="user-amount-value">{{ __('$') . $expense->borrowed }}</div>
                            </div>
                        @else
                            <div class="user-amount text-warning">
                                <div class="text-small">{{ __('You borrowed') }}</div>
                                <div class="user-amount-value">{{ __('$') . $expense->borrowed }}</div>
                            </div>
                        @endif
                    @endif
                </div>
            @endif
        @endforeach
    </div>
</x-app-layout>
