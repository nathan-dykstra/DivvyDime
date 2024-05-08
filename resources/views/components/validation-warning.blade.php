@props(['id' => null])

<div
    {{ $attributes->merge(['class' => 'session-status validation-warning hidden', 'id' => $id]) }}
>
    <p class="text-warning">{{ $slot }}</p>
    <x-topnav-button onclick="closeValidationWarning(this)">{{ __('Dismiss') }}</x-topnav-button>
</div>
