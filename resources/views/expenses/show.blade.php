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
                    <div class="profile-circle-sm-placeholder"></div>
                    <div class="expense-info-breakdown-line-container">
                        <div class="expense-info-breakdown-line"></div>
                    </div>
                </div>
    
                <div class="expense-info-breakdown-right">
                    <div class="expense-info-breakdown-payer-container">
                        <div class="expense-info-breakdown-payer">
                            <span>{{ $expense->payer_user->username }}</span>
                            @if ($expense->is_reimbursement)
                                {{ __(' received ') }}
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
                                @if ($participant->id !== $expense->payer)
                                    @if ($expense->is_reimbursement)
                                        {{ __(' receives ') }}
                                    @else
                                        {{ __(' owes ') }}
                                    @endif
                                    {{ __('$') . $participant->share }}
                                @endif
                                @if ($participant->id === $expense->payer)
                                    @if ($expense->is_reimbursement)
                                        {{ __(' keeps ') }}
                                    @else
                                        {{ __(' owes ') }}
                                    @endif
                                    {{ __('$') . $participant->share }}
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
