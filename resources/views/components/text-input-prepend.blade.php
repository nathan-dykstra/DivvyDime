@props(['type' => 'text', 'class' => '', 'id' => null, 'name' => null, 'disabled' => false, 'autocomplete' => 'off', 'placeholder' => null, 'value' => null, 'prepend' => '', 'step' => null, 'min' => null, 'max' => null, 'oninput' => null])

<div class="text-input-prepend-container">
    <div class="prepend-container">{{ $prepend }}</div>

    <input {{ $attributes->merge([
        'type' => $type,
        'class' => 'text-input-prepend ' . $class,
        'id' => $id,
        'name' => $name,
        'autocomplete' => $autocomplete,
        'placeholder' => $placeholder,
        'value' => $value,
        'step' => $step,
        'min' => $min,
        'max' => $max,
        'oninput' => $oninput,
    ]) }} {{ $disabled ? 'disabled' : '' }}>
</div>

<style>
    .text-input-prepend-container {
        display: grid;
        grid-template-columns: 20% auto;
        border: 1px solid var(--border-grey);
        border-radius: var(--border-radius);
    }

    @media (max-width: 640px) {
        .text-input-prepend-container {
            grid-template-columns: 30% auto;
        }
    }

    .prepend-container {
        display: flex;
        justify-content: center;
        align-items: center;
        border-right: 1px solid var(--border-grey);
        border-radius: var(--border-radius) 0 0 var(--border-radius);
        color: var(--text-primary);
        font-size: 1.1em;
        font-weight: 600;
        background-color: var(--secondary-grey-hover);
    }

    .text-input-prepend {
        color: var(--text-primary);
        border: none;
        border-radius: 0 var(--border-radius) var(--border-radius) 0;
        background-color: var(--secondary-grey);
        width: 100%;
    }

    .text-input-prepend:focus {
        outline: 2px solid var(--blue-hover); /* TODO: Change this to --primary-color */
        outline-offset: 0px;
        border-radius: var(--border-radius);
        border: none;
        box-shadow: none;
    }

    .text-input-prepend:disabled {
        background-color: var(--primary-grey);
    }

    .text-input-prepend::placeholder {
        color: var(--text-shy);
    }
</style>
