<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ $payment->name }}</h2>

            <div class="btn-container-end">
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
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'payment-created')
        <x-session-status>{{ __('Payment created.') }}</x-session-status>
    @elseif (session('status') === 'payment-updated')
        <x-session-status>{{ __('Payment updated.') }}</x-session-status>
    @endif

    <div class="expense-info-container">

    </div>

    @include('payments.partials.payment-delete-modal')
</x-app-layout>
