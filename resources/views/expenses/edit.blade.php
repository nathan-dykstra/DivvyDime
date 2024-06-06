<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Edit expense') }}</h2>
            <div class="btn-container-end">
                <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-expense')" icon="fa-solid fa-trash-can icon">{{ __('Delete') }}</x-primary-button>
                <x-primary-button onclick="window.history.back()">{{ __('Cancel') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    @include('expenses.partials.expense-details')

    @include('expenses.partials.expense-delete-modal')
</x-app-layout>
