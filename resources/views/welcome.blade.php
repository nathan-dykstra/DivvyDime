<x-guest-layout>
    <div class="container">
        <div class="guest-app-logo">
            <h1>DivvyDime</h1>
        </div>
    
        <div class="guest-app-slogan">
            <h3>Divvy up and start saving, one dime at a time!</h3>
        </div>
    
        <div class="guest-action-btns-container">
            <a href="{{ route('login') }}">
                <x-primary-button class="ms-4">
                    {{ __('Log In') }}
                </x-primary-button>
            </a>
            <a href="{{ route('register') }}">
                <x-primary-button class="ms-4">
                    {{ __('Register') }}
                </x-primary-button>
            </a>
        </div>
    </div>
</x-guest-layout>