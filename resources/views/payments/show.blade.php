<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Payment') }}</h2>

            <div class="btn-container-end">
                @if (auth()->user()->id === $payment->creator)
                    <x-primary-button icon="fa-solid fa-pen-to-square icon" :href="route('payments.edit', $payment)">{{ __('Edit') }}</x-primary-button>

                    <x-dropdown>
                        <x-slot name="trigger">
                            <x-primary-button icon="fa-solid fa-ellipsis-vertical" />
                        </x-slot>

                        <x-slot name="content">
                            <a class="dropdown-item">
                                <i class="fa-solid fa-camera"></i>
                                <div>{{ __('Add Image') }}</div>
                            </a>
                            <a class="dropdown-item" x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-payment')">
                                <i class="fa-solid fa-trash-can"></i>
                                <div>{{ __('Delete') }}</div>
                            </a>
                        </x-slot>
                    </x-dropdown>
                @endif
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'payment-created')
        <x-session-status>{{ __('Payment created.') }}</x-session-status>
    @elseif (session('status') === 'payment-updated')
        <x-session-status>{{ __('Payment updated.') }}</x-session-status>
    @endif

    <div>
        <h1>{{ __('$') . $payment->amount }}</h1>

        <div class="expense-info-date-group-category">
            <div class="text-shy text-thin-caps payment-info-date">{{ $payment->formatted_date }}</div>
            @if ($payment->is_settle_all_balances)
                <div class="metric-group">{{ __('Settle All Balances') }}</div>
            @else
                <a class="metric-group metric-group-hover" href="{{ route('groups.show', $payment->group->id) }}">{{ $payment->group->name }}</a>
            @endif
            <a class="metric-group">{{ __('Category') }}</a>
        </div>
    </div>

    <div class="margin-top-lg space-top-sm">
        <div class="text-primary payment-info-user-amounts">
            <div class="profile-circle-sm-placeholder"></div>
            <div class="expense-info-breakdown-payer-container">
                <div class="expense-info-breakdown-payer">
                        @if ($payment->payer === auth()->user()->id)
                            {{ __('You') }}
                        @else
                            <span class="bold-username">{{ $payment->payer_user->username }}</span>
                        @endif
                    </span>
                    {{ __(' paid ') . __('$') . $payment->amount }}
                </div>
            </div>
        </div>
    
        <div class="text-primary payment-info-user-amounts">
            <div class="profile-circle-sm-placeholder"></div>
            <div class="expense-info-breakdown-payer-container">
                <div class="expense-info-breakdown-payer">
                        @if ($payment->payee->id === auth()->user()->id)
                            {{ __('You') }}
                        @else
                            <span class="bold-username">{{ $payment->payee->username }}</span>
                        @endif
                    </span>
                    {{ __(' received ') . __('$') . $payment->amount }}
                </div>
            </div>
        </div>
    </div>

    <div class="horizontal-center margin-top-lg">
        <div class="text-shy">
            {{ __('Added ') }}
            <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $payment->created_date . __(' at ') . $payment->created_time }}">
                <span class="width-content">{{ $payment->formatted_created_date }}</span>
            </x-tooltip>
            {{ __(' by ') }}<span class="bold-username">{{ $payment->creator_user->username }}</span>
        </div>
    
        @if ($payment->created_at->toDateTimeString() !== $payment->updated_at->toDateTimeString())
            <div class="text-shy">
                {{ __('Updated ') }}
                <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $payment->updated_date . __(' at ') . $payment->updated_time }}">
                    <span class="width-content">{{ $payment->formatted_updated_date }}</span>
                </x-tooltip>
                {{ __(' by ') }}<span class="bold-username">{{ $payment->updator_user->username }}</span>
            </div>
        @endif
    </div>

    @include('payments.partials.payment-delete-modal')
</x-app-layout>
