@props(['messages'])

@if ($messages)
    <ul>
        @foreach ((array) $messages as $message)
            <li>
                <p class="text-warning text-small">{{ $message }}</p>
            </li>
        @endforeach
    </ul>
@endif
