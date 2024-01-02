<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ $group?->name }}</h2>
            <div class="btn-container-end">
                <x-primary-button icon="fa-solid fa-receipt icon">{{ __('Add Expense') }}</x-primary-button>
                <x-primary-button icon="fa-solid fa-scale-balanced icon">{{ __('Settle Up') }}</x-primary-button>

                <x-dropdown>
                    <x-slot name="trigger">
                        <x-primary-button icon="fa-solid fa-ellipsis-vertical" />
                    </x-slot>

                    <x-slot name="content">
                        <a class="dropdown-item">
                            <i class="fa-solid fa-scale-unbalanced"></i>
                            <div>{{ __('Balances') }}</div>
                        </a>
                        <a class="dropdown-item">
                            <i class="fa-solid fa-calculator"></i>
                            <div>{{ __('Totals') }}</div>
                        </a>
                        <a class="dropdown-item" href="{{ route('groups.settings', $group) }}">
                            <i class="fa-solid fa-gear"></i>
                            <div>{{ __('Settings') }}</div>
                        </a>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'group-created')
        <x-session-status>{{ __('Group created.') }}</x-session-status>
    @endif

    <p>Hello World! This is a group.</p>

</x-app-layout>