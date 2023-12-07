@props(['type' => 'button', 'class' => '', 'id' => null, 'icon' => '', 'iconId' => null, 'onclick' => ''])

<button {{ $attributes->merge(['type' => $type, 'class' => 'topnav-btn ' . $class, 'id' => $id, 'onclick' => $onclick]) }}>
    @if ($icon)
        <i class="{{ $icon }}" id="{{ $iconId }}"></i>
    @endif
    {{ $slot }}
</button>

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
        color: var(--blue-hover); /* TODO: change this hover colour to --primary-colour */
        cursor: pointer;
    }
</style>
