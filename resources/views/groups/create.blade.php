<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('Create a group') }}</h2>
    </x-slot>

    @include('groups.partials.group-details')
</x-app-layout>