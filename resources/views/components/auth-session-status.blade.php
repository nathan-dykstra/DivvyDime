@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'text-small text-success']) }}>
        {{ $status }}
    </div>
@endif
