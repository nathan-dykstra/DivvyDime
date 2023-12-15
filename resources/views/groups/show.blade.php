<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ $group?->name }}</h2>
            <div class="btn-container-end">
                <x-primary-button icon="fa-solid fa-gear icon" :href="route('groups.settings', $group)">{{ __('Settings') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    <p>Hello World! This is a group.</p>

</x-app-layout>