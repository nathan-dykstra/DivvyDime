<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ $expense->name }}</h2>

            <div class="btn-container-end">
                <x-primary-button icon="fa-solid fa-pen-to-square icon" :href="route('expenses.edit', $expense)">{{ __('Edit') }}</x-primary-button>

                <x-dropdown>
                    <x-slot name="trigger">
                        <x-primary-button icon="fa-solid fa-ellipsis-vertical" />
                    </x-slot>

                    <x-slot name="content">
                        <a class="dropdown-item">
                            <i class="fa-solid fa-camera"></i>
                            <div>{{ __('Add Image') }}</div>
                        </a>
                        <a class="dropdown-item" x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-expense')">
                            <i class="fa-solid fa-trash-can"></i>
                            <div>{{ __('Delete') }}</div>
                        </a>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'expense-created')
        <x-session-status>{{ __('Expense created.') }}</x-session-status>
    @elseif (session('status') === 'expense-updated')
        <x-session-status>{{ __('Expense updated.') }}</x-session-status>
    @endif

    <div class="expense-info-container">
        <div>
            <div class="expense-info-amount">
                {{ __('$') . $expense->amount }}
            </div>
    
            <div class="expense-info-added-date text-shy">
                {{ __('Added ') }}
                <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $expense->created_date . __(' at ') . $expense->created_time }}">
                    <span class="width-content">{{ $expense->formatted_created_date }}</span>
                </x-tooltip>
                {{ __(' by ') }}<span >{{ $expense->creator_user->username }}</span>
            </div>
    
            @if ($expense->created_at->toDateTimeString() !== $expense->updated_at->toDateTimeString())
                <div class="text-shy">
                    {{ __('Updated ') }}
                    <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $expense->updated_date . __(' at ') . $expense->updated_time }}">
                        <span class="width-content">{{ $expense->formatted_updated_date }}</span>
                    </x-tooltip>
                </div>
            @endif
    
            <div class="expense-info-breakdown margin-top-lg">
                <div class="expense-info-breakdown-left">
                    <div class="expense-info-payer-circle"></div>
                    <div class="expense-info-breakdown-line-container">
                        <div class="expense-info-breakdown-line"></div>
                    </div>
                </div>
    
                <div class="expense-info-breakdown-right">
                    <div class="expense-info-breakdown-payer-container">
                        <div class="expense-info-breakdown-payer">
                            <span>{{ $expense->payer_user->username }}</span>
                            @if ($expense->is_reimbursement)
                                {{ __(' was paid ') }}
                            @else
                                {{ __(' paid ') }}
                            @endif
                            {{ __('$') . $expense->amount }}
                        </div>
                    </div>
    
                    <div class="space-top-xs">
                        @foreach ($participants as $participant)
                            <div class="expense-info-participant text-shy">
                                {{ $participant->username }}
                                @if ($expense->is_reimbursement)
                                    {{ __(' receives ') }}
                                @else
                                    {{ __(' owes ') }}
                                @endif
                                {{ __('$') . $participant->share }}
    
                                @if ($participant->id === $expense->payer)
                                    {{ __(' and ') }}
                                    @if ($expense->is_reimbursement)
                                        {{ __(' owes ') }}
                                    @else
                                        {{ __(' receives ') }}
                                    @endif
                                    {{ __('$') . number_format($expense->amount - $participant->share, 2) }}
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div>
            @if ($expense->note)
                <div class="expense-info-note text-small">
                    <p>{!! nl2br(e($expense->note)) !!}</p>
                </div>
            @endif
        </div>  
    </div>

    @include('expenses.partials.expense-delete-modal')
</x-app-layout>

<style>
    /* Override the default margin-top for this page */
    /*.main-content {
        margin-top: var(--navbar-height);
    }*/

    .expense-info-container {
        display: grid;
        grid-template-columns: 2fr 2fr;
        gap: 32px;
        color: var(--text-primary);
    }

    @media screen and (max-width: 768px) {
        .expense-info-container {
            grid-template-columns: 1fr;
        }
    }

    .expense-info-amount {
        font-weight: 800;
        font-size: 1.75em;
    }

    .expense-info-added-date {
    }

    .expense-info-participant {
    }

    .expense-info-breakdown {
        display: grid;
        grid-template-columns: 40px auto;
        gap: 16px;
    }

    .expense-info-breakdown-left {
        display: grid;
        grid-template-rows: 40px auto;
        gap: 8px;
    }

    .expense-info-payer-circle {
        background-color: var(--primary-grey);
        border: 1px solid var(--border-grey);
        height: 40px;
        width: 40px;
        border-radius: 50%;
    }
    .expense-info-breakdown-line-container {
        display: flex;
        justify-content: center;
    }

    .expense-info-breakdown-line {
        width: 3px;
        border-radius: 1.5px;
        background-color: var(--secondary-grey);
    }

    .expense-info-breakdown-right{
        display: grid;
        grid-template-rows: 40px auto;
        gap: 8px;
    }

    .expense-info-breakdown-payer-container {
        display: flex;
        align-items: center;
    }

    .expense-info-note {
        border: 1px solid var(--border-grey);
        border-radius: var(--border-radius);
        padding: 8px 16px;
        background-color: var(--secondary-grey);
        word-wrap: break-word;
    }
</style>
