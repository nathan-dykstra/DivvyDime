@props(['type' => 'search', 'class' => '', 'placeholder' => 'Search...', 'autocomplete' => 'off'])

<input {{ $attributes->merge(['type' => $type, 'class' => 'mobile-search-input-primary ' . $class, 'placeholder' => $placeholder, 'autocomplete' => $autocomplete]) }}>
    {{ $slot }}

<style>
    .mobile-search-input-primary {
        background-color: inherit !important;
        height: var(--searchbar-primary-height);
        width: 100%;
        border: none;
        background-color: var(--secondary-grey) !important;
        font-size: 1em;
        display: inline-flex;
        flex-direction: row;
        align-items: center;
        padding: 0 12px;
    }

    .mobile-search-input-primary:focus {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
    }
</style>