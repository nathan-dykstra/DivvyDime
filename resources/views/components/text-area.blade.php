@props(['class' => '', 'id' => null, 'name' => null, 'disabled' => false, 'placeholder' => null, 'maxlength' => null, 'rows' => '4'])

<textarea {{ $attributes->merge([
    'class' => 'text-area ' . $class,
    'id' => $id,
    'name' => $name,
    'placeholder' => $placeholder,
    'maxlength' => $maxlength,
    'rows' => $rows,
]) }} {{ $disabled ? 'disabled' : '' }}></textarea>

<style>
    .text-area {
        color: var(--text-primary);
        background-color: var(--background);
        border: 1px solid var(--border-grey);
        border-radius: var(--border-radius);
        width: 100%;
        margin-top: 4px;
        transition: border 0.3s;
        min-height: 70px;
        max-height: 300px;
    }

    .text-area:focus {
        outline: 2px solid var(--blue-hover); /* TODO: Change this to --primary-color */
        outline-offset: 0px;
        border-radius: var(--border-radius);
        border: 1px solid var(--background);
        box-shadow: none;
    }

    .text-area:disabled {
        background-color: var(--primary-grey);
    }

    .text-area::placeholder {
        color: var(--text-shy);
    }
</style>