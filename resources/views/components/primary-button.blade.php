@props(['type' => 'button', 'class' => '', 'id' => null, 'icon' => null, 'iconId' => null, 'form' => null, 'href' => null, 'onclick' => null, 'disabled' => false])

@if ($href)
    <a {{ $attributes->merge(['class' => 'primary-btn ' . $class, 'id' => $id, 'onclick' => $onclick, 'href' => $href]) }} {{ $disabled ? 'disabled' : '' }}>
        @if ($icon)
            <i class="{{ $icon }}" id="{{ $iconId }}"></i>
        @endif
        {{ $slot }}
    </a>
@elseif ($form) 
    <button {{ $attributes->merge(['class' => 'primary-btn ' . $class, 'id' => $id, 'form' => $form]) }} {{ $disabled ? 'disabled' : '' }}>
        @if ($icon) 
            <i class="{{ $icon }}" id="{{ $iconId }}"></i>
        @endif
        {{ $slot }}
    </button>
@else
    <button {{ $attributes->merge(['type' => $type, 'class' => 'primary-btn ' . $class, 'id' => $id, 'onclick' => $onclick]) }} {{ $disabled ? 'disabled' : '' }}>
        @if ($icon) 
            <i class="{{ $icon }}" id="{{ $iconId }}"></i>
        @endif
        {{ $slot }}
    </button>
@endif

<style>
    .primary-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        
        height: 36px;
        background-color: var(--primary-grey);
        border: 1px solid var(--border-grey);
        border-radius: var(--border-radius);
        padding: 8px 16px;
        transition: border 0.3s, background-color 0.3s ease-in-out, outline 0.1s ease-in-out, outline-offset 0.1s;

        font-size: 0.8em;
        font-weight: 700;
        color: var(--text-heading);
        text-transform: uppercase;
        letter-spacing: 1px;

        outline: none;
    }

    .primary-btn:hover {
        background-color: var(--primary-grey-hover);
        border: 1px solid var(--border-grey-hover);
        cursor: pointer;
    }

    .primary-btn:focus-visible {
        outline: 3px solid var(--blue-hover); /* TODO: Change this to --primary-color */
        outline-offset: 1px;
    }
</style>
