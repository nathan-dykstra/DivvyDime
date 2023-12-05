@props(['type' => 'search', 'class' => '', 'placeholder' => 'Search...', 'autocomplete' => 'off'])

<input {{ $attributes->merge(['type' => $type, 'class' => 'search-input-primary ' . $class, 'placeholder' => $placeholder, 'autocomplete' => $autocomplete]) }}>
    {{ $slot }}

<style>
    .search-input-primary {
        background-color: inherit !important;
        height: var(--searchbar-primary-height);
        width: var(--searchbar-primary-width);
        font-size: 1em;
        display: inline-flex;
        flex-direction: row;
        align-items: center;
        border-radius: 0.5rem 0 0 0.5rem;
        border: none !important;
        padding: 0 12px;
    }

    .search-input-primary:focus {
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
    }
</style>
