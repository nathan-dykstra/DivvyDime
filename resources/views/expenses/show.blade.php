<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ $expense->name }}</h2>
            <div class="btn-container-end">
                <x-primary-button icon="fa-solid fa-pen-to-square icon" :href="route('expenses.edit', $expense->id)">{{ __('Edit') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    
</x-app-layout>
