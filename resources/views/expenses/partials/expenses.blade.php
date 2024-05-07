<div class="expenses">
    @foreach ($expenses as $expense)
        @if ($expense->payer === auth()->user()->id) <!-- Current User paid for the expense -->
            <div class="expense" onclick="openLink('{{ route('expenses.show', $expense->id) }}')">
                <div>
                    <div class="expense-name">
                        @if ($expense->is_payment || $expense->is_settle_all_balances)
                            <div class="expense-amount text-small">{{ __('You paid ') }}<span class="bold-username">{{ $expense->payee->username }}</span>{{ __(' $') . $expense->amount }}</div>
                        @else
                            <h4>{{ $expense->name }}</h4>
                        @endif
                        @if ($expense->is_settle_all_balances)
                            <div class="metric-group">{{ __('Settle All Balances') }}</div>
                        @else
                            <a class="metric-group metric-group-hover" href="{{ route('groups.show', $expense->groups()->first()->id) }}">{{ $expense->groups()->first()->name }}</a>
                        @endif
                    </div>

                    @if (!($expense->is_payment || $expense->is_settle_all_balances))
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
                    <div class="expense-name">
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
                        @if ($expense->is_settle_all_balances)
                            <div class="metric-group">{{ __('Settle All Balances') }}</div>
                        @else
                            <a class="metric-group metric-group-hover" href="{{ route('groups.show', $expense->groups()->first()->id) }}">{{ $expense->groups()->first()->name }}</a>
                        @endif
                    </div>

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
                            <div class="text-small">{{ __('You received') }}</div>
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
