<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Payment') }}</h2>

            <div class="btn-container-end">
                @if (auth()->user()->id === $payment->payer)
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
    @elseif (session('status') === 'payment-confirmed')
        <x-session-status>{{ __('Payment confirmed.') }}</x-session-status>
    @elseif (session('status') === 'payment-rejected')
        <x-session-status>{{ __('Payment rejected.') }}</x-session-status>
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

        @if (auth()->user()->id === $payment->payer && $payment->is_rejected)
            <!-- Payment rejected (payer): Show options -->
            <div class="info-container red-background-text margin-top-sm">
                <div>
                    <i class="fa-solid fa-triangle-exclamation fa-lg text-warning"></i>
                </div>
                <div class="space-top-xs">
                    <span class="bold-username">{{ $payment->payee->username }}</span>{{ __(' rejected your payment. Make sure you sent the money and the payment information you added is correct.') }}

                    <div class="btn-container-start">
                        <x-secondary-button href="{{ route('payments.edit', $payment) }}">{{ __('Edit Payment') }}</x-secondary-button>
                    </div>
                </div>
            </div>
        @elseif (auth()->user()->id === $payment->payer && !$payment->is_confirmed)
            <!-- Payment pending (payer) -->
            <div class="text-sm text-yellow margin-top-sm"><i class="fa-solid fa-triangle-exclamation fa-sm icon"></i>{{ __('This payment is pending') }}</div>
        @elseif (auth()->user()->id === $payment->payee->id && !$payment->is_confirmed && !$payment->is_rejected)
            <!-- Payment pending (payee): Show options -->
            <div class="info-container yellow-background-text margin-top-sm">
                <div>
                    <i class="fa-solid fa-triangle-exclamation fa-lg text-yellow"></i>
                </div>
                <div class="space-top-xs">
                    {{ __('Did you receive this payment from ') }}<span class="bold-username">{{ $payment->payer_user->username }}</span>{{ __('? Your balances will not be adjusted until you confirm the payment.') }}

                    <div class="btn-container-start">
                        <x-secondary-button onclick="submitConfirmPayment()">{{ __('Confirm') }}</x-secondary-button>
                        <x-secondary-button onclick="submitRejectPayment()">{{ __('Reject') }}</x-secondary-button>
                    </div>
                </div>
            </div>
        @elseif (auth()->user()->id === $payment->payee->id && $payment->is_rejected)
            <!-- Payment rejected (payee): Show options -->
            <div class="info-container red-background-text margin-top-sm">
                <div>
                    <i class="fa-solid fa-triangle-exclamation fa-lg text-warning"></i>
                </div>
                <div class="space-top-xs">
                    {{ __('You rejected this payment from ') }}<span class="bold-username">{{ $payment->payer_user->username }}</span>{{ __('. If you change your mind, you can still confirm the payment.') }}

                    <div class="btn-container-start">
                        <x-secondary-button onclick="submitConfirmPayment()">{{ __('Confirm Payment') }}</x-secondary-button>
                    </div>
                </div>
            </div>
        @else 
            <!-- Payment confirmed (both) -->
            <div class="text-sm text-success margin-top-sm"><i class="fa-solid fa-check fa-sm icon"></i>{{ __('This payment was confirmed') }}</div>
        @endif
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
            <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $payment->created_date . __(' at ') . $payment->created_time }}">
                {{ __('Added ') }}
                <span class="width-content">{{ $payment->formatted_created_date }}</span>
                {{ __(' by ') }}<span class="bold-username">{{ $payment->creator_user->username }}</span>
            </x-tooltip>
        </div>

        @if ($payment->created_at->toDateTimeString() !== $payment->updated_at->toDateTimeString())
            <div class="text-shy">
                <x-tooltip side="bottom" icon="fa-solid fa-calendar-days" tooltip="{{ $payment->updated_date . __(' at ') . $payment->updated_time }}">
                    {{ __('Updated ') }}
                    <span class="width-content">{{ $payment->formatted_updated_date }}</span>
                    {{ __(' by ') }}<span class="bold-username">{{ $payment->updator_user->username }}</span>
                </x-tooltip>
            </div>
        @endif
    </div>

    <form id="confirm-payment-form" action="{{ route('payments.confirm', $payment) }}" method="POST">
        @csrf

        <input type="hidden" name="payment_id" value="{{ $payment->id }}"/>
    </form>

    <form id="reject-payment-form" action="{{ route('payments.reject', $payment) }}" method="POST">
        @csrf

        <input type="hidden" name="payment_id" value="{{ $payment->id }}"/>
    </form>

    @include('payments.partials.payment-delete-modal')
</x-app-layout>

<script>
    function submitConfirmPayment() {
        $('#confirm-payment-form').submit();
    }

    function submitRejectPayment() {
        $('#reject-payment-form').submit();
    }
</script>
