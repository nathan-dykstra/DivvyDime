<div class="expense-split-equal-container">
    <div class="split-equal-select-all-container">
        <div>{{ __('Total') }}</div>

        <div class="split-equal-price-breakdown-container">
            <div>{{__('$') }}<span id="split-amount-total">{{ $expense && $expense->expense_type_id === $expense_type_ids['amount'] ? number_format($expense->amount, 2) : '0.00' }}</span></div>
            <div class="text-shy">({{__('$') }}<span id="split-amount-left">{{ $expense && $expense->expense_type_id === $expense_type_ids['amount'] ? '0.00' : $expense?->amount ?? '0.00' }}</span> {{ __('left') }})</div>
        </div>
    </div>

    <div class="expense-dropdown-empty-warning hidden">
        {{ __('You must add users to the expense before you can divvy it up.') }}
    </div>

    <ul class="split-amount-list" id="split-amount-list">
        <!-- TODO: Show user profile photo in this list -->
        @if ($expense === null) <!-- Creating a new Expense -->
            @if ($group) <!-- Expense was added from a Group, so show the Group members by default -->
                @foreach ($group->group_members as $member)
                    <li>
                        <label class="split-amount-item" for="split-amount-item-{{ $member->id }}" data-user-id="{{ $member->id }}">
                            <div class="dropdown-user-item-img-name">
                                <div class="profile-img-sm-container">
                                    <img src="{{ $member->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img-sm">
                                </div>
                                <div class="dropdown-user-item-name">{{ $member->username }}</div>
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
                    <label class="split-amount-item" for="split-amount-item-{{ $current_user->id }}" data-user-id="{{ $current_user->id }}">
                        <div class="dropdown-user-item-img-name">
                            <div class="profile-img-sm-container">
                                <img src="{{ $current_user->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img-sm">
                            </div>
                            <div class="dropdown-user-item-name">{{ $current_user->username }}</div>
                        </div>

                        <x-text-input-prepend 
                            id="split-amount-item-{{ $current_user->id }}"
                            name="split-amount-item-{{ $current_user->id }}"
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
                        <label class="split-amount-item" for="split-amount-item-{{ $friend->id }}" data-user-id="{{ $friend->id }}">
                            <div class="dropdown-user-item-img-name">
                                <div class="profile-img-sm-container">
                                    <img src="{{ $friend->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img-sm">
                                </div>
                                <div class="dropdown-user-item-name">{{ $friend->username }}</div>
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
                    <label class="split-amount-item" for="split-amount-item-{{ $involved_user->id }}" data-user-id="{{ $involved_user->id }}">
                        <div class="dropdown-user-item-img-name">
                            <div class="profile-img-sm-container">
                                <img src="{{ $involved_user->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img-sm">
                            </div>
                            <div class="dropdown-user-item-name">{{ $involved_user->username }}</div>
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
        <label class="split-amount-item" for="" data-user-id="">
            <div class="dropdown-user-item-img-name">
                <div class="profile-img-sm-container">
                    <img src="" alt="User profile image" class="profile-img-sm">
                </div>
                <div class="dropdown-user-item-name"></div>
            </div>

            <x-text-input-prepend id="" name="" type="number" step="0.01" min="0" max="99999999" placeholder="{{ __('0.00') }}" :prepend="__('$')" oninput="splitAmountUpdateTotal()" />
        </label>
    </li>
</template>
