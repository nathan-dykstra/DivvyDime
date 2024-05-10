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

    <h1>{{ __('$') . $expense->amount }}</h1>
    <div class="expense-info-date-group-category">
        <div class="text-shy text-thin-caps expense-info-date">{{ $expense->formatted_date }}</div>
        <a class="metric-group metric-group-hover" href="{{ route('groups.show', $expense->group->id) }}">{{ $expense->group->name }}</a>
        <a class="metric-group">{{ __('Category') }}</a>
    </div>

    <div class="expense-info-container margin-top-lg">
        <div>
            <div class="expense-info-breakdown">
                <div class="expense-info-breakdown-left">
                    <div class="profile-circle-sm-placeholder"></div>
                    <div class="expense-info-breakdown-line-container">
                        <div class="expense-info-breakdown-line"></div>
                    </div>
                </div>

                <div class="expense-info-breakdown-right">
                    <div class="expense-info-breakdown-payer-container">
                        <div class="expense-info-breakdown-payer">
                                @if ($expense->payer === auth()->user()->id)
                                    {{ __('You') }}
                                @else
                                    <span class="bold-username">{{ $expense->payer_user->username }}</span>
                                @endif
                            </span>
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
                                @if ($participant->id === auth()->user()->id)
                                    {{ __('You') }}
                                @else
                                    <span class="bold-username">{{ $participant->username }}</span>
                                @endif
                                @if ($participant->id !== $expense->payer)
                                    @if ($expense->is_reimbursement)
                                        {{ $participant->id === auth()->user()->id ? __(' receive ') : __(' receives ') }}
                                    @else
                                        {{ $participant->id === auth()->user()->id ? __(' owe ') : __(' owes ') }}
                                    @endif
                                    {{ __('$') . $participant->share }}
                                @endif
                                @if ($participant->id === $expense->payer)
                                    @if ($expense->is_reimbursement)
                                        {{ $participant->id === auth()->user()->id ? __(' keep ') : __(' keeps ') }}
                                    @else
                                        {{ $participant->id === auth()->user()->id ? __(' owe ') : __(' owes ') }}
                                    @endif
                                    {{ __('$') . $participant->share }}
                                    {{ __(' and ') }}
                                    @if ($expense->is_reimbursement)
                                        {{ $participant->id === auth()->user()->id ? __(' owe ') : __(' owes ') }}
                                    @else
                                        {{ $participant->id === auth()->user()->id ? __(' receive ') : __(' receives ') }}
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

    <div class="horizontal-center margin-top-lg">
        <div class="expense-info-added-date text-shy">
            <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $expense->created_date . __(' at ') . $expense->created_time }}">
                {{ __('Added ') }}
                <span class="width-content">{{ $expense->formatted_created_date }}</span>
                {{ __(' by ') }}<span class="bold-username">{{ $expense->creator_user->username }}</span>
            </x-tooltip>
        </div>

        @if ($expense->created_at->toDateTimeString() !== $expense->updated_at->toDateTimeString())
            <div class="text-shy">
                <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $expense->updated_date . __(' at ') . $expense->updated_time }}">
                    {{ __('Updated ') }}
                    <span class="width-content">{{ $expense->formatted_updated_date }}</span>
                    {{ __(' by ') }}<span class="bold-username">{{ $expense->updator_user->username }}</span>
                </x-tooltip>
            </div>
        @endif
    </div>

    @include('expenses.partials.expense-delete-modal')
</x-app-layout>
