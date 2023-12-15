@props(['type' => null, 'class' => '', 'id' => null, 'onclick' => null, 'href' => $href, 'form' => ''])

@if ($href)
    <a {{ $attributes->merge(['class' => 'link-btn no-focus' . $class, 'id' => $id, 'onclick' => $onclick, 'href' => $href]) }}>
        {{ $slot }}
    </a>
@elseif ($form) 
    <button {{ $attributes->merge(['class' => 'link-btn no-focus' . $class, 'id' => $id, 'form' => $form]) }}>
        {{ $slot }}
    </button>
@elseif ($type)
    <button {{ $attributes->merge(['type' => $type, 'class' => 'link-btn no-focus' . $class, 'id' => $id]) }}>
        {{ $slot }}
    </button>
@endif

<style>
    .link-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;

        transition: 0.3s ease-in-out;
    
        color: var(--text-shy);
        font-size: 0.9em;
        font-weight: 500;
        text-decoration: underline;

        outline: none;
    }

    .link-btn:hover {
        color: var(--text-primary);
        cursor: pointer;
    }

    .link-btn:focus {
        color: var(--text-primary);
    }
</style>
