<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ __('Edit Expense') }}
    </x-slot>

    <x-slot name="back_btn"></x-slot>

    <x-slot name="header_title">
        {{ __('Edit expense') }}
    </x-slot>

    <x-slot name="header_buttons">
        <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-expense')" icon="fa-solid fa-trash-can icon">{{ __('Delete') }}</x-primary-button>
    </x-slot>

    <x-slot name="mobile_overflow_options">
        <div class="dropdown-item warning-hover" x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-expense')">
            <i class="fa-solid fa-trash-can"></i>
            <div>{{ __('Delete') }}</div>
        </div>
    </x-slot>

    <!-- Content -->

    @include('expenses.partials.expense-details')

    @include('expenses.partials.expense-delete-modal')
</x-app-layout>
