<!-- $options expects an array: [option_value => option_label] -->
@props(['options' => [], 'emptyOption' => null, 'selected' => null])

@php 
    if ($emptyOption) {
        $options = [null => $emptyOption] + $options;
    }
@endphp

@foreach ($options as $option_value => $option_label)
    @if ($selected == $option_value)
        <option value="{{ $option_value }}" selected>{{ $option_label }}</option>
    @else
        <option value="{{ $option_value }}">{{ $option_label }}</option>
    @endif
@endforeach
