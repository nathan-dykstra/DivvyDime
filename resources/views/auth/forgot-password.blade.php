<x-guest-layout>
    <div class="container">
        <div class="guest-app-logo margin-bottom-lg">
            <h1>DivvyDime</h1>
        </div>

        <!-- Session Status -->
        <x-auth-session-status :status="session('status')" />
    
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            
            <div class="space-bottom-sm">
                <p class="text-shy">
                    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                </p>

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="btn-container-end">
                    <x-primary-button type="submit">{{ __('Email Password Reset Link') }}</x-primary-button>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>
