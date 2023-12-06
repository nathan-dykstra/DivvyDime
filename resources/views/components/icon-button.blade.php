@props(['type' => 'button', 'class' => '', 'id' => '', 'icon' => '', 'iconId' => '', 'onclick' => ''])

<button {{ $attributes->merge(['type' => $type, 'class' => 'icon-btn ' . $class, 'id' => $id, 'onclick' => $onclick]) }}>
    <i class="{{ $icon }}" id="{{ $iconId }}"></i>
    {{ $slot }}
</button>

<style>
    .icon-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        color: var(--icon-grey);
        transition: color 0.3s ease-in-out;
    }

    .icon-btn:hover {
        color: var(--blue-hover);
        cursor: pointer;
    }
</style>
