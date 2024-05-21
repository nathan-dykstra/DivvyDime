@props(['type' => 'button', 'class' => '', 'id' => null, 'icon' => null, 'iconId' => null, 'href' => null, 'onclick' => null])

@if ($href)
    <a {{ $attributes->merge(['class' => 'icon-btn ' . $class, 'id' => $id, 'href' => $href]) }}>
        <i class="{{ $icon }}" id="{{ $iconId }}"></i>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => $type, 'class' => 'icon-btn ' . $class, 'id' => $id, 'onclick' => $onclick]) }}>
        <i class="{{ $icon }}" id="{{ $iconId }}"></i>
        {{ $slot }}
    </button>
@endif

<style>
    .icon-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        color: var(--icon-grey);
        transition: color 0.3s ease-in-out;
        border-radius: var(--border-radius);

    }

    .icon-btn:hover {
        color: var(--blue-hover);
        cursor: pointer;
    }

    .icon-btn:focus-visible {
        outline: 3px solid var(--blue-hover); /* TODO: Change this to --primary-color */
        outline-offset: 4px;
    }
</style>
