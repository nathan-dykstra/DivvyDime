<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Add expense') }}</h2>
            <div class="btn-container-end">
                <x-primary-button :href="route('expenses')">{{ __('Cancel') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    <!-- TODO: remove these temp labels -->
    @if ($group)
        <h2>Group Here!</h2>
    @endif
    @if ($friend)
        <h2>Friend Here!</h2>
    @endif

    @include('expenses.partials.expense-details')
</x-app-layout>
