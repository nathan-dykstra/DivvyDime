<div class="container margin-bottom-lg">
    <div class="restrict-max-width">
        <form method="post" action="{{ $expense ? route('expenses.update', $expense) : route('expenses.store') }}" class="space-bottom-lg">
            @csrf
            @if ($expense)
                @method('patch')
            @endif

            <div class="expense-involved-container">
                <div class="involved-chips-container" id="involved-chips-container">
                    @if ($expense === null)
                        <div class="involved-chip involved-chip-fixed" data-user-id="{{ auth()->user()->id }}" data-username="{{ auth()->user()->username }}">
                            <span>{{ auth()->user()->username }}</span>
                            <!--<x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />-->
                            <!-- TODO: Allow removeal of current user (on initial load) when adding in a Group -->
                        </div>
                    @endif

                    @foreach($expense?->involvedUsers() ?? [] as $involved_user)
                        <div class="involved-chip {{ $involved_user->id === auth()->user()->id && $expense->group_id === $default_group->id ? 'involved-chip-fixed' : '' }}" data-user-id="{{ $involved_user->id }}" data-username="{{ $involved_user->username }}">
                            <span>{{ $involved_user->username }}</span>
                            @if (!($involved_user->id === auth()->user()->id && $expense->group_id === $default_group->id))
                                <x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />
                            @endif
                        </div>
                    @endforeach

                    <input id="expense-involved" class="expense-involved" type="search" placeholder="{{ __('Who was involved?') }}" autofocus autocomplete="off" />
                </div>

                <div class="expense-involved-dropdown hidden" id="expense-involved-dropdown"></div>
            </div>

            <div class="expense-name-amount-category-container">
                <x-tooltip side="bottom" icon="fa-solid fa-tag" :tooltip="__('Choose a category')">
                    <div class="expense-category">

                    </div>
                </x-tooltip>
                <div class="expense-name-amount-container">
                    <div class="expense-input-container">
                        <input id="expense-name" class="expense-name" name="expense-name" type="text" placeholder="{{ __('Describe the expense') }}" value="{{ old('expense-name', $expense ? $expense->name : '') }}" autocomplete="off" maxlength="255" required />
                    </div>

                    <div class="expense-input-container">
                        <span class="expense-currency">{{ __('$') }}</span><input id="expense-amount" class="expense-amount" name="expense-amount" type="number" step="0.01" min="0" max="99999999" placeholder="{{ __('0.00') }}" value="{{ old('expense-amount', $expense ? $expense->amount : '') }}" autocomplete="off" oninput="updateSplitDropdownAmounts()" required />
                    </div>
                </div>
            </div>

            <div class="expense-paid-split-container">
                <div>
                    <div class="expense-paid-split">
                        {{ __('Who paid?') }}

                        <x-primary-button class="expense-round-btn" id="expense-paid-btn" onclick="togglePaidDropdown()">
                            <div class="expense-round-btn-text">
                                {{ $expense?->payer_username ?? auth()->user()->username }}
                            </div>
                        </x-primary-button>
                    </div>

                    <div class="expense-expand-dropdown" id="expense-paid-dropdown">
                        <h4 class="margin-bottom-sm">{{ __('Who paid for this expense?') }}</h4>

                        <div class="paid-dropdown-empty-warning hidden">
                            {{ __('You must add users to the expense before choosing who paid.') }}
                        </div>

                        <ul class="expense-paid-dropdown-list" id="expense-paid-dropdown-list">
                            @if ($expense === null)
                                <li>
                                    <label class="split-equal-item" for="paid-dropdown-item-{{ auth()->user()->id }}" data-user-id="{{ auth()->user()->id }}" data-username="{{ auth()->user()->username }}" onclick="setExpensePayer(this)">
                                        <input type="radio" id="paid-dropdown-item-{{ auth()->user()->id }}" class="radio" name="expense-paid" value="{{ auth()->user()->id }}" checked/>
                                        <div class="split-equal-item-name">{{ auth()->user()->username }}</div>
                                    </label>
                                </li>
                            @endif

                            @foreach ($expense?->involvedUsers() ?? [] as $involved_user)
                                <li>
                                    <label class="split-equal-item" for="paid-dropdown-item-{{ $involved_user->id }}" data-user-id="{{ $involved_user->id }}" data-username="{{ $involved_user->username }}" onclick="setExpensePayer(this)">
                                        <input type="radio" id="paid-dropdown-item-{{ $involved_user->id }}" class="radio" name="expense-paid" value="{{ $involved_user->id }}" {{ $expense?->payer === $involved_user->id ? 'checked' : '' }}/>
                                        <div class="split-equal-item-name">{{ $involved_user->username }}</div>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div>
                    <div class="expense-paid-split">
                        {{ __('How was it split?') }} <!-- TODO: this section -->

                        <x-primary-button class="expense-round-btn" id="expense-split-btn" onclick="toggleSplitDropdown()">
                            <div class="expense-round-btn-text">
                                {{ $expense ? $expense_type_names[$expense->expense_type_id] : $expense_type_names[$default_expense_type] }}
                            </div>
                        </x-primary-button>
                    </div>

                    <div class="expense-expand-dropdown" id="expense-split-dropdown">
                        <h4 class="margin-bottom-sm">{{ __('How should we divvy this up?') }}</h4>

                        <div class="expense-split-tabs-wrapper">
                            <button type="button" class="expense-split-tabs-scroll-btn expense-split-tabs-left-btn" onclick="splitTabsScrollLeft()"><i class="fa-solid fa-arrow-left"></i></button>

                            @include('expenses.partials.split-tabs.expense-tab-headers')

                            <button type="button" class="expense-split-tabs-scroll-btn expense-split-tabs-right-btn" onclick="splitTabsScrollRight()"><i class="fa-solid fa-arrow-right"></i></button>
                        </div>

                        <div id="expense-split-tabs-content">
                            <div id="expense-split-equal" class="{{ $expense === null || $expense?->expense_type_id === $expense_type_ids['equal'] ? '' : 'hidden' }}">
                                @include('expenses.partials.split-tabs.expense-equal-tab')
                            </div>
                            <div id="expense-split-amount" class="{{ $expense?->expense_type_id === $expense_type_ids['amount'] ? '' : 'hidden' }}">
                                @include('expenses.partials.split-tabs.expense-amount-tab')
                            </div>
                            <div id="expense-split-percentage" class="{{ $expense?->expense_type_id === $expense_type_ids['percentage'] ? '' : 'hidden' }}">Coming soon</div>
                            <div id="expense-split-share" class="{{ $expense?->expense_type_id === $expense_type_ids['share'] ? '' : 'hidden' }}">Coming soon</div>
                            <div id="expense-split-adjustment" class="{{ $expense?->expense_type_id === $expense_type_ids['adjustment'] ? '' : 'hidden' }}">Coming soon</div>
                            <div id="expense-split-reimbursement" class="{{ $expense?->expense_type_id === $expense_type_ids['reimbursement'] ? '' : 'hidden' }}">Coming soon</div>
                            <div id="expense-split-itemized" class="{{ $expense?->expense_type_id === $expense_type_ids['itemized'] ? '' : 'hidden' }}">Coming soon</div>
                        </div>
                    </div>

                    <input type="hidden" id="expense-split" name="expense-split" value="{{ $expense ? $expense->expense_type_id : $default_expense_type }}" />
                </div>
            </div>

            <div class="expense-group-date-media-container">
                <div>
                    <div class="expense-group-date-media">
                        <x-primary-button class="expense-round-btn expense-round-btn-equal-width" id="expense-group-btn" onclick="toggleGroupDropdown()">
                            <div class="expense-round-btn-text">
                                {{ $expense?->group()->first()->name ?? $default_group->name }}
                            </div>
                        </x-primary-button>
                    </div>
    
                    <div class="expense-expand-dropdown" id="expense-group-dropdown">
                        <h4 class="margin-bottom-sm">{{ __('Choose a group') }}</h4>
    
                        <ul class="expense-paid-dropdown-list" id="expense-group-dropdown-list">
                            @foreach ($groups as $group)
                            <!-- TODO: Select the correct Group if Expense is added from a Group -->
                                <li>
                                    <label class="split-equal-item" for="group-dropdown-item-{{ $group->id }}" data-group-id="{{ $group->id }}" data-group-name="{{ $group->name }}" onclick="setExpenseGroup(this)">
                                        <input type="radio" id="group-dropdown-item-{{ $group->id }}" class="radio" name="expense-group" value="{{ $group->id }}" {{ $expense === null && $group->id === $default_group->id || $expense?->group_id === $group->id ? 'checked' : '' }}/>
                                        <div class="split-equal-item-name">{{ $group->name }}</div>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div>
                    <div class="expense-group-date-media">
                        <x-primary-button class="expense-round-btn expense-round-btn-equal-width" id="expense-date-btn" onclick="toggleDateDropdown()">
                            <div class="expense-round-btn-text">
                                {{ $expense?->formatted_date ?? $formatted_today }}
                            </div>
                        </x-primary-button>
                    </div>

                    <div class="expense-expand-dropdown" id="expense-date-dropdown">
                        <h4 class="margin-bottom-sm">{{ __('When did the expense occur?') }}</h4>

                        <div class="expense-datepicker-container">
                            <!-- Flowbite Tailwind CSS Datepicker -->
                            <div id="flowbite-datepicker" inline-datepicker datepicker-buttons datepicker-format="yyyy-mm-dd" data-date="{{ $expense ? $expense->date : $today }}"></div> <!--inline-datepicker datepicker-buttons datepicker-format="yyyy-mm-dd" data-date="{{ $today }}"-->
                        </div>
                    </div>

                    <input type="hidden" id="expense-date" name="expense-date" value="{{ $expense ? $expense->date : $today }}" />
                </div>

                <div>
                    <div class="expense-group-date-media">
                        <x-primary-button class="expense-round-btn expense-round-btn-equal-width" id="expense-media-btn" onclick="toggleMediaDropdown()">
                            <div class="expense-round-btn-text">
                                {{ __('Add Note/Media') }}
                            </div>
                        </x-primary-button>
                    </div>

                    <div class="expense-expand-dropdown" id="expense-media-dropdown">
                        <h4 class="margin-bottom-sm">{{ __('Add a note or image') }}</h4>

                        <x-input-label for="expense-note" :value="__('Note')" />
                        <x-text-area id="expense-note" name="expense-note" maxlength="65535" :value="old('expense-note', $expense?->note ?? '')" />
                    </div>
                </div>
            </div>

            <div class="btn-container-start">
                <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
            </div>
        </form>
    </div>

    <!-- HTML Templates -->

    <template id="involved-chip-template">
        <div class="involved-chip" data-user-id="" data-username="">
            <div class="involved-chip-text"></div>
            <x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />
        </div>
    </template>

    <template id="involved-chip-current-user-fixed-template">
        <div class="involved-chip involved-chip-fixed" data-user-id="{{ auth()->user()->id }}" data-username="{{ auth()->user()->username }}">
            <!-- TODO: Add user image to this template -->
            <div class="involved-chip-text">{{ auth()->user()->username }}</div>
        </div>
    </template>

    <template id="involved-chip-current-user-template">
        <div class="involved-chip" data-user-id="{{ auth()->user()->id }}" data-username="{{ auth()->user()->username }}">
            <!-- TODO: Add user image to this template -->
            <div class="involved-chip-text">{{ auth()->user()->username }}</div>
            <x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />
        </div>
    </template>

    <template id="dropdown-item-already-involved-template">
        <div class="involved-dropdown-item" onmouseover="highlightDropdownItem(this)">
            <div class="involved-dropdown-item-user">
                <div></div>
                <div class="text-shy">{{ __('Already involved') }}</div>
            </div>
            <i class="fa-solid fa-user-check friend-added-icon"></i>
        </div>
    </template>

    <template id="dropdown-item-not-involved-template">
        <div class="involved-dropdown-item" onmouseover="highlightDropdownItem(this)">
            <div class="involved-dropdown-item-user">
                <div></div>
                <div class="text-shy"></div>
            </div>
            <i class="fa-solid fa-user-plus add-friend-icon"></i>
        </div>
    </template>

    <template id="paid-dropdown-item-template">
        <!--<div class="paid-dropdown-item" data-user-id="" data-username="" onclick="setExpensePayer(this)">
            <div class="paid-dropdown-item-name"></div>

            <i class="fa-solid fa-check text-success hidden"></i>
        </div>-->

        <li>
            <label class="split-equal-item" for="" data-user-id="" data-username="" onclick="setExpensePayer(this)">
                <input type="radio" id="" class="radio" name="expense-paid" value="" />
                <div class="split-equal-item-name"></div>
            </label>
        </li>
    </template>
</div>

<style>
    .expense-involved-container {
        position: relative;
    }

    .involved-chips-container {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        background-color: var(--secondary-grey);
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border-grey);
    }

    .expense-involved {
        height: 2em;
        color: var(--text-primary);
        min-width: 200px;
        width: auto;
        border: none;
        padding: 0;
        margin: 0;
        background-color: var(--secondary-grey);
    }

    .expense-involved:focus {
        border: none !important;
        outline: none !important;
        outline-offset: 0 !important;
        box-shadow: none !important;
    }

    .expense-involved::placeholder {
        color: var(--text-shy);
    }

    .expense-involved-dropdown {
        position: absolute;
        right: 0;
        left: 0;
        z-index: 50;

        background-color: var(--secondary-grey);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 8px;
        margin-top: 0.5rem;
        display: flex;
        flex-direction: column;
    }

    .involved-chip {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 8px;
        border: 1px solid var(--border-grey);
        background-color: var(--primary-grey);
        transition: border 0.3s, background-color 0.3s ease-in-out;

        color: var(--text-primary);
        height: 2em;
        border-radius: 1em;
        padding: 0 10px;
    }

    .involved-chip-selected {
        background-color: var(--primary-grey-hover);
        border: 1px solid var(--border-grey-hover);
    }

    .involved-chip-text {
        max-width: 150px;
        overflow: hidden;
        text-wrap: nowrap;
        text-overflow: ellipsis;
    }

    .involved-dropdown-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: var(--text-primary);
        border-radius: 0.3rem;
        padding: 8px 16px;
        border-radius: 0.3rem;
        transition: background-color 0.1s ease, color 0.1s ease;
    }

    .involved-dropdown-item-selected {
        cursor: pointer;
        background-color: var(--secondary-grey-hover);
        color: var(--text-primary-highlight);
    }

    .expense-name-amount-category-container {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 2em;
    }

    .expense-category {
        height: 80px;
        width: 80px;
        background-color: var(--primary-grey);
        border: 1px solid var(--border-grey);
        border-radius: var(--border-radius);
        transition: border 0.3s, background-color 0.3s ease-in-out;
    }

    .expense-category:hover {
        background-color: var(--primary-grey-hover);
        border: 1px solid var(--border-grey-hover);
        cursor: pointer;
    }

    .expense-name, .expense-amount {
        color: var(--text-primary);
        border: none;
        width: 100%;
        padding: 4px 8px;
        margin: 0;
        background-color: var(--secondary-grey);
    }

    .expense-name:focus, .expense-amount:focus {
        border: none !important;
        outline: none !important;
        outline-offset: 0 !important;
        box-shadow: none !important;
    }

    .expense-name::placeholder, .expense-amount::placeholder {
        color: var(--text-shy);
    }

    .expense-name-amount-container {
        width: 100%;
    }

    .expense-input-container {
        color: var(--text-primary);
        display: flex;
        align-items: flex-end;
        border-bottom: 1px solid var(--border-grey);
        margin-bottom: 8px;
    }

    .expense-name {
        font-size: 1.25em;
        font-weight: 600;
    }

    .expense-currency {
        padding: 4px 0 4px 8px;
        font-size: 1.25em;
        font-weight: 600;
    }

    .expense-amount {
        font-size: 1.75em;
        font-weight: 800;

        /* Remove up/down arrows - Firefox */
        -moz-appearance: textfield;
    }

    /* Remove up/down arrows - Chrome, Safari, Edge */
    .expense-amount::-webkit-outer-spin-button, input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .expense-paid-split-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding-bottom: 2em;
        border-bottom: 1px solid var(--border-grey);
    }

    .expense-paid-split {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        color: var(--text-shy);
    }

    .expense-round-btn {
        font-size: 1em !important;
        font-weight: 400 !important;
        text-transform: none !important;
        letter-spacing: 0 !important;
        height: 2em !important;
        border-radius: 1em !important;
        padding: 0 10px !important;
    }

    .expense-round-btn-text {
        overflow: hidden;
        text-wrap: nowrap;
        text-overflow: ellipsis;
    }

    .expense-round-btn-equal-width {
        width: 250px !important;
    }

    .expense-expand-dropdown {
        overflow: hidden;
        width: 100%;
        max-height: 0;
        opacity: 0;
        transition: max-height 0.3s, padding 0.3s, margin 0.3s, opacity 0.3s;
    }

    .expense-expand-dropdown-open {
        max-height: 500px;
        border-top: 1px solid var(--border-grey);
        border-bottom: 1px solid var(--border-grey);
        margin: 16px 0 0 0;
        padding: 16px 0;
        opacity: 100%;
    }

    .expense-paid-dropdown-list {
        color: var(--text-primary);
    }

    .paid-dropdown-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: var(--border-radius);
        color: var(--text-primary);
        padding: 8px 16px;
        transition: 0.1s ease, color 0.1s ease;
    }

    .paid-dropdown-item:hover {
        cursor: pointer;
        background-color: var(--secondary-grey-hover);
        color: var(--text-primary-highlight);
    }

    .paid-dropdown-empty-warning {
        display: flex;
        justify-content: center;
        width: 100%;
        padding: 8px 16px;
        color: var(--text-warning);
        border: 1px solid var(--border-grey);
        border-radius: var(--border-radius);
    }

    .expense-group-date-media-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding-bottom: 2em;
        border-bottom: 1px solid var(--border-grey);
    }

    .expense-group-date-media {
        display: flex;
        justify-content: center;
    }

    .expense-datepicker-container {
        display: flex;
        justify-content: center;
    }

    .expense-split-tabs-wrapper {
        position: relative;
        overflow: hidden;
    }

    .expense-split-tabs-scroll-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-primary);
        height: 32px;
        width: 32px;
        border-radius: 16px;
        background-color: var(--secondary-grey-hover);
        border: none;
        cursor: pointer;
    }

    .expense-split-tabs-left-btn {
        left: 0;
    }

    .expense-split-tabs-right-btn {
        right: 0;
    }

    .expense-split-tabs {
        display: flex;
        align-items: center;
        overflow-x: hidden;
        white-space: nowrap;
        color: var(--text-shy);
        border-bottom: 1px solid var(--border-grey);
    }

    .expense-split-tab {
        display: inline-block;
        padding: 8px 16px;
        transition: color 0.3s ease-in-out;
    }

    .expense-split-tab:hover {
        cursor: pointer;
    }

    .expense-split-tab:not(.expense-split-tab-active):hover {
        color: var(--text-primary);
    }

    .expense-split-tab-active {
        color: var(--blue-hover);
        border-bottom: 3px solid var(--blue-hover);
    }

    /* Flowbite Datepicker style overrides */

    .expense-datepicker-container .datepicker-picker {
        background-color: var(--secondary-grey) !important;
        box-shadow: none !important;
    }

    .expense-datepicker-container .days-of-week > span {
        color: var(--text-shy) !important;
    }

    .expense-datepicker-container .datepicker-cell {
        color: var(--text-primary) !important;
        padding: 6px 0 !important;
    }

    .expense-datepicker-container .datepicker-cell:hover {
        background-color: var(--secondary-grey-hover) !important;
    }

    .expense-datepicker-container .datepicker-cell.selected {
        color: var(--text-primary-highlight) !important;
        background-color: var(--blue-hover) !important; /* TODO: Change this to --primary-colour-hover */
    }

    .expense-datepicker-container .datepicker-cell.prev, .expense-datepicker-container .datepicker-cell.next {
        color: var(--text-shy) !important;
    }

    .expense-datepicker-container .datepicker-cell.prev:hover, .expense-datepicker-container .datepicker-cell.next:hover {
        color: var(--text-primary) !important;
    }

    .expense-datepicker-container button.bg-white {
        display: inline-flex !important;
        justify-content: center !important;
        align-items: center !important;

        background-color: var(--secondary-grey) !important;
        color: var(--text-primary);
        height: 36px !important;
        border: 1px solid var(--icon-grey) !important;
        border-radius: var(--border-radius) !important;
        padding: 8px 16px !important;
        transition: border 0.3s, background-color 0.3s ease-in-out, outline 0.1s ease-in-out, outline-offset 0.1s !important;

        font-size: 0.8em !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 1px !important;

        outline: none !important;
    }

    .expense-datepicker-container button.bg-white:hover {
        background-color: var(--primary-grey-hover) !important;
        cursor: pointer !important;
    }

    .expense-datepicker-container button.bg-white:focus {
        outline: 3px solid var(--blue-hover) !important; /* TODO: Change this to --primary-color-hover */
        outline-offset: 1px !important;
        border: 1px solid var(--icon-grey) !important;
        border-radius: var(--border-radius) !important;
        box-shadow: none !important;
    }

    .expense-datepicker-container button svg {
        color: var(--text-primary) !important;
    }

    .expense-datepicker-container button.today-btn {
        display: inline-flex !important;
        justify-content: center !important;
        align-items: center !important;

        background-color: var(--blue-hover) !important; /* TODO: Change this to --primary-color */
        color: var(--text-opposite) !important;
        height: 36px !important;
        border: none !important;
        border-radius: var(--border-radius) !important;
        padding: 8px 16px !important;
        transition: border 0.3s, background-color 0.3s ease-in-out, olor 0.3s ease-in-out, outline 0.1s ease-in-out, outline-offset 0.1s !important;

        font-size: 0.8em !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 1px !important;

        outline: none !important;
    }

    .expense-datepicker-container button.today-btn:hover {
        background-color: var(--blue-hover) !important; /* TODO: Change this to --primary-color-hover */
        border: none !important;
        color: var(--text-opposite-highlight) !important;
        cursor: pointer !important;
    }

    .expense-datepicker-container button.today-btn:focus {
        outline: 3px solid var(--blue-hover) !important; /* TODO: Change this to --primary-color-hover */
        outline-offset: 1px !important;
        border: none !important;
        border-radius: var(--border-radius) !important;
        box-shadow: none !important;
    }
</style>

<script>
    const involvedFriendsInput = document.getElementById('expense-involved');
    const involvedChipsContainer = document.getElementById('involved-chips-container');
    const involvedDropdown = document.getElementById('expense-involved-dropdown');

    const paidDropdown = document.getElementById('expense-paid-dropdown');
    const splitDropdown = document.getElementById('expense-split-dropdown');
    const groupDropdown = document.getElementById('expense-group-dropdown');
    const dateDropdown = document.getElementById('expense-date-dropdown');
    const mediaDropdown = document.getElementById('expense-media-dropdown');

    const paidDropdownList = document.getElementById('expense-paid-dropdown-list');
    const splitTabs = document.getElementById('expense-split-tabs');
    const splitTabsContent = document.getElementById('expense-split-tabs-content');
    const groupDropdownList = document.getElementById('expense-group-dropdown-list');
    const datePicker = document.getElementById('flowbite-datepicker');

    const splitEqualList = document.getElementById('split-equal-list');

    const currentAmountInput = document.getElementById('expense-amount');
    const currentPayerInput = document.querySelector('input[name="expense-paid"]:checked');
    const currentSplitInput = document.getElementById('expense-split');
    const currentGroupInput = document.querySelector('input[name="expense-group"]:checked');
    const currentDateInput = document.getElementById('expense-date');

    const paidBtn = document.getElementById('expense-paid-btn');
    const splitBtn = document.getElementById('expense-split-btn');
    const groupBtn = document.getElementById('expense-group-btn');
    const dateBtn = document.getElementById('expense-date-btn');
    const mediaBtn = document.getElementById('expense-media-btn');

    const scrollStep = 200;
    const scrollDuration = 300;

    var selectedDropdownItemIndex = 0;

    involvedFriendsInput.addEventListener('input', function(event) {
        const searchString = event.target.value;
        var involvedChips = involvedChipsContainer.querySelectorAll('.involved-chip .involved-chip-text');

        // Remove highlight on last User chip (if it exists)
        if ($(involvedChipsContainer).children().length >= 2 && searchString !== '') {
            const lastChip = $(involvedChipsContainer).children().eq(-2);
            lastChip.removeClass('involved-chip-selected');
        }

        $.ajax({
            url: "{{ route('expenses.search-friends-to-include') }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'search_string': searchString,
            },
            success: function(users) {
                if (searchString === '') {
                    involvedDropdown.classList.add('hidden');
                } else {
                    displaySearchResults(users);
                }
            },
            error: function(error) {
                console.log(error);
            }
        });
    });

    involvedFriendsInput.addEventListener('blur', function() {
        // Remove highlight on last User chip (if it exists)
        if ($(involvedChipsContainer).children().length >= 2) {
            const lastChip = $(involvedChipsContainer).children().eq(-2);
            lastChip.removeClass('involved-chip-selected');
        }
    });

    function displaySearchResults(results) {
        $(involvedDropdown).empty();

        if (results.length > 0) {
            const usersAlreadyInvolved = Array.from(involvedChipsContainer.children).map(child => parseInt(child.dataset.userId));

            results.forEach(user => {
                if (usersAlreadyInvolved.includes(parseInt(user['id']))) { // This user has already been added as a chip
                    var dropdownItemAlreadyInvolvedContent = $('#dropdown-item-already-involved-template').html();
                    var dropdownItem = $(dropdownItemAlreadyInvolvedContent).clone();

                    const usernameChild = dropdownItem.children('.involved-dropdown-item-user').children('div:first-child');
                    $(usernameChild).text(user.username);

                    dropdownItem.on('click', function() {
                        involvedDropdown.classList.add('hidden');
                        involvedFriendsInput.value = '';
                        involvedFriendsInput.focus();
                    });
                } else { // This user has not been added as a chip
                    var dropdownItemNotInvolvedContent = $('#dropdown-item-not-involved-template').html();
                    var dropdownItem = $(dropdownItemNotInvolvedContent).clone();

                    const usernameChild = dropdownItem.children('.involved-dropdown-item-user').children('div:first-child');
                    const emailChild = dropdownItem.children('.involved-dropdown-item-user').children('div:nth-child(2)');
                    $(usernameChild).text(user.username);
                    $(emailChild).text(user.email);

                    dropdownItem.on('click', function() {
                        addUserChip(user);
                    });
                }

                $(involvedDropdown).append(dropdownItem);
            });

            // Highlight the first item in the dropdown
            selectedDropdownItemIndex = 0;
            $(involvedDropdown).children().eq(0).addClass('involved-dropdown-item-selected');

            involvedDropdown.classList.remove('hidden');
        } else {
            involvedDropdown.classList.add('hidden');
        }
    }

    function addUserChip(user) {
        var userChipContent = $('#involved-chip-template').html();
        var userChip = $(userChipContent).clone();

        // TODO: add user image to the user chip
        userChip.children('.involved-chip-text').text(user.username);
        userChip.attr('data-user-id', user.id);
        userChip.attr('data-username', user.username);

        const searchInput = $(involvedChipsContainer).children('.expense-involved');
        searchInput.before(userChip);

        involvedDropdown.classList.add('hidden');
        involvedFriendsInput.value = '';
        involvedFriendsInput.focus();

        updatePaidDropdownList();
    }

    involvedChipsContainer.addEventListener('click', function() {
        involvedFriendsInput.focus();
    });

    function removeUserChip(removeBtn) {
        userChip = removeBtn.closest('.involved-chip');
        $(userChip).remove();

        involvedFriendsInput.value = '';
        involvedFriendsInput.focus();

        updatePaidDropdownList();
    }

    document.addEventListener('click', function(event) {
        const clickedElement = event.target;

        if (!involvedDropdown.contains(clickedElement)) {
            // Hide dropdown and reset the highlighted dropdown item
            involvedDropdown.classList.add('hidden');
            selectedDropdownItemIndex = 0;
        }
    });

    function highlightDropdownItem(item) {
        if ($(item).hasClass('involved-dropdown-item-selected')) {
            return;
        } else {
            $(involvedDropdown).find('.involved-dropdown-item-selected').removeClass('involved-dropdown-item-selected');
            $(item).addClass('involved-dropdown-item-selected');

            const itemIndex = $(involvedDropdown).children().index($(item));
            selectedDropdownItemIndex = itemIndex;
        }
    }

    involvedFriendsInput.addEventListener('keydown', function(event) {
        const dropdownCount = $(involvedDropdown).children().length;

        if (event.keyCode === 8 && event.target.value === '' && $(involvedChipsContainer).children().length >= 2) { // Backspace
            // Highlight/delete the last User chip
            const lastChip = $(involvedChipsContainer).children().eq(-2);
            if (lastChip.hasClass('involved-chip-selected')) {
                lastChip.children('button').click();
            } else {
                if (!lastChip.hasClass('involved-chip-fixed')) {
                    lastChip.addClass('involved-chip-selected');
                }
            }
        } else if (event.keyCode === 13) { // Enter
            event.preventDefault();

            // Click the highlighted dropdown item (to add the User chip)
            const selectedDropdownItem = $(involvedDropdown).find('.involved-dropdown-item-selected');
            selectedDropdownItem.click();
        } else if (event.key === 'ArrowUp' || event.keyCode === 38) { // Arrow Up
            event.preventDefault();

            // Update highlighted dropdown item

            $(involvedDropdown).children().eq(selectedDropdownItemIndex).removeClass('involved-dropdown-item-selected');

            if (selectedDropdownItemIndex === 0) {
                selectedDropdownItemIndex = dropdownCount - 1;
            } else {
                selectedDropdownItemIndex--;
            }

            $(involvedDropdown).children().eq(selectedDropdownItemIndex).addClass('involved-dropdown-item-selected');
        } else if (event.key === 'ArrowDown' || event.keyCode === 40) { // Arrow Down
            event.preventDefault();

            // Update highlighted dropdown item

            $(involvedDropdown).children().eq(selectedDropdownItemIndex).removeClass('involved-dropdown-item-selected');

            if (selectedDropdownItemIndex === dropdownCount - 1) {
                selectedDropdownItemIndex = 0;
            } else {
                selectedDropdownItemIndex++;
            }

            $(involvedDropdown).children().eq(selectedDropdownItemIndex).addClass('involved-dropdown-item-selected');
        } else if (event.key === 'Escape' || event.keyCode === 27) { // Escape
            // Hide the dropdown
            involvedDropdown.classList.add('hidden');
        }
    });

    function togglePaidDropdown() {
        splitDropdown.classList.remove('expense-expand-dropdown-open');
        groupDropdown.classList.remove('expense-expand-dropdown-open');
        mediaDropdown.classList.remove('expense-expand-dropdown-open');
        dateDropdown.classList.remove('expense-expand-dropdown-open');

        paidDropdown.classList.toggle('expense-expand-dropdown-open');
    }

    function toggleSplitDropdown() {
        paidDropdown.classList.remove('expense-expand-dropdown-open');
        groupDropdown.classList.remove('expense-expand-dropdown-open');
        mediaDropdown.classList.remove('expense-expand-dropdown-open');
        dateDropdown.classList.remove('expense-expand-dropdown-open');

        splitDropdown.classList.toggle('expense-expand-dropdown-open');
    }

    function toggleGroupDropdown() {
        paidDropdown.classList.remove('expense-expand-dropdown-open');
        splitDropdown.classList.remove('expense-expand-dropdown-open');
        mediaDropdown.classList.remove('expense-expand-dropdown-open');
        dateDropdown.classList.remove('expense-expand-dropdown-open');

        groupDropdown.classList.toggle('expense-expand-dropdown-open');
    }

    function toggleMediaDropdown() {
        paidDropdown.classList.remove('expense-expand-dropdown-open');
        splitDropdown.classList.remove('expense-expand-dropdown-open');
        groupDropdown.classList.remove('expense-expand-dropdown-open');
        dateDropdown.classList.remove('expense-expand-dropdown-open');

        mediaDropdown.classList.toggle('expense-expand-dropdown-open');
    }

    function toggleDateDropdown() {
        paidDropdown.classList.remove('expense-expand-dropdown-open');
        splitDropdown.classList.remove('expense-expand-dropdown-open');
        groupDropdown.classList.remove('expense-expand-dropdown-open');
        mediaDropdown.classList.remove('expense-expand-dropdown-open');

        dateDropdown.classList.toggle('expense-expand-dropdown-open');
    }

    // TODO: This function must update the lists in all sections that use "involved users" - change name
    // For split lists, change to only adjust the users that were added/removed to save previous settings
    function updatePaidDropdownList() {
        $(paidDropdownList).empty();
        $(splitEqualList).empty();

        const usersInvolved = Array.from(involvedChipsContainer.children).slice(0, -1);

        const currentPayer = parseInt(currentPayerInput.value);

        if (usersInvolved.length === 0) { // No users in the involved list
            $(paidDropdown).find('.paid-dropdown-empty-warning').removeClass('hidden');
            $(splitDropdown).find('.paid-dropdown-empty-warning').removeClass('hidden');
        } else {
            $(paidDropdown).find('.paid-dropdown-empty-warning').addClass('hidden');
            $(splitDropdown).find('.paid-dropdown-empty-warning').addClass('hidden');

            usersInvolved.forEach(user => {
                // Create "Paid" dropdown list with paid-dropdown-item-template

                var paidDropdownItemContent = $('#paid-dropdown-item-template').html();
                var paidDropdownItem = $(paidDropdownItemContent).clone();

                const paidItemLabel = paidDropdownItem.find('.split-equal-item');
                const paidItemInput = paidDropdownItem.find('.radio');
                const paidItemName = paidDropdownItem.find('.split-equal-item-name');

                paidItemLabel.attr('for', 'paid-dropdown-item-' + user.dataset.userId);
                paidItemLabel.attr('data-user-id', user.dataset.userId);
                paidItemLabel.attr('data-username', user.dataset.username);

                paidItemInput.attr('id', 'paid-dropdown-item-' + user.dataset.userId);
                paidItemInput.attr('value', user.dataset.userId);

                paidItemName.text(user.dataset.username)

                if (parseInt(user.dataset.userId) === currentPayer) {
                    paidItemInput.attr('checked', 'checked');
                }

                $(paidDropdownList).append(paidDropdownItem);

                // Create "Split Equal" dropdown list with split-equal-dropdown-item-template

                var splitEqualDropdownItemContent = $('#split-equal-dropdown-item-template').html();
                var splitEqualDropdownItem = $(splitEqualDropdownItemContent).clone();

                splitEqualDropdownItem.find('.split-equal-item').attr('for', 'split-equal-item-' + user.dataset.userId);
                splitEqualDropdownItem.find('.split-equal-item-checkbox').attr('id', 'split-equal-item-' + user.dataset.userId);
                splitEqualDropdownItem.find('.split-equal-item-checkbox').attr('value', user.dataset.userId);
                splitEqualDropdownItem.find('.split-equal-item-name').text(user.dataset.username);

                $(splitEqualList).append(splitEqualDropdownItem);
            });

            // Check if current payer was removed from the involved list
            if (!Array.from(usersInvolved).map(user => parseInt(user.dataset.userId)).includes(currentPayer)) {
                const firstPaidDropdownItem = paidDropdownList.firstElementChild;
                currentPayerInput.value = firstPaidDropdownItem.dataset.userId;
                $(paidBtn).children('.expense-round-btn-text').text($(firstPaidDropdownItem).find('.split-equal-item').attr('data-username'));
                $(firstPaidDropdownItem).find('.radio').attr('checked', 'checked');
            }
        }

        splitEqualUpdateSelectAll();
    }

    function setExpensePayer(payer) {
        newPayer = parseInt(payer.dataset.userId);
        currentPayerInput.value = newPayer;

        $(paidBtn).children('.expense-round-btn-text').text(payer.dataset.username);

        $(paidDropdownList).find('.fa-check').addClass('hidden');
        $(payer).children('.fa-check').removeClass('hidden');
    }

    function setExpenseSplit(tab) {
        // Update the selected tab
        $(splitTabs).children().removeClass('expense-split-tab-active');
        tab.classList.add('expense-split-tab-active');

        // Display the selected tab's content
        tabContent = document.getElementById(tab.dataset.tabId);
        $(splitTabsContent).children().addClass('hidden');
        tabContent.classList.remove('hidden');

        // Update the split button and form input
        $(splitBtn).children('.expense-round-btn-text').text(tab.dataset.tabName);
        currentSplitInput.value = tab.dataset.expenseTypeId;

        // Scroll so the selected tab is fully visible (if necessary)

        const currentPosition = splitTabs.scrollLeft;
        const containerWidth = splitTabs.offsetWidth;
        const tabWidth = tab.offsetWidth;
        const tabLeft = tab.offsetLeft;
        const tabRight = tabLeft + tabWidth;

        const nearLeftEdge = tabLeft - splitTabs.scrollLeft < 32;
        const nearRightEdge = splitTabs.scrollLeft + containerWidth - tabLeft - tabWidth < 32;

        if (nearLeftEdge) { // Scroll left so selected tab is fully visible
            const scrollAmount = -(currentPosition + 32 - tabLeft);
            const newPosition = currentPosition + scrollAmount;
            $(splitTabs).animate({ scrollLeft: newPosition }, scrollDuration);
            $('.expense-split-tabs-left-btn').css('display', newPosition > 0 ? 'block' : 'none');
            $('.expense-split-tabs-right-btn').css('display', splitTabs.scrollWidth - newPosition > splitTabs.clientWidth ? 'block' : 'none');
        } else if (nearRightEdge) { // Scroll right so selected tab is fully visible
            const scrollAmount = tabRight - (currentPosition + containerWidth - 32);
            const newPosition = currentPosition + scrollAmount;
            $(splitTabs).animate({ scrollLeft: newPosition }, scrollDuration);
            $('.expense-split-tabs-left-btn').css('display', newPosition > 0 ? 'block' : 'none');
            $('.expense-split-tabs-right-btn').css('display', splitTabs.scrollWidth - newPosition > splitTabs.clientWidth ? 'block' : 'none');
        }
    }

    function setExpenseGroup(group) {
        newGroup = parseInt(group.dataset.groupId);
        currentGroupInput.value = newGroup;

        $(groupBtn).children('.expense-round-btn-text').text(group.dataset.groupName);

        $(groupDropdownList).find('.fa-check').addClass('hidden');
        $(group).children('.fa-check').removeClass('hidden');

        // Check whether the group was changed to the default Group (Individual Expenses)
        // If it was, then the current user must be involved (can't remove their chip)
        // Otherwise, the current user can be removed (can remove their chip)

        const defaultGroupId = {{ json_encode($default_group->id) }};
        const currentUserId = {{ json_encode(auth()->user()->id) }};

        const involvedUserChips = Array.from(involvedChipsContainer.children).slice(0, -1);
        const currentUserChip = involvedUserChips.find(function(chip) {
            return parseInt(chip.dataset.userId) === currentUserId;
        });

        if (parseInt(group.dataset.groupId) === defaultGroupId) {
            if (currentUserChip) {
                $(currentUserChip).remove();
            }

            var userChipContent = $('#involved-chip-current-user-fixed-template').html();
            var userChip = $(userChipContent).clone();

            $(involvedChipsContainer).prepend(userChip);

            updatePaidDropdownList();
        } else {
            if (currentUserChip) {
                var userChipContent = $('#involved-chip-current-user-template').html();
                var userChip = $(userChipContent).clone();

                $(currentUserChip).replaceWith(userChip);

                updatePaidDropdownList();
            }
        }
    }

    datePicker.addEventListener('changeDate', function(event) {
        // Get selected date in 'yyyy-mm-dd' format
        let selectedDate = new Date(event.detail.date);

        const inputDate = selectedDate.toISOString().split('T')[0];

        let formattedDateOptions = { month: 'long', day: 'numeric', year: 'numeric' };
        const  formattedDate = selectedDate.toLocaleDateString(undefined, formattedDateOptions);

        currentDateInput.value = inputDate;
        $(dateBtn).children('.expense-round-btn-text').text(formattedDate);
    })

    function splitTabsScrollLeft() {
        const direction = 'left';
        splitTabsScroll(direction);
    }

    function splitTabsScrollRight() {
        const direction = 'right';
        splitTabsScroll(direction);
    }

    function splitTabsScroll(direction) {
        const scrollAmount = direction === 'left' ? -scrollStep : scrollStep;
        const currentPosition = splitTabs.scrollLeft;
        const newPosition = currentPosition + scrollAmount;

        $(splitTabs).animate({ scrollLeft: newPosition }, scrollDuration);

        // Update scroll arrows
        $('.expense-split-tabs-left-btn').css('display', newPosition > 0 ? 'block' : 'none');
        $('.expense-split-tabs-right-btn').css('display', splitTabs.scrollWidth - newPosition > splitTabs.clientWidth ? 'block' : 'none');
    }

    function splitTabsScrollToCurrentTab() {
        const activeTab = document.querySelector('.expense-split-tab-active');

        const containerWidth = splitTabs.offsetWidth;
        const tabWidth = activeTab.offsetWidth;
        const tabLeft = activeTab.offsetLeft;

        const nearLeftEdge = tabLeft - splitTabs.scrollLeft < 32;
        const nearRightEdge = splitTabs.scrollLeft + containerWidth - tabLeft - tabWidth < 32;

        // If the selected tab is not near the left/right edge, there is no need to scroll (it's already fully visible)
        if (!nearLeftEdge && !nearRightEdge) {
            // Update scroll arrows (for initial load)
            $('.expense-split-tabs-left-btn').css('display', splitTabs.scrollLeft > 0 ? 'block' : 'none');
            $('.expense-split-tabs-right-btn').css('display', splitTabs.scrollWidth - splitTabs.scrollLeft > splitTabs.clientWidth ? 'block' : 'none');
            return;
        }

        // Calculate the scroll position to center the selected tab
        const scrollPosition = tabLeft - (containerWidth - tabWidth) / 2;

        // Ensure scroll position is within valid range
        const minScroll = 0;
        const maxScroll = splitTabs.scrollWidth - containerWidth;
        const finalScroll = Math.max(minScroll, Math.min(maxScroll, scrollPosition));

        // Scroll to bring the selected tab into view
        splitTabs.scrollLeft = finalScroll;

        // Update scroll arrows
        $('.expense-split-tabs-left-btn').css('display', finalScroll > 0 ? 'block' : 'none');
        $('.expense-split-tabs-right-btn').css('display', splitTabs.scrollWidth - finalScroll > splitTabs.clientWidth ? 'block' : 'none');
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Scroll to bring the selected tab into view on initial load
        splitTabsScrollToCurrentTab();
    })

    function updateSplitDropdownAmounts() {
        splitEqualUpdatePriceBreakdown();
    }
</script>
