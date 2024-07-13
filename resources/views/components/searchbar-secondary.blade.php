@props(['type' => 'search', 'class' => '', 'placeholder' => 'Search', 'autocomplete' => 'off'])

<input {{ $attributes->merge([
    'type' => $type,
    'class' => 'search-input-secondary ' . $class,
    'placeholder' => $placeholder,
    'autocomplete' => $autocomplete
]) }}>
    {{ $slot }}

<style>
    .search-input-secondary {
        color: var(--text-primary);
        background-color: var(--secondary-grey) !important;
        height: var(--searchbar-primary-height);
        width: 100%;
        font-size: 1em;
        display: inline-flex;
        flex-direction: row;
        align-items: center;
        border: none !important;
        border-radius: var(--border-radius);
        padding: 0 12px;
    }

    .search-input-secondary:focus {
        border: none !important;
        outline: 2px solid var(--blue-text) !important; /* TODO: change this to --primary-color-hover */
        outline-offset: 0 !important;
        box-shadow: none !important;
    }

    .search-input-secondary::placeholder {
        color: var(--text-shy);
    }
</style>
