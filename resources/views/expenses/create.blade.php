<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ __('New Expense') }}
    </x-slot>

    <x-slot name="back_btn"></x-slot>

    <x-slot name="header_title">
        {{ __('Add expense') }}
    </x-slot>

    <!-- Content -->

    @include('expenses.partials.expense-details')
</x-app-layout>
