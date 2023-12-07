<x-guest-layout>
    <div class="container">
        <div class="guest-app-logo animate-drop">
            <h1>DivvyDime</h1>
        </div>
    
        <div class="guest-app-slogan margin-bottom-lg animate-fade-in">
            <h3>Divvy up and start saving, one dime at a time!</h3>
        </div>
    
        <div class="btn-container-end">
            <x-secondary-button route="login">{{ __('Log in') }}</x-primary-button>
            <x-primary-button class="primary-color-btn" route="register">{{ __('Register') }}</x-primary-button>
        </div>
    </div>
</x-guest-layout>

<style>
    /* Logo animation */

    .animate-drop {
        opacity: 0;
        animation: drop-in 0.6s ease forwards;
    }

    @keyframes drop-in {
        0% {
            transform: translateY(-60px);
        }
        60% {
            transform: translateY(20px);
        }
        80% {
            transform: translateY(-5px);
        }
        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Slogan animation */

    .animate-fade-in {
        opacity: 0;
        animation: fade-in 0.5s ease 0.5s forwards;
    }

    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>