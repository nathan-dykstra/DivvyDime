<x-app-layout>
    <x-slot name="header">
        <h2>Profile</h2>
    </x-slot>

    @if (session('status') === 'profile-updated')
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 4000)"
            class="session-status"
        >
            <p class="text-success">{{ __('Profile updated.') }}</p>
        </div>
    @elseif (session('status') === 'preferences-updated')
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 4000)"
            class="session-status"
        >
            <p class="text-success">{{ __('Preferences updated.') }}</p>
        </div>
    @elseif (session('status') === 'password-updated')
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 4000)"
            class="session-status"
        >
            <p class="text-success">{{ __('Password updated.') }}</p>
        </div>
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
