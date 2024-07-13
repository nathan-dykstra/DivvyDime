<div class="container margin-bottom-lg">
    <x-validation-warning id="expense-name-warning">{{ __('You must give the expense a name') }}</x-validation-warning>
    <x-validation-warning id="expense-amount-warning">{{ __('The expense amount must be a positive number') }}</x-validation-warning>
    <x-validation-warning id="expense-group-changed-warning">{{ __('A user was removed from the expense when you changed the group') }}</x-validation-warning>
    <x-validation-warning id="expense-payer-warning">{{ __('You must select a payer') }}</x-validation-warning>
    <x-validation-warning id="expense-participant-warning">{{ __('The expense must involve at least two users (including the payer)') }}</x-validation-warning>
    <x-validation-warning id="expense-current-user-warning">{{ __('The current user must be involved when adding an expense in ') }}<span class="bold-username">{{ $default_group->name }}</span></x-validation-warning>
    <x-validation-warning id="expense-split-amount-sum-warning">{{ __('The amounts must sum to the expense total when splitting by "Amount"') }}</x-validation-warning>

    <div class="restrict-max-width">
        <form method="post" id="expense-form" action="{{ $expense ? route('expenses.update', $expense) : route('expenses.store') }}" class="space-bottom-lg">
            @csrf
            @if ($expense)
                @method('patch')
            @endif

            <div class="expense-involved-container">
                <div class="involved-chips-container" id="involved-chips-container">
                    @if ($expense === null) <!-- Creating a new Expense -->
                        @if ($group) <!-- Expense was added from a Group, so show the Group members by default -->
                            @foreach ($group->group_members as $member)
                                <div
                                    class="involved-chip"
                                    data-user-id="{{ $member->id }}"
                                    data-username="{{ $member->username }}"
                                    data-user-img="{{ $member->getProfileImageUrlAttribute() }}"
                                >
                                    <span>{{ $member->username }}</span>
                                    <x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />
                                </div>
                            @endforeach
                        @else <!-- Expense was not added from a Group (or it was added from "Individual Expenses") -->
                            <div 
                                class="involved-chip involved-chip-fixed"
                                data-user-id="{{ $current_user->id }}"
                                data-username="{{ $current_user->username }}"
                                data-user-img="{{ $current_user->getProfileImageUrlAttribute() }}"
                            >
                                <span>{{ $current_user->username }}</span>
                            </div>

                            @if ($friend) <!-- Expense was added from a Friend -->
                                <div
                                    class="involved-chip"
                                    data-user-id="{{ $friend->id }}"
                                    data-username="{{ $friend->username }}"
                                    data-user-img="{{ $friend->getProfileImageUrlAttribute() }}"
                                >
                                    <span>{{ $friend->username }}</span>
                                    <x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />
                                </div>
                            @endif
                        @endif
                    @else <!-- Updating an existing Expense -->
                        @foreach($expense->involvedUsers() as $involved_user)
                            <div 
                                class="involved-chip {{ $involved_user->id === $current_user->id && $expense->groups->first()->id === $default_group->id ? 'involved-chip-fixed' : '' }}"
                                data-user-id="{{ $involved_user->id }}"
                                data-username="{{ $involved_user->username }}"
                                data-user-img="{{ $current_user->getProfileImageUrlAttribute() }}"
                            >
                                <span>{{ $involved_user->username }}</span>
                                @if (!($involved_user->id === $current_user->id && $expense->groups->first()->id === $default_group->id))
                                    <x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />
                                @endif
                            </div>
                        @endforeach
                    @endif

                    <input id="expense-involved" class="expense-involved" type="search" placeholder="{{ __('Who was involved?') }}" autofocus autocomplete="off" />
                </div>

                <div class="expense-involved-dropdown hidden" id="expense-involved-dropdown"></div>
            </div>

            <div class="expense-name-amount-category-container">
                <!--<x-tooltip side="bottom" icon="fa-solid fa-tag" :tooltip="__('Choose a category')">
                    <div class="expense-category">
                        TODO: category selector
                    </div>
                </x-tooltip>-->
                <div class="expense-name-amount-container">
                    <div class="expense-input-container">
                        <input id="expense-name" class="expense-form-name" name="expense-name" type="text" placeholder="{{ __('Describe the expense') }}" value="{{ old('expense-name', $expense ? $expense->name : '') }}" autocomplete="off" maxlength="255" required />
                    </div>

                    <div class="expense-input-container">
                        <span class="expense-currency">{{ __('$') }}</span><input id="expense-amount" class="expense-form-amount" name="expense-amount" type="number" step="0.01" min="0" max="99999999" placeholder="{{ __('0.00') }}" value="{{ old('expense-amount', $expense ? $expense->amount : '') }}" autocomplete="off" oninput="updateSplitDropdownAmounts()" required />
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

                    <div class="expand-dropdown" id="expense-paid-dropdown">
                        <h4 class="margin-bottom-sm">{{ __('Who paid for this expense?') }}</h4>

                        <!-- Empty dropdown warning -->
                        <div class="info-container red-background-text expense-dropdown-empty-warning hidden">
                            <div>
                                <i class="fa-solid fa-triangle-exclamation fa-lg text-warning"></i>
                            </div>
                            <div>
                                {{ __('You must add users to the expense before choosing who paid!') }}
                            </div>
                        </div>

                        <ul class="expense-paid-dropdown-list" id="expense-paid-dropdown-list">
                            @if ($expense === null) <!-- Creating a new Expense -->
                                @if ($group) <!-- Expense was added from a Group, so show the Group members by default -->
                                    @foreach ($group->group_members as $member)
                                        <li>
                                            <label class="expand-dropdown-item" for="paid-dropdown-item-{{ $member->id }}" data-user-id="{{ $member->id }}" data-username="{{ $member->username }}" onclick="setExpensePayer(this)">
                                                <input type="radio" id="paid-dropdown-item-{{ $member->id }}" class="radio" name="expense-paid" value="{{ $member->id }}" {{ $member->id === $current_user->id ? 'checked' : '' }} />
                                                <div class="dropdown-user-item-img-name">
                                                    <div class="profile-img-sm-container">
                                                        <img src="{{ $member->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img">
                                                    </div>
                                                    <div class="dropdown-user-item-name">{{ $member->username }}</div>
                                                </div>
                                            </label>
                                        </li>
                                    @endforeach
                                @else <!-- Expense was not added from a Group (or it was added from "Individual Expenses") -->
                                    <li>
                                        <label class="expand-dropdown-item" for="paid-dropdown-item-{{ $current_user->id }}" data-user-id="{{ $current_user->id }}" data-username="{{ $current_user->username }}" onclick="setExpensePayer(this)">
                                            <input type="radio" id="paid-dropdown-item-{{ $current_user->id }}" class="radio" name="expense-paid" value="{{ $current_user->id }}" checked/>
                                            <div class="dropdown-user-item-img-name">
                                                <div class="profile-img-sm-container">
                                                    <img src="{{ $current_user->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img">
                                                </div>
                                                <div class="dropdown-user-item-name">{{ $current_user->username }}</div>
                                            </div>
                                        </label>
                                    </li>

                                    @if ($friend) <!-- Expense was added from a Friend -->
                                        <li>
                                            <label class="expand-dropdown-item" for="paid-dropdown-item-{{ $friend->id }}" data-user-id="{{ $friend->id }}" data-username="{{ $friend->username }}" onclick="setExpensePayer(this)">
                                                <input type="radio" id="paid-dropdown-item-{{ $friend->id }}" class="radio" name="expense-paid" value="{{ $friend->id }}" />
                                                <div class="dropdown-user-item-img-name">
                                                    <div class="profile-img-sm-container">
                                                        <img src="{{ $friend->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img">
                                                    </div>
                                                    <div class="dropdown-user-item-name">{{ $friend->username }}</div>
                                                </div>
                                            </label>
                                        </li>
                                    @endif
                                @endif
                            @else <!-- Updating an existing Expense -->
                                @foreach ($expense->involvedUsers() as $involved_user)
                                    <li>
                                        <label class="expand-dropdown-item" for="paid-dropdown-item-{{ $involved_user->id }}" data-user-id="{{ $involved_user->id }}" data-username="{{ $involved_user->username }}" onclick="setExpensePayer(this)">
                                            <input type="radio" id="paid-dropdown-item-{{ $involved_user->id }}" class="radio" name="expense-paid" value="{{ $involved_user->id }}" {{ $expense?->payer === $involved_user->id ? 'checked' : '' }}/>
                                            <div class="dropdown-user-item-img-name">
                                                <div class="profile-img-sm-container">
                                                    <img src="{{ $involved_user->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img">
                                                </div>
                                                <div class="dropdown-user-item-name">{{ $involved_user->username }}</div>
                                            </div>
                                        </label>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>

                <div>
                    <div class="expense-paid-split">
                        {{ __('How was it split?') }}

                        <x-primary-button class="expense-round-btn" id="expense-split-btn" onclick="toggleSplitDropdown()">
                            <div class="expense-round-btn-text">
                                {{ $expense ? $expense_type_names[$expense->expense_type_id] : $expense_type_names[$default_expense_type] }}
                            </div>
                        </x-primary-button>
                    </div>

                    <div class="expand-dropdown" id="expense-split-dropdown">
                        <h4 class="margin-bottom-sm">{{ __('How do you want to divvy this up?') }}</h4>

                        <!-- Empty dropdown warning -->
                        <div class="info-container red-background-text expense-dropdown-empty-warning hidden">
                            <div>
                                <i class="fa-solid fa-triangle-exclamation fa-lg text-warning"></i>
                            </div>
                            <div>
                                {{ __('You must add users to the expense before divvying it up!') }}
                            </div>
                        </div>

                        <div id="expense-split-tabs-container">
                            <div class="expense-split-tabs-container">
                                <div class="expense-split-tabs-left-btn">
                                    <x-blur-background-button class="expense-split-tabs-scroll-btn " icon="fa-solid fa-chevron-left" onclick="splitTabsScroll('left')" />
                                </div>
                                <div class="expense-split-tabs-wrapper">
                                    @include('expenses.partials.split-tabs.expense-tab-headers')
                                </div>
                                <div class="expense-split-tabs-right-btn">
                                    <x-blur-background-button class="expense-split-tabs-scroll-btn" icon="fa-solid fa-chevron-right" onclick="splitTabsScroll('right')" />
                                </div>
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
                                <div id="expense-split-reimbursement" class="{{ $expense?->expense_type_id === $expense_type_ids['reimbursement'] ? '' : 'hidden' }}">
                                    @include('expenses.partials.split-tabs.expense-reimbursement-tab')
                                </div>
                                <div id="expense-split-itemized" class="{{ $expense?->expense_type_id === $expense_type_ids['itemized'] ? '' : 'hidden' }}">Coming soon</div>
                            </div>
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
                                @if ($expense === null) <!-- Creating a new Expense -->
                                    @if ($group) <!-- Expense was added from a Group, so show this Group by default -->
                                        {{ $group->name }}
                                    @else <!-- Expense was not added from a Group (or it was added from "Individual Expenses") -->
                                        {{ $default_group->name }}
                                    @endif
                                @else <!-- Updating an existing Expense -->
                                    {{ $expense->groups->first()->name }}
                                @endif
                            </div>
                        </x-primary-button>
                    </div>

                    <div class="expand-dropdown" id="expense-group-dropdown">
                        <h4 class="margin-bottom-sm">{{ __('Choose a group') }}</h4>

                        <ul class="expense-paid-dropdown-list" id="expense-group-dropdown-list">
                            @foreach ($groups as $dropdown_group)
                                <li>
                                    <label class="expand-dropdown-item" for="group-dropdown-item-{{ $dropdown_group->id }}" data-group-id="{{ $dropdown_group->id }}" data-group-name="{{ $dropdown_group->name }}" onclick="setExpenseGroup(this)">
                                        <input
                                            type="radio"
                                            id="group-dropdown-item-{{ $dropdown_group->id }}"
                                            class="radio"
                                            name="expense-group"
                                            value="{{ $dropdown_group->id }}"
                                            @if ($expense === null)
                                                @if ($group && $dropdown_group->id === $group->id)
                                                    checked
                                                @elseif (!$group && $dropdown_group->id === $default_group->id)
                                                    checked
                                                @endif
                                            @else
                                                @if ($expense->groups->first()->id === $dropdown_group->id)
                                                    checked
                                                @endif
                                            @endif
                                        />
                                        <div class="dropdown-user-item-img-name">
                                            <div class="group-img-sm-container">
                                                <img src="{{ $dropdown_group->getGroupImageUrlAttribute() }}" alt="Group image" class="group-img-sm">
                                            </div>
                                            <div class="dropdown-user-item-name">{{ $dropdown_group->name }}</div>
                                        </div>
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

                    <div class="expand-dropdown" id="expense-date-dropdown">
                        <h4 class="margin-bottom-sm">{{ __('When did the expense occur?') }}</h4>

                        <div class="expense-datepicker-container">
                            <!-- Flowbite Tailwind CSS Datepicker -->
                            <div id="flowbite-datepicker" inline-datepicker datepicker-format="yyyy-mm-dd" data-date="{{ $expense ? $expense->date : $today }}"></div>
                        </div>
                    </div>

                    <input type="hidden" id="expense-date" name="expense-date" value="{{ $expense ? $expense->date : $today }}" />
                </div>

                <div>
                    <div class="expense-group-date-media">
                        <x-primary-button class="expense-round-btn expense-round-btn-equal-width" id="expense-media-btn" onclick="toggleMediaDropdown()">
                            <div class="expense-round-btn-text">
                                {{ __('Add note') }}
                            </div>
                        </x-primary-button>
                    </div>

                    <div class="expand-dropdown" id="expense-media-dropdown">
                        <h4>{{ __('Add a note') }}</h4>
                        <p class="text-shy margin-bottom-sm">{{ __('Images can be added once the expense is saved.') }}</p>

                        <x-input-label for="expense-note" :value="__('Note')" />
                        <x-text-area id="expense-note" name="expense-note" maxlength="65535" :value="old('expense-note', $expense?->note ?? '')" />
                    </div>
                </div>
            </div>

            <div class="btn-container-start">
                <x-primary-button onclick="validateExpenseForm()">{{ __('Save') }}</x-primary-button>
            </div>
        </form>
    </div>

    <!-- HTML Templates -->

    <template id="involved-chip-template">
        <div class="involved-chip" data-user-id="" data-username="" data-user-img="">
            <div class="involved-chip-text"></div>
            <x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />
        </div>
    </template>

    <template id="involved-chip-current-user-fixed-template">
        <div
            class="involved-chip involved-chip-fixed"
            data-user-id="{{ $current_user->id }}"
            data-username="{{ $current_user->username }}"
            data-user-img="{{ $current_user->getProfileImageUrlAttribute() }}"
        >
            <div class="involved-chip-text">{{ $current_user->username }}</div>
        </div>
    </template>

    <template id="involved-chip-current-user-template">
        <div
            class="involved-chip"
            data-user-id="{{ $current_user->id }}"
            data-username="{{ $current_user->username }}"
            data-user-img="{{ $current_user->getProfileImageUrlAttribute() }}"
        >
            <div class="involved-chip-text">{{ $current_user->username }}</div>
            <x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />
        </div>
    </template>

    <template id="dropdown-item-already-involved-template">
        <div class="involved-dropdown-item" onmouseover="highlightDropdownItem(this)">
            <div class="dropdown-user-item-img-name">
                <div class="profile-img-sm-container">
                    <img src="" alt="User profile image" class="profile-img">
                </div>
                <div>
                    <div class="involved-dropdown-user-name"></div>
                    <div class="text-shy">{{ __('Already involved') }}</div>
                </div>
            </div>
            <i class="fa-solid fa-user-check friend-added-icon"></i>
        </div>
    </template>

    <template id="dropdown-item-not-involved-template">
        <div class="involved-dropdown-item" onmouseover="highlightDropdownItem(this)">
            <div class="dropdown-user-item-img-name">
                <div class="profile-img-sm-container">
                    <img src="" alt="User profile image" class="profile-img">
                </div>
                <div>
                    <div class="involved-dropdown-user-name"></div>
                    <div class="text-shy involved-dropdown-user-email"></div>
                </div>
            </div>
            <i class="fa-solid fa-user-plus add-friend-icon"></i>
        </div>
    </template>

    <template id="dropdown-item-group-template">
        <div class="involved-dropdown-item" onmouseover="highlightDropdownItem(this)">
            <div class="dropdown-user-item-img-name">
                <div class="group-img-sm-container">
                    <img src="" alt="Group image" class="group-img-sm">
                </div>
                <div>
                    <div class="involved-dropdown-user-name"></div>
                </div>
            </div>
        </div>
    </template>

    <template id="dropdown-divider-template">
        <div class="involved-dropdown-divider"></div>
    </template>

    <template id="paid-dropdown-item-template">
        <li>
            <label class="expand-dropdown-item" for="" data-user-id="" data-username="" onclick="setExpensePayer(this)">
                <input type="radio" id="" class="radio" name="expense-paid" value="" />
                <div class="dropdown-user-item-img-name">
                    <div class="profile-img-sm-container">
                        <img src="" alt="User profile image" class="profile-img">
                    </div>
                    <div class="dropdown-user-item-name"></div>
                </div>
            </label>
        </li>
    </template>
</div>

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
    const splitTabsContainer = document.getElementById('expense-split-tabs-container');
    const splitTabs = document.getElementById('expense-split-tabs');
    const splitTabsContent = document.getElementById('expense-split-tabs-content');
    const groupDropdownList = document.getElementById('expense-group-dropdown-list');
    const datePicker = document.getElementById('flowbite-datepicker');

    const splitEqualList = document.getElementById('split-equal-list');
    const splitAmountList = document.getElementById('split-amount-list');
    const splitReimbursementList = document.getElementById('split-reimbursement-list');

    const currentNameInput = document.getElementById('expense-name');
    const currentAmountInput = document.getElementById('expense-amount');
    const currentPayerInput = document.querySelector('input[name="expense-paid"]:checked');
    const currentSplitInput = document.getElementById('expense-split');
    const currentGroupInput = document.querySelector('input[name="expense-group"]:checked');
    const currentDateInput = document.getElementById('expense-date');
    const currentNoteInput = document.getElementById('expense-note');

    const paidBtn = document.getElementById('expense-paid-btn');
    const splitBtn = document.getElementById('expense-split-btn');
    const groupBtn = document.getElementById('expense-group-btn');
    const dateBtn = document.getElementById('expense-date-btn');
    const mediaBtn = document.getElementById('expense-media-btn');

    const expenseNameValidationWarning = document.getElementById('expense-name-warning');
    const expenseAmountValidationWarning = document.getElementById('expense-amount-warning');
    const groupChangeValidationWarning = document.getElementById('expense-group-changed-warning');
    const payerValidationWarning = document.getElementById('expense-payer-warning');
    const participantValidationWarning = document.getElementById('expense-participant-warning');
    const currentUserValidationWarning = document.getElementById('expense-current-user-warning');
    const splitAmountSumValidationWarning = document.getElementById('expense-split-amount-sum-warning');

    let selectedDropdownItemIndex = 0;

    involvedFriendsInput.addEventListener('input', function(event) {
        const searchString = event.target.value;

        // Remove backspace highlight on last user chip (if it exists)
        if (involvedChipsContainer.children.length >= 2 && searchString !== '') {
            const lastChip = involvedChipsContainer.children[involvedChipsContainer.children.length - 2];
            lastChip.classList.remove('involved-chip-selected');
        }

        $.ajax({
            url: "{{ route('expenses.search-friends-to-include') }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'search_string': searchString,
                'group_id': currentGroupInput.value,
            },
            success: function(response) {
                if (searchString === '') {
                    involvedDropdown.classList.add('hidden');
                } else {
                    displaySearchResults(response);
                }
            },
            error: function(error) {
                console.error(error);
            }
        });
    });

    involvedFriendsInput.addEventListener('blur', function() {
        // Remove backspace highlight on last user chip (if it exists)
        if (involvedChipsContainer.children.length >= 2) {
            const lastChip = involvedChipsContainer.children[involvedChipsContainer.children.length - 2];
            lastChip.classList.remove('involved-chip-selected');
        }
    });

    function displaySearchResults(response) {
        // Clear the dropdown from any previous results
        involvedDropdown.innerHTML = '';

        if (response.groups.length || response.users.length) {
            response.groups.forEach(group => {
                let dropdownItem;

                let dropdownItemTemplate = document.getElementById('dropdown-item-group-template');
                dropdownItem = dropdownItemTemplate.content.cloneNode(true);

                dropdownItem.querySelector('.group-img-sm').src = group.group_image_url;
                dropdownItem.querySelector('.involved-dropdown-user-name').textContent = group.name;

                dropdownItem.querySelector('.involved-dropdown-item').addEventListener('click', () => {
                    addGroupChips(group);
                });

                // Add the item to the involved users search results dropdown
                involvedDropdown.appendChild(dropdownItem);
            });

            if (response.groups.length && response.users.length) {
                let dropdownDividerTemplate = document.getElementById('dropdown-divider-template');
                dropdownDivider = dropdownDividerTemplate.content.cloneNode(true);

                involvedDropdown.appendChild(dropdownDivider);
            }

            // Get an array of user IDs that are already involved
            const usersAlreadyInvolved = Array.from(involvedChipsContainer.children).map(child => parseInt(child.dataset.userId));

            response.users.forEach(user => {
                let dropdownItem;

                if (usersAlreadyInvolved.includes(parseInt(user.id))) { // This user has already been added
                    let dropdownItemTemplate = document.getElementById('dropdown-item-already-involved-template');
                    dropdownItem = dropdownItemTemplate.content.cloneNode(true);

                    dropdownItem.querySelector('.profile-img').src = user.profile_image_url;
                    dropdownItem.querySelector('.involved-dropdown-user-name').textContent = user.username;

                    dropdownItem.querySelector('.involved-dropdown-item').addEventListener('click', () => {
                        involvedDropdown.classList.add('hidden');
                        involvedFriendsInput.value = '';
                        involvedFriendsInput.focus();
                    });
                } else { // This user has not yet been added
                    let dropdownItemTemplate = document.getElementById('dropdown-item-not-involved-template');
                    dropdownItem = dropdownItemTemplate.content.cloneNode(true);

                    dropdownItem.querySelector('.profile-img').src = user.profile_image_url;
                    dropdownItem.querySelector('.involved-dropdown-user-name').textContent = user.username;
                    dropdownItem.querySelector('.involved-dropdown-user-email').textContent = user.email;

                    dropdownItem.querySelector('.involved-dropdown-item').addEventListener('click', () => {
                        addUserChip(user);
                    });
                }

                // Add the item to the involved users search results dropdown
                involvedDropdown.appendChild(dropdownItem);
            });

            // Highlight the first item in the dropdown
            selectedDropdownItemIndex = 0;
            involvedDropdown.children[0].classList.add('involved-dropdown-item-selected');
            involvedDropdown.classList.remove('hidden');
        } else {
            // No matching search results, hide the dropdown
            involvedDropdown.classList.add('hidden');
        }
    }

    function addUserChip(user) {
        let userChipTemplate = document.getElementById('involved-chip-template');
        let userChip = userChipTemplate.content.cloneNode(true);

        // Configure the chip content
        userChip.querySelector('.involved-chip-text').textContent = user.username;
        userChip.querySelector('.involved-chip').dataset.userId = user.id;
        userChip.querySelector('.involved-chip').dataset.username = user.username;
        userChip.querySelector('.involved-chip').dataset.userImg = user.profile_image_url;

        // Add the chip
        const involvedSearchInput = involvedChipsContainer.querySelector('.expense-involved');
        involvedSearchInput.parentNode.insertBefore(userChip, involvedSearchInput);

        // Update the expense dropdowns with the new user
        addExpenseUser(user);

        // Clear and hide search dropdown results
        involvedDropdown.classList.add('hidden');
        involvedFriendsInput.value = '';
        involvedFriendsInput.focus();
    }

    function addGroupChips(group) {
        // Get an array of user IDs that are already involved
        const usersAlreadyInvolved = Array.from(involvedChipsContainer.children).map(child => parseInt(child.dataset.userId));

        group.group_members.forEach(member => {
            if (usersAlreadyInvolved.includes(parseInt(member.id))) {
                return; // User is already involved in the expense
            }

            let userChipTemplate = document.getElementById('involved-chip-template');
            let userChip = userChipTemplate.content.cloneNode(true);

            // Configure the chip content
            userChip.querySelector('.involved-chip-text').textContent = member.username;
            userChip.querySelector('.involved-chip').dataset.userId = member.id;
            userChip.querySelector('.involved-chip').dataset.username = member.username;
            userChip.querySelector('.involved-chip').dataset.userImg = member.profile_image_url;

            // Add the chip
            const involvedSearchInput = involvedChipsContainer.querySelector('.expense-involved');
            involvedSearchInput.parentNode.insertBefore(userChip, involvedSearchInput);

            // Update the expense dropdowns with the new user
            addExpenseUser(member);
        })

        const groupElement = groupDropdownList.querySelector(`[data-group-id="${group.id}"]`);
        if (groupElement) {
            groupElement.click(); // Click the corresponding group to set the expense group
        }

        // Clear and hide search dropdown results
        involvedDropdown.classList.add('hidden');
        involvedFriendsInput.value = '';
        involvedFriendsInput.focus();
    }

    involvedChipsContainer.addEventListener('click', function() {
        involvedFriendsInput.focus();
    });

    function removeUserChip(removeBtn) {
        userChip = removeBtn.closest('.involved-chip');
        userChip.parentNode.removeChild(userChip);

        involvedFriendsInput.value = '';
        involvedFriendsInput.focus();

        // Update the expense dropdowns with the removed user
        //updatePaidDropdownList();

        removeExpenseUser(userChip.dataset.userId);
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
        // Highlight the specified involved users search dropdown item
        if (!item.classList.contains('involved-dropdown-item-selected')) {
            involvedDropdown.querySelector('.involved-dropdown-item-selected').classList.remove('involved-dropdown-item-selected');
            item.classList.add('involved-dropdown-item-selected');

            const itemIndex = Array.from(involvedDropdown.querySelectorAll(':scope > :not(.involved-dropdown-divider)')).indexOf(item);
            selectedDropdownItemIndex = itemIndex;
        }
    }

    involvedFriendsInput.addEventListener('keydown', function(event) {
        const dropdownItemsCount = involvedDropdown.querySelectorAll(':scope > :not(.involved-dropdown-divider)').length;

        if ((event.key === 'Backspace' || event.keyCode === 8) && event.target.value === '' && involvedChipsContainer.children.length >= 2) { // Backspace
            // Highlight/delete the last User chip
            const lastChip = involvedChipsContainer.children[involvedChipsContainer.children.length - 2];
            if (lastChip.classList.contains('involved-chip-selected')) {
                // Remove the chip
                lastChip.querySelector('button').click();
            } else {
                // Highlight the chip on backspace (if it's not a fixed chip)
                if (!lastChip.classList.contains('involved-chip-fixed')) {
                    lastChip.classList.add('involved-chip-selected');
                }
            }
        } else if (event.key === 'Enter' || event.keyCode === 13) { // Enter
            event.preventDefault();

            // Click the highlighted dropdown item (to add the corresponding chip)
            const selectedDropdownItem = involvedDropdown.querySelector('.involved-dropdown-item-selected');
            selectedDropdownItem.click();
        } else if (event.key === 'ArrowUp' || event.keyCode === 38) { // Arrow Up
            event.preventDefault();

            // Update highlighted dropdown item
            involvedDropdown.querySelectorAll(':scope > :not(.involved-dropdown-divider)')[selectedDropdownItemIndex].classList.remove('involved-dropdown-item-selected');
            if (selectedDropdownItemIndex === 0) {
                selectedDropdownItemIndex = dropdownItemsCount - 1;
            } else {
                selectedDropdownItemIndex--;
            }
            involvedDropdown.querySelectorAll(':scope > :not(.involved-dropdown-divider)')[selectedDropdownItemIndex].classList.add('involved-dropdown-item-selected');
        } else if (event.key === 'ArrowDown' || event.keyCode === 40) { // Arrow Down
            event.preventDefault();

            // Update highlighted dropdown item
            involvedDropdown.querySelectorAll(':scope > :not(.involved-dropdown-divider)')[selectedDropdownItemIndex].classList.remove('involved-dropdown-item-selected');
            if (selectedDropdownItemIndex === dropdownItemsCount - 1) {
                selectedDropdownItemIndex = 0;
            } else {
                selectedDropdownItemIndex++;
            }
            involvedDropdown.querySelectorAll(':scope > :not(.involved-dropdown-divider)')[selectedDropdownItemIndex].classList.add('involved-dropdown-item-selected');
        } else if (event.key === 'Escape' || event.keyCode === 27) { // Escape
            // Hide the dropdown
            involvedDropdown.classList.add('hidden');
        }
    });

    function togglePaidDropdown() {
        // Open the Payer dropdown and close all other dropdowns
        splitDropdown.classList.remove('expand-dropdown-open');
        groupDropdown.classList.remove('expand-dropdown-open');
        mediaDropdown.classList.remove('expand-dropdown-open');
        dateDropdown.classList.remove('expand-dropdown-open');

        paidDropdown.classList.toggle('expand-dropdown-open');
    }

    function toggleSplitDropdown() {
        // Open the Split dropdown and close all other dropdowns
        paidDropdown.classList.remove('expand-dropdown-open');
        groupDropdown.classList.remove('expand-dropdown-open');
        mediaDropdown.classList.remove('expand-dropdown-open');
        dateDropdown.classList.remove('expand-dropdown-open');

        splitTabsScrollToCurrentTab();
        splitDropdown.classList.toggle('expand-dropdown-open');
    }

    function toggleGroupDropdown() {
        // Open the Group dropdown and close all other dropdowns
        paidDropdown.classList.remove('expand-dropdown-open');
        splitDropdown.classList.remove('expand-dropdown-open');
        mediaDropdown.classList.remove('expand-dropdown-open');
        dateDropdown.classList.remove('expand-dropdown-open');

        groupDropdown.classList.toggle('expand-dropdown-open');
    }

    function toggleMediaDropdown() {
        // Open the Note dropdown and close all other dropdowns
        paidDropdown.classList.remove('expand-dropdown-open');
        splitDropdown.classList.remove('expand-dropdown-open');
        groupDropdown.classList.remove('expand-dropdown-open');
        dateDropdown.classList.remove('expand-dropdown-open');

        mediaDropdown.classList.toggle('expand-dropdown-open');
    }

    function toggleDateDropdown() {
        // Open the Date dropdown and close all other dropdowns
        paidDropdown.classList.remove('expand-dropdown-open');
        splitDropdown.classList.remove('expand-dropdown-open');
        groupDropdown.classList.remove('expand-dropdown-open');
        mediaDropdown.classList.remove('expand-dropdown-open');

        dateDropdown.classList.toggle('expand-dropdown-open');
    }

    function findExpenseUserInsertIndex(username) {
        const currentInvolvedUsers = Array.from(paidDropdownList.children).map(item => item.querySelector('.expand-dropdown-item').dataset.username);
        for (let i = 0; i < currentInvolvedUsers.length; i++) {
            if (username.localeCompare(currentInvolvedUsers[i]) < 0) {
                return i;
            }
        }
        return currentInvolvedUsers.length;
    }

    function addExpenseUser(user) {
        // Note: user has user.id, user.username, and user.profile_image_url to populate the dropdowns

        hideEmptyDropdownWarnings();

        // Find index to insert the user (maintain alphabetical ordering)
        const insertIndex = findExpenseUserInsertIndex(user.username);
        
        // Add user to the "Paid" dropdown list

        let paidDropdownItemTemplate = document.getElementById('paid-dropdown-item-template');
        let paidDropdownItem = paidDropdownItemTemplate.content.cloneNode(true);

        let paidDropdownItemLabel = paidDropdownItem.querySelector('.expand-dropdown-item');
        let paidDropdownItemInput = paidDropdownItem.querySelector('.radio');

        paidDropdownItemLabel.setAttribute('for', 'paid-dropdown-item-' + user.id);
        paidDropdownItemLabel.dataset.userId = user.id;
        paidDropdownItemLabel.dataset.username = user.username;

        paidDropdownItemInput.setAttribute('id', 'paid-dropdown-item-' + user.id);
        paidDropdownItemInput.value = user.id;

        paidDropdownItem.querySelector('.dropdown-user-item-name').textContent = user.username;
        paidDropdownItem.querySelector('.profile-img').src = user.profile_image_url;

        if (insertIndex === paidDropdownList.children.length) {
            paidDropdownList.appendChild(paidDropdownItem);
        } else {
            paidDropdownList.insertBefore(paidDropdownItem, paidDropdownList.children[insertIndex]);
        }

        // Select this new user as the payer if the list was empty
        if (paidDropdownList.children.length === 1) {
            paidDropdownList.querySelector('.expand-dropdown-item').click();
        }

        // Add user to the "Split Equal" dropdown list

        let splitEqualDropdownItemTemplate = document.getElementById('split-equal-dropdown-item-template');
        let splitEqualDropdownItem = splitEqualDropdownItemTemplate.content.cloneNode(true);
        
        splitEqualDropdownItem.querySelector('.expand-dropdown-item').setAttribute('for', 'split-equal-item-' + user.id);
        splitEqualDropdownItem.querySelector('.expand-dropdown-item').dataset.userId = user.id;
        splitEqualDropdownItem.querySelector('.split-equal-item-checkbox').setAttribute('id', 'split-equal-item-' + user.id);
        splitEqualDropdownItem.querySelector('.split-equal-item-checkbox').setAttribute('value', user.id);
        splitEqualDropdownItem.querySelector('.dropdown-user-item-name').textContent = user.username;
        splitEqualDropdownItem.querySelector('.profile-img').src = user.profile_image_url;

        if (insertIndex === splitEqualList.children.length) {
            splitEqualList.appendChild(splitEqualDropdownItem);
        } else {
            splitEqualList.insertBefore(splitEqualDropdownItem, splitEqualList.children[insertIndex]);
        }

        // Add user to the "Split Amount" dropdown list

        let splitAmountDropdownItemTemplate = document.getElementById('split-amount-dropdown-item-template');
        let splitAmountDropdownItem = splitAmountDropdownItemTemplate.content.cloneNode(true);

        splitAmountDropdownItem.querySelector('.split-amount-item').setAttribute('for', 'split-amount-item-' + user.id);
        splitAmountDropdownItem.querySelector('.split-amount-item').dataset.userId = user.id;
        splitAmountDropdownItem.querySelector('.text-input-prepend').setAttribute('id', 'split-amount-item-' + user.id);
        splitAmountDropdownItem.querySelector('.text-input-prepend').setAttribute('name', 'split-amount-item-' + user.id);
        splitAmountDropdownItem.querySelector('.dropdown-user-item-name').textContent = user.username;
        splitAmountDropdownItem.querySelector('.profile-img').src = user.profile_image_url;

        if (insertIndex === splitAmountList.children.length) {
            splitAmountList.appendChild(splitAmountDropdownItem);
        } else {
            splitAmountList.insertBefore(splitAmountDropdownItem, splitAmountList.children[insertIndex]);
        }

        // Add user to the "Split Reimbursement" dropdown list

        let splitReimbursementDropdownItemTemplate = document.getElementById('split-reimbursement-dropdown-item-template');
        let splitReimbursementDropdownItem = splitReimbursementDropdownItemTemplate.content.cloneNode(true);

        splitReimbursementDropdownItem.querySelector('.expand-dropdown-item').setAttribute('for', 'split-reimbursement-item-' + user.id);
        splitReimbursementDropdownItem.querySelector('.expand-dropdown-item').dataset.userId = user.id;
        splitReimbursementDropdownItem.querySelector('.split-reimbursement-item-checkbox').setAttribute('id', 'split-reimbursement-item-' + user.id);
        splitReimbursementDropdownItem.querySelector('.split-reimbursement-item-checkbox').setAttribute('value', user.id);
        splitReimbursementDropdownItem.querySelector('.dropdown-user-item-name').textContent = user.username;
        splitReimbursementDropdownItem.querySelector('.profile-img').src = user.profile_image_url;

        if (insertIndex === splitReimbursementList.children.length) {
            splitReimbursementList.appendChild(splitReimbursementDropdownItem);
        } else {
            splitReimbursementList.insertBefore(splitReimbursementDropdownItem, splitReimbursementList.children[insertIndex]);
        }

        // Make sure price breakdowns in the "Split" dropdowns are updated with the new user
        updateSplitDropdownAmounts();
    }

    function removeExpenseUser(userId) {
        // Remove user from the "Paid" dropdown list
        Array.from(paidDropdownList.children).forEach(child => {
            if (child.querySelector('.expand-dropdown-item').dataset.userId === userId) {
                paidDropdownList.removeChild(child);
            }
        });

        // Remove user from the "Split Equal" dropdown list
        Array.from(splitEqualList.children).forEach(child => {
            if (child.querySelector('.expand-dropdown-item').dataset.userId === userId) {
                splitEqualList.removeChild(child);
            }
        });

        // Remove user from the "Split Amount" dropdown list
        Array.from(splitAmountList.children).forEach(child => {
            if (child.querySelector('.split-amount-item').dataset.userId === userId) {
                splitAmountList.removeChild(child);
            }
        });

        // Remove user from the "Split Reimbursement" dropdown list
        Array.from(splitReimbursementList.children).forEach(child => {
            if (child.querySelector('.expand-dropdown-item').dataset.userId === userId) {
                splitReimbursementList.removeChild(child);
            }
        });

        // Additional updates to the menus if there are no involved users left
        if (paidDropdownList.children.length === 0) {
            currentPayerInput.value = null;
            paidBtn.querySelector('.expense-round-btn-text').textContent = 'Choose a payer';

            showEmptyDropdownWarnings();
        } else {
            // If the removed user was the current payer, set the first user in the "Paid" dropdown list as the new payer
            if (parseInt(userId) === parseInt(currentPayerInput.value)) {
                const firstPaidDropdownItem = paidDropdownList.firstElementChild;
                currentPayerInput.value = firstPaidDropdownItem.dataset.userId;
                paidBtn.querySelector('.expense-round-btn-text').textContent = firstPaidDropdownItem.querySelector('.expand-dropdown-item').dataset.username;
                firstPaidDropdownItem.querySelector('.radio').checked = true;
            }

            hideEmptyDropdownWarnings();
        }

        // Update price breakdowns and the "Select All" checkboxes in the "Split" dropdown lists
        updateSplitDropdownAmounts();
        updateSplitDropdownSelectAll();
    }

    function showEmptyDropdownWarnings() {
        paidDropdown.querySelector('.expense-dropdown-empty-warning').classList.remove('hidden');
        splitDropdown.querySelector('.expense-dropdown-empty-warning').classList.remove('hidden');
        splitTabsContainer.classList.add('hidden');
    }

    function hideEmptyDropdownWarnings() {
        paidDropdown.querySelector('.expense-dropdown-empty-warning').classList.add('hidden');
        splitDropdown.querySelector('.expense-dropdown-empty-warning').classList.add('hidden');
        splitTabsContainer.classList.remove('hidden');
    }

    // Update the amount breakdowns shown in the "Split" dropdown lists
    function updateSplitDropdownAmounts() {
        splitEqualUpdatePriceBreakdown();
        splitAmountUpdateTotal();
        splitReimbursementUpdatePriceBreakdown();
    }

    // Update the "Select All" checkboxes in the "Split" dropdown lists
    function updateSplitDropdownSelectAll() {
        splitEqualUpdateSelectAll();
        splitReimbursementUpdateSelectAll();
    }

    // "Split Equal" dropdown list functions

    function splitEqualSelectAll(selectAllCheckbox) {
        let checkboxes = document.querySelectorAll('.split-equal-item-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });

        splitEqualUpdatePriceBreakdown();
    }

    function splitEqualUpdateSelectAll() {
        let checkboxes = document.querySelectorAll('.split-equal-item-checkbox');
        let checkedCheckboxes = document.querySelectorAll('.split-equal-item-checkbox:checked');
        document.getElementById('split-equal-select-all').checked = checkboxes.length === checkedCheckboxes.length;

        splitEqualUpdatePriceBreakdown();
    }

    function splitEqualUpdatePriceBreakdown() {
        const currentParticipantCount = document.querySelectorAll('.split-equal-item-checkbox:checked').length;
        const amountPerParticipant = currentParticipantCount === 0 || currentAmountInput.value === '' ? 0 : parseFloat(currentAmountInput.value) / currentParticipantCount;

        document.querySelector('.split-equal-price-breakdown').textContent = amountPerParticipant.toFixed(2);
        document.getElementById('split-equal-participant-count').textContent = currentParticipantCount;
        document.getElementById('split-equal-participant-count-label').textContent = currentParticipantCount === 1 ? ' person' : ' people';
    }

    // "Split Amount" dropdown list functions

    function splitAmountUpdateTotal() {
        const splitAmountItems = splitAmountList.querySelectorAll('li');

        let newTotal = 0.00;
        let amountLeft = currentAmountInput.value === '' ? 0.00 : parseFloat(currentAmountInput.value);

        splitAmountItems.forEach((item) => {
            const itemAmount = item.querySelector('.text-input-prepend').value;

            if (itemAmount !== '') {
                newTotal += parseFloat(itemAmount);
                amountLeft -= parseFloat(itemAmount);
            }
        });

        document.getElementById('split-amount-total').textContent = newTotal.toFixed(2);
        document.getElementById('split-amount-left').textContent = amountLeft.toFixed(2);
    }

    // "Split Reimbursement" dropdown list functions

    function splitReimbursementSelectAll(selectAllCheckbox) {
        let checkboxes = document.querySelectorAll('.split-reimbursement-item-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAllCheckbox.checked;
        });

        splitReimbursementUpdatePriceBreakdown();
    }

    function splitReimbursementUpdateSelectAll() {
        let checkboxes = document.querySelectorAll('.split-reimbursement-item-checkbox');
        let checkedCheckboxes = document.querySelectorAll('.split-reimbursement-item-checkbox:checked');
        document.getElementById('split-reimbursement-select-all').checked = checkboxes.length === checkedCheckboxes.length;

        splitReimbursementUpdatePriceBreakdown();
    }

    function splitReimbursementUpdatePriceBreakdown() {
        const currentParticipantCount = document.querySelectorAll('.split-reimbursement-item-checkbox:checked').length;
        const amountPerParticipant = currentParticipantCount === 0 || currentAmountInput.value === '' ? 0 : parseFloat(currentAmountInput.value) / currentParticipantCount;

        document.getElementById('split-reimbursement-price-breakdown').textContent = amountPerParticipant.toFixed(2);
        document.getElementById('split-reimbursement-participant-count').textContent = currentParticipantCount;
        document.getElementById('split-reimbursement-participant-count-label').textContent = currentParticipantCount === 1 ? ' person' : ' people';
    }

    // Set the expense payer, split, group, and date

    function setExpensePayer(payer) {
        newPayer = parseInt(payer.dataset.userId);
        currentPayerInput.value = newPayer;
        paidBtn.querySelector('.expense-round-btn-text').textContent = payer.dataset.username;
        hideValidationWarning(payerValidationWarning);
    }

    function setExpenseSplit(tab) {
        // Update the selected tab
        splitTabs.querySelector('.expense-split-tab-active').classList.remove('expense-split-tab-active');
        tab.classList.add('expense-split-tab-active');

        // Display the selected tab's content
        tabContent = document.getElementById(tab.dataset.tabId);
        Array.from(splitTabsContent.children).forEach(child => child.classList.add('hidden'));
        tabContent.classList.remove('hidden');

        // Update the split button and form input
        splitBtn.querySelector('.expense-round-btn-text').textContent = tab.dataset.tabName;
        currentSplitInput.value = tab.dataset.expenseTypeId;

        // Scroll so the selected tab is fully visible (if necessary)
        splitTabsScrollToCurrentTab();
    }

    function setExpenseGroup(group) {
        newGroup = parseInt(group.dataset.groupId);
        currentGroupInput.value = newGroup;
        groupBtn.querySelector('.expense-round-btn-text').textContent = group.dataset.groupName;

        $.ajax({
            url: "{{ route('expenses.get-expense-group-details') }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'group_id': newGroup,
            },
            success: function(response) {
                const groupIsDefault = response.group_is_default;
                const groupMembers = response.group_members;
                const currentUserId = response.current_user_id;

                let currentUserChip = involvedChipsContainer.querySelector('.involved-chip[data-user-id="' + currentUserId + '"]');

                if (groupIsDefault) {
                    // Fix the current user's chip so it can't be removed

                    fixedCurrentUserChipTemplate = document.getElementById('involved-chip-current-user-fixed-template');
                    fixedCurrentUserChip = fixedCurrentUserChipTemplate.content.cloneNode(true);

                    if (currentUserChip) {
                        currentUserChip.parentNode.removeChild(currentUserChip);
                    } else {
                        // Current user must be added to the expense menus
                        currentUser = {
                            id: fixedCurrentUserChip.querySelector('.involved-chip-fixed').dataset.userId,
                            username: fixedCurrentUserChip.querySelector('.involved-chip-fixed').dataset.username,
                            profile_image_url: fixedCurrentUserChip.querySelector('.involved-chip-fixed').dataset.userImg
                        };
                        addExpenseUser(currentUser);
                    }

                    involvedChipsContainer.insertBefore(fixedCurrentUserChip, involvedChipsContainer.firstChild);
                } else if (currentUserChip && currentUserChip.classList.contains('involved-chip-fixed')) {
                    // Unfix the current user's chip so it can be removed
                    currentUserChip.parentNode.removeChild(currentUserChip);
                    currentUserChipTemplate = document.getElementById('involved-chip-current-user-template');
                    currentUserChip = currentUserChipTemplate.content.cloneNode(true);
                    involvedChipsContainer.insertBefore(currentUserChip, involvedChipsContainer.firstChild);

                    // Check each involved user to see if they are in the new group
                    // If not, remove them from the expense
                    // TODO: add validation warning here if chip is removed
                    Array.from(involvedChipsContainer.children).slice(0, -1).forEach(chip => {
                        const userId = parseInt(chip.dataset.userId);
                        if (!groupMembers.includes(userId)) {
                            chip.querySelector('button').click();
                            showValidationWarning(groupChangeValidationWarning);
                        }
                    });
                }
            },
            error: function(error) {
                console.error(error);
            }
        });
    }

    datePicker.addEventListener('changeDate', function(event) {
        // Get selected date in 'yyyy-mm-dd' format
        let selectedDate = new Date(event.detail.date);

        const inputDate = selectedDate.toISOString().split('T')[0];

        let formattedDateOptions = { month: 'long', day: 'numeric', year: 'numeric' };
        const  formattedDate = selectedDate.toLocaleDateString(undefined, formattedDateOptions);

        currentDateInput.value = inputDate;
        dateBtn.querySelector('.expense-round-btn-text').textContent = formattedDate;
    })

    // "Split" dropdown tabs functions

    const splitTabsWrapper = document.querySelector('.expense-split-tabs-wrapper');

    splitTabsWrapper.addEventListener('touchstart', (event) => {
        const startX = event.touches[0].pageX;
        const scrollLeft = splitTabsWrapper.scrollLeft;

        function onTouchMove(e) {
            const x = e.touches[0].pageX;
            const walk = x - startX;
            splitTabsWrapper.scrollLeft = scrollLeft - walk;
        }

        function onTouchEnd() {
            splitTabsWrapper.removeEventListener('touchmove', onTouchMove);
            splitTabsWrapper.removeEventListener('touchend', onTouchEnd);
        }

        splitTabsWrapper.addEventListener('touchmove', onTouchMove);
        splitTabsWrapper.addEventListener('touchend', onTouchEnd);
    });

    function splitTabsScrollToCurrentTab() {
        const activeTab = document.querySelector('.expense-split-tab-active');

        // Get the position and size of the active tab
        const tabRect = activeTab.getBoundingClientRect();
        const tabListRect = splitTabs.getBoundingClientRect();

        // Calculate the offset to center the active tab
        const tabListScrollLeft = splitTabsWrapper.scrollLeft;
        const offsetLeft = tabRect.left - tabListRect.left;
        const offsetCenter = offsetLeft - (tabListRect.width / 2) + (tabRect.width / 2);

        // Ensure the scroll position keeps the tab fully visible if near the edges
        const maxScrollLeft = splitTabs.scrollWidth - tabListRect.width;
        const scrollPosition = Math.min(Math.max(offsetCenter, 0), maxScrollLeft);

        // Scroll the container to the calculated position
        splitTabsWrapper.scrollTo({ left: scrollPosition, behavior: 'smooth' });

        updateSplitTabArrows();
    }

    function splitTabsScroll(direction) {
        const scrollAmount = 200;
        const currentScroll = splitTabsWrapper.scrollLeft;

        if (direction === 'left') {
            splitTabsWrapper.scrollTo({ left: currentScroll - scrollAmount, behavior: 'smooth' });
        } else if (direction === 'right') {
            splitTabsWrapper.scrollTo({ left: currentScroll + scrollAmount, behavior: 'smooth' });
        }

        updateSplitTabArrows();
    }

    function updateSplitTabArrows() {
        const leftBtn = document.querySelector('.expense-split-tabs-left-btn');
        const rightBtn = document.querySelector('.expense-split-tabs-right-btn');

        if (splitTabsWrapper.scrollLeft > 0) {
            leftBtn.classList.remove('hidden');
        } else {
            leftBtn.classList.add('hidden');
        }

        if (splitTabsWrapper.scrollWidth - splitTabsWrapper.clientWidth - splitTabsWrapper.scrollLeft > 1) {
            rightBtn.classList.remove('hidden');
        } else {
            rightBtn.classList.add('hidden');
        }
    }

    splitTabsWrapper.addEventListener('scroll', updateSplitTabArrows);
    window.addEventListener('resize', updateSplitTabArrows);

    Array.from(splitTabs.children).forEach(tab => {
        tab.addEventListener('keydown', function() {
            if (event.key === 'Enter' || event.keyCode === 13) {
                setExpenseSplit(tab);
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Scroll to bring the selected tab into view on initial load
        splitTabsScrollToCurrentTab();

        // Resize the "Note" textarea to fit it's content
        resizeTextarea(currentNoteInput);

        // Update the "Split" dropdown list "Select All" checkboxes with the initial selection state
        updateSplitDropdownSelectAll();
    })

    // Must match constant in Group model
    const defaultGroupId = 1;

    // Must match constants in ExpenseType model
    const splitEqual = 1;
    const splitAmount = 2;
    const splitPercentage = 3;
    const splitShare = 4;
    const splitAdjustment = 5;
    const splitReimbursement = 6;
    const splitItemized = 7;

    function validateExpenseForm() {
        const expenseForm = document.getElementById('expense-form');
        const currentUserId = parseInt({{ $current_user->id }});

        // Validate expense name
        if (currentNameInput.value.trim() === '') {
            currentNameInput.focus();
            showValidationWarning(expenseNameValidationWarning);
            return;
        }

        // Validate expense amount
        const expenseAmount = parseFloat(currentAmountInput.value);
        if (isNaN(expenseAmount) || expenseAmount <= 0) {
            currentAmountInput.focus();
            showValidationWarning(expenseAmountValidationWarning);
            return;
        }

        // Validate expense payer
        if (currentPayerInput === null || paidDropdownList.children.length === 0) {
            showValidationWarning(payerValidationWarning);
            return;
        }

        // Ensure current user is involved in the expense if it is in the default group
        let currentUserInvolved = false;
        if (currentGroupInput.value == defaultGroupId && currentPayerInput.value == currentUserId) {
            currentUserInvolved = true;
        }

        // Validate the expense split details
        if (document.querySelector('.expense-split-tab-active').dataset.expenseTypeId == splitEqual) {
            const splitEqualParticipants = splitEqualList.querySelectorAll('.split-equal-item-checkbox:checked');
            if (splitEqualParticipants.length === 0) {
                showValidationWarning(participantValidationWarning);
                return;
            } else if (splitEqualParticipants.length === 1) {
                if (splitEqualList.querySelector('.split-equal-item-checkbox:checked').closest('.expand-dropdown-item').dataset.userId == currentPayerInput.value) {
                    showValidationWarning(participantValidationWarning);
                    return;
                }
            }

            if (currentGroupInput.value == defaultGroupId) {
                Array.from(splitEqualParticipants).forEach(participant => {
                    if (parseInt(participant.value) == currentUserId) {
                        currentUserInvolved = true;
                    }
                });
            }
        } else if (document.querySelector('.expense-split-tab-active').dataset.expenseTypeId == splitAmount) {
            let userAmountsSum = 0;

            Array.from(splitAmountList.children).forEach(child => {
                if (child.querySelector('.split-amount-item').dataset.userId == currentUserId && child.querySelector('input').value !== '') {
                    currentUserInvolved = true;
                }

                if (child.querySelector('input').value !== '') {
                    userAmountsSum += parseFloat(child.querySelector('input').value);
                }
            });

            if (userAmountsSum !== parseFloat(currentAmountInput.value)) {
                showValidationWarning(splitAmountSumValidationWarning);
                return;
            }
        } else if (document.querySelector('.expense-split-tab-active').dataset.expenseTypeId == splitReimbursement) {
            const splitReimbursementParticipants = splitReimbursementList.querySelectorAll('.split-reimbursement-item-checkbox:checked');
            if (splitReimbursementParticipants.length === 0) {
                showValidationWarning(participantValidationWarning);
                return;
            } else if (splitReimbursementParticipants.length === 1) {
                if (splitReimbursementList.querySelector('.split-reimbursement-item-checkbox:checked').closest('.expand-dropdown-item').dataset.userId == currentPayerInput.value) {
                    showValidationWarning(participantValidationWarning);
                    return;
                }
            }

            if (currentGroupInput.value == defaultGroupId) {
                Array.from(splitReimbursementParticipants).forEach(participant => {
                    if (parseInt(participant.value) == currentUserId) {
                        currentUserInvolved = true;
                    }
                });
            }
        }

        if (currentGroupInput.value == defaultGroupId && !currentUserInvolved) {
            showValidationWarning(currentUserValidationWarning);
            return;
        }

        expenseForm.submit();
    }
</script>
