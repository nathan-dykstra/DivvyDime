@props(['class' => '', 'id' => null, 'name' => null, 'disabled' => false, 'placeholder' => null, 'maxlength' => null, 'rows' => '4', 'value' => null])

<textarea {{ $attributes->merge([
    'class' => 'text-area ' . $class,
    'id' => $id,
    'name' => $name,
    'placeholder' => $placeholder,
    'maxlength' => $maxlength,
    'rows' => $rows,
]) }} {{ $disabled ? 'disabled' : '' }}>{{ $value ?? $slot }}</textarea>

<style>
    .text-area {
        color: var(--text-primary);
        background-color: var(--background);
        border: 1px solid var(--border-grey);
        border-radius: var(--border-radius);
        width: 100%;
        margin-top: 4px;
        transition: border 0.3s;
        min-height: 75px;
        max-height: 300px;
    }

    .text-area:focus {
        outline: 2px solid var(--blue-text);
        outline-offset: 0px;
        border: 1px solid var(--background);
    }

    .text-area:disabled {
        background-color: var(--primary-grey);
    }

    .text-area::placeholder {
        color: var(--text-shy);
    }
</style>
