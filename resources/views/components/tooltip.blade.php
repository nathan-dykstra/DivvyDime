@props(['side' => 'left', 'icon' => '', 'tooltip' => ''])

<div class="tooltip {{ 'tooltip-' . $side }} width-content">
    {{ $slot }}
    <span class="tooltip-text">
        @if ($icon)
            <i class="{{ $icon . ' icon'}}"></i>
        @endif
        {{ $tooltip }}
    </span>
</div>
