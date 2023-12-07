@props(['type' => 'text', 'class' => '', 'disabled' => false])

<input {{ $attributes->merge(['type' => $type, 'class' => 'text-input ' . $class]) }} {{ $disabled ? 'disabled' : '' }}>

<style>
    .text-input {
        color: var(--text-primary);
        background-color: var(--background);
        border: 1px solid var(--border-grey);
        border-radius: var(--border-radius);
        width: 100%;
        transition: 0.1s ease-in-out;
    }

    .text-input:focus {
        outline: 2px solid var(--blue-hover); /* TODO: Change this to --primary-color */
        outline-offset: 0px;
        border-radius: var(--border-radius);
        border: 1px solid var(--background);
        box-shadow: none;
    }
</style>