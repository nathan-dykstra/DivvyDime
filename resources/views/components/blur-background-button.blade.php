@props(['type' => 'button', 'class' => '', 'id' => null, 'icon' => null, 'href' => null, 'onclick' => null, 'disabled' => false])

@if ($href)
    <a {{ $attributes->merge(['class' => 'blur-background-btn ' . $class, 'id' => $id, 'onclick' => $onclick, 'href' => $href]) }} {{ $disabled ? 'disabled' : '' }}>
        @if ($icon)
            <i class="{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => $type, 'class' => 'blur-background-btn ' . $class, 'id' => $id, 'onclick' => $onclick]) }} {{ $disabled ? 'disabled' : '' }}>
        @if ($icon) 
            <i class="{{ $icon }}"></i>
        @endif
        {{ $slot }}
    </button>
@endif

<style>
    .blur-background-btn {
        display: flex;
        justify-content: center;
        align-items: center;
        color: var(--icon-grey);
        background-color: var(--background-blur-color);
        backdrop-filter: var(--background-blur-filter);
        border: 1px solid var(--border-grey);
        transition: background-color 0.3s ease-in-out;
    }

    .blur-background-btn:hover {
        background-color: var(--background-blur-color-hover);
    }

    .blur-background-btn:focus-visible {
        outline: 3px solid var(--blue-hover); /* TODO: Change this to --primary-color */
        outline-offset: 1px;
        border-radius: 50%;
    }
</style>
