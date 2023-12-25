<x-app-layout>
    <x-slot name="header">
        <h2>Profile</h2>
    </x-slot>

    @if (session('status') === 'profile-updated')
        <x-session-status>{{ __('Profile updated.') }}</x-session-status>
    @elseif (session('status') === 'preferences-updated')
        <x-session-status>{{ __('Preferences updated.') }}</x-session-status>
    @elseif (session('status') === 'password-updated')
        <x-session-status>{{ __('Password updated.') }}</x-session-status>
    @endif

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
