<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ $expense->name }}</h2>
            <div class="btn-container-end">
                <x-primary-button icon="fa-solid fa-pen-to-square icon" :href="route('expenses.edit', $expense)">{{ __('Edit') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'expense-created')
        <x-session-status>{{ __('Expense created.') }}</x-session-status>
    @elseif (session('status') === 'expense-updated')
        <x-session-status>{{ __('Expense updated.') }}</x-session-status>
    @endif

    This is an expense.
</x-app-layout>
