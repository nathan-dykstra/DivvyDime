<x-app-layout>
    <x-slot name="header">
        <h2>Dashboard</h2>
    </x-slot>

    <!-- Title & Header -->

    <x-slot name="header_title">
        {{ $greeting . ', ' . $current_user->username }}
    </x-slot>

    <p>You're logged in! More features coming to this page soon...</p>
</x-app-layout>
