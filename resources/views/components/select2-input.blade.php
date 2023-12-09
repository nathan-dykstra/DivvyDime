@props(['class' => '', 'id' => null, 'name' => null, 'disabled' => false, 'multiple' => null])

<select class="{{ $multiple ? 'select-input-multi' : 'select-input' . $class }}" {{ $attributes->merge([
    'id' => $id,
    'name' => $name,
    'multiple' => $multiple
]) }}>
    {{ $slot }}
</select>

<script type="module">
    $(document).ready(function() {
        $(".select-input").select2();

        $(".select-input-multi").select2({
            closeOnSelect: false
        });
    });
</script>

<style>
    /* Select2 Default*/

    .select2.select2-container {
        width: 100% !important;
        margin-top: 4px;
    }

    .select2.select2-container .select2-selection {
        background-color: var(--background);
        border: 1px solid var(--border-grey);
        border-radius: var(--border-radius);
        padding: 8px 12px;
        transition: border 0.3s;
    }

    .select2-container .select2-selection--single {
        height: 100%;
    }

    .select2.select2-container .select2-selection:focus {
        outline: 2px solid var(--blue-hover); /* TODO: Change this to --primary-color-hover */
        outline-offset: 0px;
        border-radius: var(--border-radius);
        border: 1px solid var(--background);
        box-shadow: none;
    }

    .select2.select2-container .select2-selection .select2-selection__rendered {
        color: var(--text-primary);
        line-height: 1.5;
        padding-right: 33px;
        padding-left: 0;
    }

    .select2-container--default.select2-container--open.select2-container--above .select2-selection--single,
    .select2-container--default.select2-container--open.select2-container--below .select2-selection--single {
        border-radius: var(--border-radius);
    }

    .select2.select2-container .select2-selection .select2-selection__arrow {
        height: 40px;
        width: 33px;
    }

    .select2.select2-container.select2-container--open .select2-selection.select2-selection--single {
        background: var(--background);
    }

    .select2-dropdown {
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
    }

    .select2-container--open .select2-dropdown--below {
        margin-top: 5px !important;
    }

    .select2-search--dropdown {
        padding: 6px !important;
        background-color: var(--background);
        border: 1px solid var(--border-grey) !important;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .select2-container .select2-dropdown .select2-search input {
        width: 100%;
        padding: 6px 12px;
        line-height: 1.2;
        color: var(--text-primary);
        background: var(--background);
        border: 1px solid var(--border-grey);
        border-radius: 0.3rem;
    }

    .select2-container .select2-dropdown .select2-search .select2-search__field:focus {
        outline: 2px solid var(--blue-hover) !important;
        outline-offset: 0;
        border: 1px solid var(--background) !important;
        box-shadow: none !important;
    }

    .select2-container--open .select2-dropdown--above,
    .select2-container--open .select2-dropdown--below {
        border-radius: var(--border-radius) !important;
    }

    .select2-container .select2-dropdown .select2-results ul {
        color: var(--text-primary);
        background-color: var(--background) !important;
        border: 1px solid var(--border-grey) !important;
        border-radius: 0 0 var(--border-radius) var(--border-radius);
    }

    .select2-container--default .select2-results__option--selected {
        background-color: var(--secondary-grey);
    }

    .select2-results__option {
        padding: 6px 12px;
    }

    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        color: var(--text-primary-highlight) !important;
        background-color: var(--accent-color) !important;
    }

    .select2-container .select2-dropdown {
      background: transparent;
      border: none;
      margin-top: -5px;
    }

    .select2-container .select2-dropdown .select2-search,
    .select2-container .select2-dropdown .select2-results {
        padding: 0;
    }

    /* Select2 Multiple */

    .select2-container--default .select2-selection--multiple {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
    }

    .select2-container--default .select2-selection--multiple:focus {
        outline: none !important;
        border: 1px solid var(--background) !important;
        box-shadow: none !important;
    }

    .select2-container--default.select2-container--open.select2-container--above .select2-selection--multiple,
    .select2-container--default.select2-container--open.select2-container--below .select2-selection--multiple {
        border-radius: var(--border-radius);
    }

    .select2.select2-container .select2-selection--multiple .select2-search--inline .select2-search__field {
        height: 24px;
        max-width: 200px;
        color: var(--text-primary) !important;
        background-color: var(--background) !important;
        margin: 0;
    }

    .select2.select2-container .select2-selection--multiple .select2-selection__rendered {
        display: inline-flex;
        flex-wrap: wrap;
        row-gap: 6px;
        padding: 0;
    }

    .select2.select2-container .select2-selection--multiple .select2-selection__choice {
        display: flex;
        align-items: center;
        margin: 0 6px 0 0;
        color: var(--text-primary);
        font-size: 0.9em;
        background-color: var(--secondary-grey);
        border: 1px solid var(--border-grey);
        border-radius: 0.25rem;
        height: 24px;
        padding: 3px 8px 3px 22px;
        line-height: 1.4;
        position: relative;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
        padding: 0;
    }


    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        background-color: var(--secondary-grey);
    }

    .select2.select2-container .select2-selection--multiple .select2-selection__choice .select2-selection__choice__remove {
        top: 0;
        left: 0;
        height: 100%;
        width: 22px;
        margin: 0;
        text-align: center;
        color: var(--text-warning);
        border: none;
    }

    /* Select2 Scrollbar */

    .select2-results__options::-webkit-scrollbar {
        width: var(--scrollbar-width);
    }

    .select2-results__options::-webkit-scrollbar-track {
        background-color: var(--background);
        border-radius: 0 0.5rem 0.5rem 0;
        border: solid var(--scrollbar-border) var(--background);
    }

    .select2-results__options::-webkit-scrollbar-thumb {
        background-color: var(--scrollbar-color);
        border-radius: var(--scrollbar-border-radius);
        border: solid var(--scrollbar-border) var(--background);
    }

    .select2-results__options::-webkit-scrollbar-thumb:hover {
        background-color: var(--scrollbar-color-hover);
    }

    /* Conditional */

    @if ($multiple)
        .select2-container .select2-dropdown .select2-results ul {
            border-radius: var(--border-radius) !important;
        }
    @endif
</style>
