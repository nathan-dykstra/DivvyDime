<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ __('Edit Payment') }}
    </x-slot>

    <x-slot name="back_btn"></x-slot>

    <x-slot name="header_title">
        {{ __('Edit payment') }}
    </x-slot>

    <x-slot name="header_buttons">
        <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-payment')" icon="fa-solid fa-trash-can icon">{{ __('Delete') }}</x-primary-button>
    </x-slot>

    <x-slot name="mobile_overflow_options">
        <div class="dropdown-item warning-hover" x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-payment')">
            <i class="fa-solid fa-trash-can"></i>
            <div>{{ __('Delete') }}</div>
        </div>
    </x-slot>

    <!-- Content -->

    @include('payments.partials.payment-details')

    @include('payments.partials.payment-delete-modal')
</x-app-layout>
