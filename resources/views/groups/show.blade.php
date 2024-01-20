<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ $group->name }}</h2>
            <div class="btn-container-end">
                <x-primary-button icon="fa-solid fa-receipt icon">{{ __('Add Expense') }}</x-primary-button>
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

    @foreach ($expenses as $expense)
        @if ($expense->payer === auth()->user()->id) <!-- Current User paid for the expense -->
            <div class="expense" onclick="openExpense('{{ route('expenses.show', $expense->id) }}')">
                <div>
                    <h4>{{ $expense->name }}</h4>

                    <div class="expense-amount text-small">{{ __('You paid $') . $expense->amount }}</div>

                    <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $expense->date . __(' at ') . $expense->formatted_time }}">
                        <div class="text-shy width-content">{{ $expense->formatted_date }}</div>
                    </x-tooltip>
                </div>

                <div class="user-amount text-success">
                    <div class="text-small">{{ __('You lent') }}</div>
                    <div class="user-amount-value">{{ __('$') . number_format($expense->lent, 2) }}</div>
                </div>
            </div>
        @else <!-- Friend paid for the expense -->
            <div class="expense" onclick="openExpense('{{ route('expenses.show', $expense->id) }}')">
                <div>
                    <h4 class="expense-name-text">{{ $expense->name }}</h4>

                    <div class="expense-amount text-small"><a class="notification-username notification-username-link" href="{{ route('friends.show', $expense->payer) }}">{{ $expense->payer_user->username }}</a>{{ __(' paid $') . $expense->amount }}</div> 

                    <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $expense->date . __(' at ') . $expense->formatted_time }}">
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
</x-app-layout>
