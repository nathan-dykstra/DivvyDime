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
