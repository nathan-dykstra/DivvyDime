<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Create a group') }}</h2>
            <div class="btn-container-end">
                <x-primary-button :href="route('groups')">{{ __('Cancel') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    @include('groups.partials.group-details')
</x-app-layout>
