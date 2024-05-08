<x-modal name="delete-payment" focusable>
    <form method="post" action="{{ route('payments.destroy', $payment) }}" class="space-bottom-sm">
        @csrf
        @method('delete')

        <div>
            <h3>{{ __('Delete payment') }}</h3>
            <p class="text-shy">
                {{ __('Are you sure you want to delete this payment? This action does not move any money. If you already transferred funds for this payment, you will have to reconcile that outside of the DivvyDime app. This action cannot be undone.') }}
            </p>
        </div>

        <div class="btn-container-end">
            <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
            <x-danger-button type="submit">{{ __('Delete Payment') }}</x-danger-button>
        </div>
    </form>
</x-modal>
