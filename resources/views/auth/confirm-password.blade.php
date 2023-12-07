<!-- To require password confirmation to access a route on the site, add the 'password.confirm' middleware to the route definition -->

<x-guest-layout>
    <div class="container">
        <div class="guest-app-logo margin-bottom-lg">
            <h1>DivvyDime</h1>
        </div>

        <div class="margin-bottom-sm">
            <p class="text-shy">
                {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
            </p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <!-- Password -->
            <div class="margin-bottom-sm">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" />
            </div>

            <div class="btn-container-end">
                <x-primary-button type="submit">{{ __('Confirm') }}</x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
