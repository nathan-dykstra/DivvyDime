<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ __('New Expense') }}
    </x-slot>

    <x-slot name="back_btn">
        <x-no-background-button class="mobile-header-btn" icon="fa-solid fa-arrow-left" onclick="window.history.back()" />
    </x-slot>

    <x-slot name="header_title">
        {{ __('Add expense') }}
    </x-slot>

    <!-- Content -->

    @include('expenses.partials.expense-details')
</x-app-layout>
