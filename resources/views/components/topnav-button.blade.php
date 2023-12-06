@props(['type' => 'button', 'class' => '', 'id' => '', 'icon' => '', 'iconId' => '', 'onclick' => ''])

<button {{ $attributes->merge(['type' => $type, 'class' => 'topnav-btn ' . $class, 'id' => $id, 'onclick' => $onclick]) }}>
    <i class="{{ $icon }}" id="{{ $iconId }}"></i>
    {{ $slot }}
</button>

<style>
    .topnav-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        color: var(--icon-grey);
        transition: color 0.3s ease-in-out;
    }

    .topnav-btn:hover {
        color: var(--blue-hover); /* TODO: change this hover colour to --primary-colour */
        cursor: pointer;
    }
</style>
