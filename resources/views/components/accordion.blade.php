@props(['collapseOthers' => false, 'group' => ""])

<div class="accordion-toggle" data-group="{{ $group }}" onclick="{{ $collapseOthers ? 'toggleAccordion(this, true)' : 'toggleAccordion(this)' }}">
    {{ $toggle }}
</div>

<div class="accordion-content hidden">
    {{ $content }}
</div>

<style>
    .accordion-content {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transition: max-height 0.3s, opacity 0.3s, padding 0.3s, margin 0.3s;
    }
</style>
