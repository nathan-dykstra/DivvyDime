@props(['type' => 'text', 'class' => '', 'id' => null, 'name' => null, 'disabled' => false, 'autocomplete' => 'off', 'placeholder' => null, 'value' => null])

<input {{ $attributes->merge([
    'type' => $type,
    'class' => 'text-input ' . $class,
    'id' => $id,
    'name' => $name,
    'autocomplete' => $autocomplete,
    'placeholder' => $placeholder,
    'value' => $value,
]) }} {{ $disabled ? 'disabled' : '' }}>

<style>
    .text-input {
        color: var(--text-primary);
        background-color: var(--background);
        border: 1px solid var(--border-grey);
        border-radius: var(--border-radius);
        width: 100%;
        margin-top: 4px;
        transition: border 0.3s;
    }

    .text-input:focus {
        outline: 2px solid var(--blue-text); /* TODO: Change this to --primary-color */
        outline-offset: 0px;
        border-radius: var(--border-radius);
        border: 1px solid var(--background);
        box-shadow: none;
    }

    .text-input:disabled {
        background-color: var(--primary-grey);
    }

    .text-input::placeholder {
        color: var(--text-shy);
    }
</style>
