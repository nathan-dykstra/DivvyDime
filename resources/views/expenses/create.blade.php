<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Add an expense') }}</h2>
            <div class="btn-container-end">
                <x-primary-button :href="route('expenses')">{{ __('Cancel') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    @include('expenses.partials.expense-details')
</x-app-layout>
