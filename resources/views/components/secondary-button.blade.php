@props(['type' => 'button', 'class' => '', 'id' => null, 'icon' => '', 'iconId' => null, 'form' => '', 'onclick' => null])

@if ($form) 
    <button {{ $attributes->merge(['class' => 'secondary-btn ' . $class, 'id' => $id, 'form' => $form]) }}>
        @if ($icon)
            <i class="{{ $icon }}" id="{{ $iconId }}"></i>
        @endif
        {{ $slot }}
    </button>
@else
    <button {{ $attributes->merge(['type' => $type, 'class' => 'secondary-btn ' . $class, 'id' => $id, 'onclick' => $onclick]) }}>
        @if ($icon)
            <i class="{{ $icon }}" id="{{ $iconId }}"></i>
        @endif
        {{ $slot }}
    </button>
@endif

<style>
    .secondary-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        
        border: 1px solid var(--icon-grey);
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

    .secondary-btn:hover {
        background-color: var(--primary-grey-hover);
        cursor: pointer;
    }

    .secondary-btn:focus {
        outline: 3px solid var(--blue-hover); /* TODO: Change this to --primary-color */
        outline-offset: 1px;
        border: 1px solid var(--primary-grey-hover);
        border-radius: var(--border-radius);
        box-shadow: none;
    }
</style>
