@foreach ($expenses as $expense)
    @if ($expense->payer === auth()->user()->id) <!-- Current User paid for the expense -->
        <div class="expense" onclick="openLink('{{ $expense->is_payment ? route('payments.show', $expense->id) : route('expenses.show', $expense->id) }}')">
            <div class="expenses-list-category-container">
                <div class="expense-list-category {{ $expense->category['colour_class'] }}">
                    <i class="{{ $expense->category['icon_class'] }}"></i>
                </div>
            </div>

            <div>
                <div class="expense-name">
                    @if ($expense->is_payment)
                        <div class="expense-amount text-small">{{ __('You paid ') }}<span class="bold-username">{{ $expense->payee->username }}</span>{{ __(' $') . $expense->amount }}</div>
                    @else
                        <h4>{{ $expense->name }}</h4>
                    @endif
                    @if ($expense->is_settle_all_balances)
                        <div class="info-chip info-chip-truncate info-chip-green">{{ __('Settle All Balances') }}</div>
                    @else
                        <a class="info-chip info-chip-truncate info-chip-link info-chip-grey" href="{{ route('groups.show', $expense->groups()->first()->id) }}">{{ $expense->groups()->first()->name }}</a>
                    @endif
                </div>

                @if (!$expense->is_payment)
                    <div class="expense-amount text-small">{{ ($expense->is_reimbursement ? __('You received $') : __('You paid $')) . $expense->amount }}</div>
                @endif

                <div class="text-shy text-thin-caps">{{ $expense->formatted_date }}</div>
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
        <div class="expense" onclick="openLink('{{ $expense->is_payment ? route('payments.show', $expense->id) : route('expenses.show', $expense->id) }}')">
            <div class="expenses-list-category-container">
                <div class="expense-list-category {{ $expense->category['colour_class'] }}">
                    <i class="{{ $expense->category['icon_class'] }}"></i>
                </div>
            </div>

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
                    @if ($expense->is_settle_all_balances)
                        <div class="info-chip info-chip-truncate info-chip-green">{{ __('Settle All Balances') }}</div>
                    @else
                        <a class="info-chip info-chip-truncate info-chip-link info-chip-grey" href="{{ route('groups.show', $expense->groups()->first()->id) }}">{{ $expense->groups()->first()->name }}</a>
                    @endif
                </div>

                @if (!$expense->is_payment)
                    <div class="expense-amount text-small">
                        <span class="bold-username">{{ $expense->payer_user->username }}</span>{{ ($expense->is_reimbursement ? __(' received $') : __(' paid $')) . $expense->amount }}
                    </div>
                @endif

                <div class="text-shy text-thin-caps">{{ $expense->formatted_date }}</div>
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