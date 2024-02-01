<div class="expense-split-equal-container">
    <div class="split-equal-select-all-container">
        <div>{{ __('Total') }}</div>

        <div class="split-equal-price-breakdown-container">
            <div>{{__('$') }}<span id="split-amount-total">{{ $expense && $expense->expense_type_id === $expense_type_ids['amount'] ? number_format($expense->amount, 2) : '0.00' }}</span></div>
            <div class="text-shy">({{__('$') }}<span id="split-amount-left">{{ $expense && $expense->expense_type_id === $expense_type_ids['amount'] ? '0.00' : $expense?->amount ?? '0.00' }}</span> {{ __('left') }})</div>
        </div>
    </div>

    <div class="paid-dropdown-empty-warning hidden">
        {{ __('You must add users to the expense before you can divvy it up.') }}
    </div>

    <ul class="split-amount-list" id="split-amount-list">
        <!-- TODO: Show user profile photo in this list -->
        @if ($expense === null) <!-- Creating a new Expense -->
            @if ($group) <!-- Expense was added from a Group, so show the Group members by default -->
                @foreach ($group->group_members as $member)
                    <li>
                        <label class="split-amount-item" for="split-amount-item-{{ $member->id }}">
                            <div class="user-photo-name">
                                <div class="profile-circle-sm-placeholder"></div>
                                <div class="split-equal-item-name">{{ $member->username }}</div>
                            </div>

                            <x-text-input-prepend 
                                id="split-amount-item-{{ $member->id }}"
                                name="split-amount-item-{{ $member->id }}"
                                type="number"
                                step="0.01"
                                min="0"
                                max="99999999"
                                placeholder="{{ __('0.00') }}"
                                :prepend="__('$')"
                                oninput="splitAmountUpdateTotal()"
                            />
                        </label>
                    </li>
                @endforeach
            @else <!-- Expense was not added from a Group (or it was added from "Individual Expenses") -->
                <li>
                    <label class="split-amount-item" for="split-amount-item-{{ auth()->user()->id }}">
                        <div class="user-photo-name">
                            <div class="profile-circle-sm-placeholder"></div>
                            <div class="split-equal-item-name">{{ auth()->user()->username }}</div>
                        </div>

                        <x-text-input-prepend 
                            id="split-amount-item-{{ auth()->user()->id }}"
                            name="split-amount-item-{{ auth()->user()->id }}"
                            type="number"
                            step="0.01"
                            min="0"
                            max="99999999"
                            placeholder="{{ __('0.00') }}"
                            :prepend="__('$')"
                            oninput="splitAmountUpdateTotal()"
                        />
                    </label>
                </li>

                @if ($friend) <!-- Expense was added from a Friend -->
                    <li>
                        <label class="split-amount-item" for="split-amount-item-{{ $friend->id }}">
                            <div class="user-photo-name">
                                <div class="profile-circle-sm-placeholder"></div>
                                <div class="split-equal-item-name">{{ $friend->username }}</div>
                            </div>

                            <x-text-input-prepend 
                                id="split-amount-item-{{ $friend->id }}"
                                name="split-amount-item-{{ $friend->id }}"
                                type="number"
                                step="0.01"
                                min="0"
                                max="99999999"
                                placeholder="{{ __('0.00') }}"
                                :prepend="__('$')"
                                oninput="splitAmountUpdateTotal()"
                            />
                        </label>
                    </li>
                @endif
            @endif
        @else <!-- Updating an existing Expense -->
            @foreach ($expense->involvedUsers() as $involved_user)
                <li>
                    <label class="split-amount-item" for="split-amount-item-{{ $involved_user->id }}">
                        <div class="user-photo-name">
                            <div class="profile-circle-sm-placeholder"></div>
                            <div class="split-equal-item-name">{{ $involved_user->username }}</div>
                        </div>

                        <x-text-input-prepend 
                            id="split-amount-item-{{ $involved_user->id }}"
                            name="split-amount-item-{{ $involved_user->id }}"
                            type="number"
                            step="0.01"
                            min="0"
                            max="99999999"
                            placeholder="{{ __('0.00') }}"
                            value="{{ old('split-amount-item-' . $involved_user->id, $expense->expense_type_id === $expense_type_ids['amount'] ? $involved_user->participant_amount : '') }}"
                            :prepend="__('$')"
                            oninput="splitAmountUpdateTotal()" 
                        />
                    </label>
                </li>
            @endforeach
        @endif
    </ul>
</div>

<template id="split-amount-dropdown-item-template">
    <li>
        <label class="split-amount-item" for="">
            <div class="user-photo-name">
                <div class="profile-circle-sm-placeholder"></div>
                <div class="split-equal-item-name"></div>
            </div>

            <x-text-input-prepend id="" name="" type="number" step="0.01" min="0" max="99999999" placeholder="{{ __('0.00') }}" :prepend="__('$')" oninput="splitAmountUpdateTotal()" />
        </label>
    </li>
</template>

<style>
    .split-amount-item {
        display: grid;
        grid-template-columns: auto 35%;
        gap: 16px;
        padding: 8px 16px;
        border-radius: var(--border-radius);
    }
</style>

<script>
    function splitAmountUpdateTotal() {
        const splitAmountItems = document.querySelectorAll(".split-amount-list li");

        let newTotal = 0.00;
        let amountLeft = currentAmountInput.value === '' ? 0.00 : parseFloat(currentAmountInput.value);

        splitAmountItems.forEach(function(item) {
            const itemAmount = item.querySelector('.text-input-prepend').value;

            if (itemAmount !== '') {
                newTotal += parseFloat(itemAmount);
                amountLeft -= parseFloat(itemAmount);
            }
        });

        $('#split-amount-total').text(newTotal.toFixed(2));
        $('#split-amount-left').text(amountLeft.toFixed(2));
    }
</script>
