<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ __('New Group') }}
    </x-slot>
    
    <x-slot name="back_btn"></x-slot>
    
    <x-slot name="header_title">
        {{ __('Create a group') }}
    </x-slot>

    <!-- Content -->

    @include('groups.partials.group-details')
</x-app-layout>
