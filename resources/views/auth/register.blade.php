<x-guest-layout>
    <div class="container">
        <div class="guest-app-logo margin-bottom-lg">
            <h1>DivvyDime</h1>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="space-bottom-sm">
                <!-- Name -->
                <div>
                    <x-input-label for="username" :value="__('Username')" />
                    <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('username')" />
                </div>

                <!-- Email Address -->
                <div class="mt-4">
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />
                
                    <x-text-input id="password" class="block mt-1 w-full"
                                    type="password"
                                    name="password"
                                    required autocomplete="new-password" />
                
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

                    <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                    type="password"
                                    name="password_confirmation" required autocomplete="new-password" />

                    <x-input-error :messages="$errors->get('password_confirmation')" />
                </div>

                <div class="btn-container-end">
                    <x-link-button route="login">{{ __('Already registered?') }}</x-link-button>
                    
                    <x-primary-button type="submit">{{ __('Register') }}</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>
