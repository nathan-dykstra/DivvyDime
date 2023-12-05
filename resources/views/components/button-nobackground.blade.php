@props(['type' => 'button', 'class' => '', 'id' => '', 'icon' => ''])

<button {{ $attributes->merge(['type' => $type, 'class' => 'button-nobackground ' . $class, 'id' => $id]) }}>
    <i class="{{ $icon }}"></i>
    {{ $slot }}
</button>

@section('css')
    <style>
        .button-nobackground {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border-radius: 0.5rem;
            padding: 3px 5px;
            transition: 0.3s ease-in-out;
        }

        .button-nobackground:hover {
            background-color: grey;
            cursor: pointer;
        }
    </style>
@stop