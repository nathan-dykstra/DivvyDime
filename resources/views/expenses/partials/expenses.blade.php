<div class="expenses">
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

                    <div class="expense-amount text-small"><span class="bold-username">{{ $expense->payer_user->username }}</span>{{ __(' paid $') . $expense->amount }}</div> 

                    <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $expense->date }}">
                        <div class="text-shy width-content">{{ $expense->formatted_date }}</div>
                    </x-tooltip>
                </div>

                @if ($expense->borrowed == 0)
                    <div class="user-amount text-shy text-small">{{ __('Not involved') }}</div>
                @else
                    <div class="user-amount text-warning">
                        <div class="text-small">{{ __('You borrowed') }}</div>
                        <div class="user-amount-value">{{ __('$') . number_format($expense->borrowed, 2) }}</div>
                    </div>
                @endif
            </div>
        @endif
    @endforeach
</div>
