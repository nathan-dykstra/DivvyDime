@props(['messages'])

@if ($messages)
    <ul>
        @foreach ((array) $messages as $message)
            <li>
                <p class="text-warning text-sm">{{ $message }}</p>
            </li>
        @endforeach
    </ul>
@endif
