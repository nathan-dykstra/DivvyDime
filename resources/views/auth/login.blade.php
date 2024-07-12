<x-guest-layout>
    <div class="container">
        <div class="guest-app-logo margin-bottom-lg">
            <a href="{{ route('welcome') }}">
                <h1>{{ config('app.name') }}</h1>
            </a>
        </div>

        <!-- Session Status -->
        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-bottom-sm">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <!-- Password -->
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" />
            </div>

            <!-- Remember Me -->
            <x-checkbox id="remember_me" name="remember">{{ __('Remember me') }}</x-checkbox>

            <div class="btn-container-end">
                @if (Route::has('password.request'))
                    <x-link-button :href="route('password.request')">{{ __('Forgot your password?') }}</x-link-button>
                @endif

                <x-primary-button type="submit">{{ __('Log in') }}</x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
