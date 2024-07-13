@foreach ($expenses as $expense)
    @if ($expense->payer === auth()->user()->id) <!-- Current User paid for the expense -->
        <div class="expense" onclick="openLink('{{ $expense->is_payment ? route('payments.show', $expense->id) : route('expenses.show', $expense->id) }}')">
            <div>
                @if ($expense->is_payment)
                    <div class="expense-amount text-small">{{ __('You paid ') }}<span class="bold-username">{{ $expense->payee->username }}</span>{{ __(' $') . $expense->amount }}</div>
                @else
                    <h4>{{ $expense->name }}</h4>
                @endif

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
            <div>
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

                @if (!$expense->is_payment)
                    <div class="expense-amount text-small"><span class="bold-username">{{ $expense->payer_user->username }}</span>{{ ($expense->is_reimbursement ? __(' received $') : __(' paid $')) . $expense->amount }}</div> 
                @endif

                <div class="text-shy text-thin-caps">{{ $expense->formatted_date }}</div>
            </div>

            @if ($expense->borrowed == 0)
                <div class="user-amount text-shy text-small">{{ __('Not involved') }}</div>
            @else
                @if ($expense->is_reimbursement)
                    <div class="user-amount text-success">
                        <div class="text-small">{{ __('You receive') }}</div>
                        <div class="user-amount-value">{{ __('$') . $expense->borrowed }}</div>
                    </div>
                @elseif ($expense->is_payment)
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
