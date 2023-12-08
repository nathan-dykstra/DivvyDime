<x-app-layout>
    <x-slot name="header">
        <h2>Profile</h2>
    </x-slot>

    <div class="space-bottom-lg">
        <div class="container">
            <div class="restrict-max-width">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="container">
            <div class="restrict-max-width">
                @include('profile.partials.update-preferences-form')
            </div>
        </div>

        <div class="container">
            <div class="restrict-max-width">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="container">
            <div class="restrict-max-width">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
