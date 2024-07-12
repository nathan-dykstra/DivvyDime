<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ __('New Payment') }}
    </x-slot>

    <x-slot name="back_btn"></x-slot>

    <x-slot name="header_title">
        {{ __('Add payment') }}
    </x-slot>

    <!-- Content -->

    @include('payments.partials.payment-details')
</x-app-layout>
