<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Record payment') }}</h2>

            <div class="btn-container-end">
                <x-primary-button onclick="window.history.back()">{{ __('Cancel') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    @include('payments.partials.payment-details')
</x-app-layout>
