<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Edit expense') }}</h2>
            <div class="btn-container-end">
                <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-expense')" icon="fa-solid fa-trash-can icon">{{ __('Delete') }}</x-primary-button>
                <x-primary-button :href="route('expenses.show', $expense)">{{ __('Cancel') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    @include('expenses.partials.expense-details')

    <x-modal name="delete-expense" focusable>
        <form method="post" action="{{ route('expenses.destroy', $expense) }}" class="space-bottom-sm">
            @csrf
            @method('delete')

            <div>
                <h3>{{ __('Delete expense') }}</h3>
                <p class="text-shy">
                    {{ __('Are you sure you want to delete this expense? The expense will be deleted for everyone involved. This action cannot be undone.') }}
                </p>
            </div>

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                <x-danger-button type="submit">{{ __('Delete Expense') }}</x-danger-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
