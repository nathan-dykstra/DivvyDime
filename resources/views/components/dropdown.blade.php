@props(['align' => 'right', 'contentClasses' => 'py-1 bg-white dark:bg-gray-700'])

@php
switch ($align) {
    case 'left':
        $alignmentClasses = 'ltr:origin-top-left rtl:origin-top-right start-0';
        break;
    case 'top':
        $alignmentClasses = 'origin-top';
        break;
    case 'right':
    default:
        $alignmentClasses = 'ltr:origin-top-right rtl:origin-top-left end-0';
        break;
}
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute z-50 mt-2 rounded-md shadow-lg {{ $alignmentClasses }}"
            style="display: none;"
            @click="open = false">
        <div class="dropdown-container {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>

<style>
    .dropdown-container {
        display: flex;
        flex-direction: column;
        background-color: var(--secondary-grey);
        border-radius: var(--border-radius);
        color: var(--text-primary);
        width: 200px;
        padding: 8px;
        box-shadow: var(--box-shadow);
    }

    .dropdown-item {
        display: grid;
        gap: 8px;
        grid-template-columns: 22px auto;

        padding: 8px 16px;
        border-radius: 0.3rem;
        transition: background-color 0.1s ease;

        font-size: 0.8em;
        font-weight: 700;
        color: var(--text-heading);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .dropdown-item > * {
        display: flex;
        flex-direction: row;
        align-items: center;
    }

    .dropdown-item > .fa-solid {
        justify-content: center;
    }

    .dropdown-item:hover {
        background-color: var(--accent-color);
        cursor: pointer;
    }
</style>
