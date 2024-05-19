@props(['align' => 'right'])

@php
    switch ($align) {
        case 'left':
            $alignment = 'align-left';
            break;
        case 'right':
            $alignment = 'align-right';
            break;
    }
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <!-- Note: the dropdown animations are still using Tailwind classes -->
    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="dropdown-container {{ $alignment }}"
        style="display: none;"
        @click="open = false"
    >
        <div class="dropdown-menu">
            {{ $content }}
        </div>
    </div>
</div>

<style>
    .align-right {
        direction: ltr;
        transform-origin: top left;
        right: 0; 
    }

    .align-left {
        direction: ltr;
        transform-origin: top right;
        left: 0
    }

    .dropdown-container {
        position: absolute;
        z-index: 50;
        margin-top: 0.5rem;
    }

    .dropdown-menu {
        display: flex;
        flex-direction: column;
        background-color: var(--secondary-grey);
        border-radius: var(--border-radius-lg);
        color: var(--text-primary);
        padding: 8px;
        box-shadow: var(--box-shadow);
    }

    .dropdown-item {
        display: grid;
        gap: 8px;
        grid-template-columns: 20px auto;

        padding: 8px 16px;
        border-radius: var(--border-radius);
        transition: background-color 0.1s ease, color 0.1s ease;

        font-size: 0.8em;
        font-weight: 700;
        color: var(--text-heading);
        text-transform: uppercase;
        letter-spacing: 1px;

        max-width: 250px;
        text-wrap: nowrap;
        overflow: hidden;
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
        background-color: var(--secondary-grey-hover);
        cursor: pointer;
        color: var(--text-primary-highlight);
    }
</style>
