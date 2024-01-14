<div class="expense-split-equal-container">
    <div class="split-equal-select-all-container">
        <x-checkbox id="split-equal-select-all" onclick="splitEqualSelectAll(this)">{{ __('Select/deselect all') }}</x-checkbox> 

        <div class="split-equal-price-breakdown-container">
            <div>{{__('$') }}<span class="split-equal-price-breakdown">{{$expense && $expense->participants->count() !== 0 ? number_format(round($expense->amount / $expense->participants->count(), 2), 2) : '0.00' }}</span>{{ __('/person')}}</div>
            <div class="text-shy">(<span class="split-equal-participant-count">{{ $expense?->participants->count() ?? '1' }}</span> <span class="split-equal-participant-count-label">{{ $expense?->participants->count() === 1 || $expense === null ? __('person') : __('people') }}</span>)</div>
        </div>
    </div>

    <div class="paid-dropdown-empty-warning hidden">
        {{ __('You must add users to the expense before you can divvy it up.') }}
    </div>

    <ul class="split-equal-list" id="split-equal-list">
        <!-- TODO: Show user profile photo in this list -->
        @if ($expense === null)
            <li>
                <label class="split-equal-item" for="split-equal-item-{{ auth()->user()->id }}" onclick="splitEqualUpdateSelectAll()">
                    <input type="checkbox" id="split-equal-item-{{ auth()->user()->id }}" class="checkbox split-equal-item-checkbox" name="split-equal-user[]" value="{{ auth()->user()->id }}" checked />
                    <div class="split-equal-item-name">{{ auth()->user()->username }}</div>
                </label>
            </li>
        @endif

        @foreach ($expense?->involvedUsers() ?? [] as $involved_user)
            <li>
                <label class="split-equal-item" for="split-equal-item-{{ $involved_user->id }}" onclick="splitEqualUpdateSelectAll()">
                    <input type="checkbox" id="split-equal-item-{{ $involved_user->id }}" class="checkbox split-equal-item-checkbox" name="split-equal-user[]" value="{{ $involved_user->id }}" {{ $expense->participants->contains('id', $involved_user->id) ? 'checked' : '' }}/>
                    <div class="split-equal-item-name">{{ $involved_user->username }}</div>
                </label>
            </li>
        @endforeach
    </ul>
</div>

<template id="split-equal-dropdown-item-template">
    <li>
        <label class="split-equal-item" for="" onclick="splitEqualUpdateSelectAll()">
            <input type="checkbox" id="" class="checkbox split-equal-item-checkbox" name="split-equal-user[]" value="" checked/>
            <div class="split-equal-item-name"></div>
        </label>
    </li>
</template>

<style>
    .expense-split-equal-container {
        padding: 16px 0;
        color: var(--text-primary);
    }

    .split-equal-select-all-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        padding: 0 16px 8px 16px;
    }

    .split-equal-price-breakdown-container {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .split-equal-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .split-equal-item {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        gap: 16px;
        padding: 8px 16px;
        border-radius: var(--border-radius);
        transition: background-color 0.1s ease, color 0.1s ease;
    }

    .split-equal-item:hover {
        cursor: pointer;
        background-color: var(--secondary-grey-hover);
        color: var(--text-primary-highlight);
    }

    .split-equal-item-name {
        transition: color 0.1s ease, text-decoration 0.3s ease;
    }

    .split-equal-item-name:hover {
        cursor: pointer;
    }

    .split-equal-item-strikethrough:hover {
        color: var(--text-shy) !important;
    }
</style>

<script>
    function splitEqualSelectAll(box) {
        $('.split-equal-item-checkbox').prop('checked', box.checked);

        splitEqualUpdatePriceBreakdown();
    }

    function splitEqualUpdateSelectAll() {
        $('#split-equal-select-all').prop('checked', $('.split-equal-item-checkbox:checked').length === $('.split-equal-item-checkbox').length);

        splitEqualUpdatePriceBreakdown();
    }

    function splitEqualUpdatePriceBreakdown() {
        const currentParticipantCount = parseInt($('.split-equal-item-checkbox:checked').length);
        const amountPerParticipant = currentParticipantCount === 0 || currentAmountInput.value === '' ? 0 : parseFloat(currentAmountInput.value) / currentParticipantCount;

        $('.split-equal-price-breakdown').text(amountPerParticipant.toFixed(2));
        $('.split-equal-participant-count').text(currentParticipantCount);
        $('.split-equal-participant-count-label').text(currentParticipantCount === 1 ? "{{ __('person') }}" : "{{ __('people') }}");
    }
</script>
