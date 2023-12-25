@props(['innerClass' => 'text-success', 'timeout' => '4000'])

<div
    x-data="{ show: true }"
    x-show="show"
    x-transition
    x-init="setTimeout(() => show = false, {{ $timeout }})"
    class="session-status"
    >
        <p class="{{ $innerClass }}">{{ $slot }}</p>
</div>
