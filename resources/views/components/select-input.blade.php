@props(['class' => '', 'id' => null, 'name' => null, 'disabled' => false, 'multiple' => null])

<select {{ $attributes->merge([
    'class' => 'select-input ' . $class,
    'id' => $id,
    'name' => $name,
    'multiple' => $multiple,
]) }}>
    {{ $slot }}
</select>

<style>
    .select-input {
        color: var(--text-primary);
        background-color: var(--background);
        border: 1px solid var(--border-grey);
        border-radius: var(--border-radius);
        width: 100%;
        margin-top: 4px;
        transition: border 0.3s;
    }

    .select-input:focus {
        outline: 2px solid var(--blue-hover); /* TODO: Change this to --primary-color-hover */
        outline-offset: 0px;
        border-radius: var(--border-radius);
        border: 1px solid var(--background);
        box-shadow: none;
    }

    .select-input::placeholder {
        color: var(--text-shy);
    }

    .option {
        padding: 8px 12px !important;
    }
</style>