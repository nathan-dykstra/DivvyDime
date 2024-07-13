<x-guest-layout>
    <div class="container">
        <div class="guest-app-logo margin-bottom-lg">
            <a href="{{ route('welcome') }}">
                <h1>{{ config('app.name') }}</h1>
            </a>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="space-bottom-sm">
                <!-- Name -->
                <div>
                    <x-input-label for="username" :value="__('Username')" />
                    <x-text-input id="username" type="text" name="username" :value="old('username')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('username')" />
                </div>

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Password')" />
                    <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                    <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" />
                </div>

                <div class="btn-container-end">
                    <x-no-background-button :href="route('login')">{{ __('Already registered?') }}</x-no-background-button>
                    <x-primary-button type="submit">{{ __('Register') }}</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>
