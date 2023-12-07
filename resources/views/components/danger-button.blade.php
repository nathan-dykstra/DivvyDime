@props(['type' => 'button', 'class' => '', 'id' => null, 'icon' => '', 'iconId' => null, 'form' => '', 'onclick' => null])

@if ($form) 
    <button {{ $attributes->merge(['class' => 'danger-btn ' . $class, 'id' => $id, 'form' => $form]) }}>
        @if ($icon) 
            <i class="{{ $icon }}" id="{{ $iconId }}"></i>
        @endif
        {{ $slot }}
    </button>
@else
    <button {{ $attributes->merge(['type' => $type, 'class' => 'danger-btn ' . $class, 'id' => $id, 'onclick' => $onclick]) }}>
        @if ($icon) 
            <i class="{{ $icon }}" id="{{ $iconId }}"></i>
        @endif
        {{ $slot }}
    </button>
@endif

<style>
    .danger-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        
        background-color: var(--danger);
        border-radius: var(--border-radius);
        padding: 8px 16px;
        transition: 0.3s border 0.3s, background-color 0.3s ease-in-out, outline 0.1s ease-in-out, outline-offset 0.1s;

        font-size: 0.8em;
        font-weight: 700;
        color: var(--text-white);
        text-transform: uppercase;
        letter-spacing: 1px;

        outline: none;
    }

    .danger-btn:hover {
        background-color: var(--danger-hover);
        cursor: pointer;
    }

    .danger-btn:focus {
        outline: 3px solid var(--danger-hover);
        outline-offset: 1px;
        border-radius: var(--border-radius);
        box-shadow: none;
    }
</style>
