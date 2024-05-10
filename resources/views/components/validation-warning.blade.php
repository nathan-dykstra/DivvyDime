@props(['id' => null])

<div
    {{ $attributes->merge(['class' => 'session-status validation-warning hidden', 'id' => $id]) }}
>
    <p class="text-warning">{{ $slot }}</p>
    <x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="closeValidationWarning(this)"/>
</div>
