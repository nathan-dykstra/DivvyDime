@props(['type' => 'button', 'class' => '', 'id' => null, 'icon' => null, 'href' => null, 'onclick' => null, 'disabled' => false])

@if ($href)
    <a {{ $attributes->merge(['class' => 'no-background-btn ' . $class, 'id' => $id, 'onclick' => $onclick, 'href' => $href]) }} {{ $disabled ? 'disabled' : '' }}>
        @if ($icon)
            <i class="{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => $type, 'class' => 'no-background-btn ' . $class, 'id' => $id, 'onclick' => $onclick]) }} {{ $disabled ? 'disabled' : '' }}>
        @if ($icon) 
            <i class="{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </button>
@endif

<style>
    .no-background-btn {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 36px;
        padding: 8px 16px;
        color: var(--icon-grey);
        border-radius: var(--border-radius);
        transition: background-color 0.1s ease;
    }

    .no-background-btn:hover {
        cursor: pointer;
        background-color: var(--secondary-grey-hover);
    }

    .no-background-btn:focus-visible {
        outline: 3px solid var(--blue-text);
        outline-offset: 1px;
        border-radius: var(--border-radius);
    }
</style>
