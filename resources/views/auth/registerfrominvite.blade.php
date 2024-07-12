<x-guest-layout>
    <div class="container">
        <div class="guest-app-logo margin-bottom-lg">
            <a href="{{ route('welcome') }}">
                <h1>{{ config('app.name') }}</h1>
            </a>
        </div>

        <form method="POST" action="{{ $invite->group_id !== null ? route('register.store-from-group-invite', $invite->token) : route('register.storefrominvite', $invite->token) }}">
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
                    <x-text-input id="email" :value="$invite->email" required disabled />
                    <x-text-input type="email" name="email" :value="$invite->email" hidden />
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
                    <x-link-button :href="route('login')">{{ __('Already registered?') }}</x-link-button>
                    <x-primary-button type="submit">{{ __('Register') }}</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>
