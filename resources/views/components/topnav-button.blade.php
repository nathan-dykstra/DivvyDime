@props(['type' => 'button', 'class' => '', 'id' => null, 'icon' => null, 'iconId' => null, 'onclick' => null, 'href' => null])

@if ($href)
    <a {{ $attributes->merge(['class' => 'topnav-btn ' . $class, 'id' => $id, 'onclick' => $onclick, 'href' => $href]) }}>
        @if ($icon)
            <i class="{{ $icon }}" id="{{ $iconId }}"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => $type, 'class' => 'topnav-btn ' . $class, 'id' => $id, 'onclick' => $onclick]) }}>
        @if ($icon)
            <i class="{{ $icon }}" id="{{ $iconId }}"></i>
        @endif
        {{ $slot }}
    </button>
@endif

<style>
    .topnav-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;

        transition: color 0.3s ease-in-out;

        color: var(--icon-grey);
        font-weight: 600;
    }

    .topnav-btn:hover {
        color: var(--blue-text); /* TODO: change this hover colour to --primary-colour */
        cursor: pointer;
    }
</style>
