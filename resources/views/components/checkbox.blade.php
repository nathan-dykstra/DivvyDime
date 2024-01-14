@props(['class' => '', 'id' => null, 'name' => null, 'disabled' => false])

<label for="{{ $id }}" class="checkbox-label">
    <input {{ $attributes->merge([
        'id' => $id,
        'type' => 'checkbox',
        'class' => 'checkbox ' . $class,
        'name' => $name
    ]) }} {{ $disabled ? 'disabled' : '' }}>
    <span class="checkbox-label-text">{{ $slot }}</span>
</label>

<style>
    .checkbox {
        color: var(--blue-hover); /* TODO: Change this to --primary-color */
        background-color: var(--background);
        border: 1px solid var(--border-grey);
        border-radius: 0.25rem;
        transition: outline 0.1s ease-in-out, outline-offset 0.1s;
        margin-right: 4px;
    }

    .checkbox:focus {
        outline: 2px solid var(--blue-hover); /* TODO: Change this to --primary-color */
        outline-offset: 1px;
        border-radius: 0.25rem;
        border: 1px solid var(--background);
        box-shadow: none;
    }

    .checkbox-label {
        display: inline-flex;
        align-items: center;
    }

    .checkbox-label:hover, .checkbox:hover {
        cursor: pointer;
    }

    .checkbox-label-text {
        color: var(--text-shy);
        font-size: 0.9em;
        font-weight: 500;
    }
</style>
