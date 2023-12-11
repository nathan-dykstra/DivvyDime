<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ $friend?->username }}</h2>
            <div class="btn-container-end">
            </div>
        </div>
    </x-slot>


</x-app-layout>