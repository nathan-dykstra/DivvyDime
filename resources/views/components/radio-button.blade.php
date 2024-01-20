@props(['class' => '', 'id' => null, 'name' => null, 'disabled' => false])

<label for="{{ $id }}" class="radio-label">
    <input {{ $attributes->merge([
        'id' => $id,
        'type' => 'radio',
        'class' => 'radio ' . $class,
        'name' => $name
    ]) }} {{ $disabled ? 'disabled' : '' }}>
    <span class="radio-label-text">{{ $slot }}</span>
</label>
