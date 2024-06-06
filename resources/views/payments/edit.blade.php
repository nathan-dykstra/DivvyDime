<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Edit payment') }}</h2>
            <div class="btn-container-end">
                <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-payment')" icon="fa-solid fa-trash-can icon">{{ __('Delete') }}</x-primary-button>
                <x-primary-button onclick="window.history.back()">{{ __('Cancel') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    @include('payments.partials.payment-details')

    @include('payments.partials.payment-delete-modal')
</x-app-layout>
