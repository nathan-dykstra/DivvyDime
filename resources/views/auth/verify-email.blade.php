<x-guest-layout>
    <div class="container">
        <div class="guest-app-logo margin-bottom-lg">
            <a href="{{ route('welcome') }}">
                <h1>{{ config('app.name') }}</h1>
            </a>
        </div>

        <div class="margin-bottom-sm">
            <p class="text-shy">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </p>
        
            @if (session('status') == 'verification-link-sent')
                <p class="text-small text-success">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </p>
            @endif
        </div>

        <div class="btn-container-end">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                
                <x-no-background-button type="submit">{{ __('Log Out') }}</x-no-background-button>
            </form>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
    
                <div>
                    <x-primary-button type="submit">{{ __('Resend Verification Email') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
