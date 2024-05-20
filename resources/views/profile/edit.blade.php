<x-app-layout>
    <x-slot name="header">
        <h2>{{ __('Settings') }}</h2>
    </x-slot>

    @if (session('status') === 'profile-updated')
        <x-session-status>{{ __('Profile updated.') }}</x-session-status>
    @elseif (session('status') === 'preferences-updated')
        <x-session-status>{{ __('Preferences updated.') }}</x-session-status>
    @elseif (session('status') === 'password-updated')
        <x-session-status>{{ __('Password updated.') }}</x-session-status>
    @elseif (session('status') === 'profile-image-uploaded')
        <x-session-status>{{ __('Profile image uploaded.') }}</x-session-status>
    @elseif (session('status') === 'profile-image-deleted')
        <x-session-status>{{ __('Profile image deleted.') }}</x-session-status>
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
        
        @if (count($groups))
            <div class="container">
                <div class="restrict-max-width">
                    @include('profile.partials.group-settings')
                </div>
            </div>
        @endif

        <div class="container">
            <div class="restrict-max-width">
                @include('profile.partials.account-settings')
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function submitLogOutForm() {
        document.getElementById('log-out-form').submit();
    }
</script>
