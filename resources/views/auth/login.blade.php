<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="container">
        <div class="guest-app-logo margin-bottom-lg">
            <h1>DivvyDime</h1>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="space-bottom-sm">
                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" />
                
                    <x-text-input id="password" class="block mt-1 w-full"
                                    type="password"
                                    name="password"
                                    required autocomplete="current-password" />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me -->
                <x-checkbox id="remember_me" name="remember">{{ __('Remember me') }}</x-checkbox>

                <div class="btn-container-end">
                    @if (Route::has('password.request'))
                        <x-link-button route="password.request">{{ __('Forgot your password?') }}</x-link-button>
                    @endif
    
                    <x-primary-button type="submit" class="no-focus">{{ __('Log in') }}</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>
