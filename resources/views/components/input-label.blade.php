@props(['value'])

<label {{ $attributes->merge(['class' => 'label']) }}>
    {{ $value ?? $slot }}
</label>

<style>
    .label {
        color: var(--text-shy);
        font-size: 0.9em;
        font-weight: 500;
    }
</style>