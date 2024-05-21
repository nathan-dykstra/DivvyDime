<div class="container margin-bottom-lg">
    <div class="restrict-max-width">
        <form method="post" action="{{ $expense ? route('expenses.update', $expense) : route('expenses.store') }}" class="space-bottom-lg">
            @csrf
            @if ($expense)
                @method('patch')
            @endif

            <div class="expense-involved-container">
                <div class="involved-chips-container" id="involved-chips-container">
                    @if ($expense === null) <!-- Creating a new Expense -->
                        @if ($group) <!-- Expense was added from a Group, so show the Group members by default -->
                            @foreach ($group->group_members as $member)
                                <div class="involved-chip" data-user-id="{{ $member->id }}" data-username="{{ $member->username }}">
                                    <span>{{ $member->username }}</span>
                                    <x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />
                                </div>
                            @endforeach
                        @else <!-- Expense was not added from a Group (or it was added from "Individual Expenses") -->
                            <div class="involved-chip involved-chip-fixed" data-user-id="{{ auth()->user()->id }}" data-username="{{ auth()->user()->username }}">
                                <span>{{ auth()->user()->username }}</span>
                            </div>

                            @if ($friend) <!-- Expense was added from a Friend -->
                                <div class="involved-chip" data-user-id="{{ $friend->id }}" data-username="{{ $friend->username }}">
                                    <span>{{ $friend->username }}</span>
                                    <x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />
                                </div>
                            @endif
                        @endif
                    @else <!-- Updating an existing Expense -->
                        @foreach($expense->involvedUsers() as $involved_user)
                            <div class="involved-chip {{ $involved_user->id === auth()->user()->id && $expense->groups->first()->id === $default_group->id ? 'involved-chip-fixed' : '' }}" data-user-id="{{ $involved_user->id }}" data-username="{{ $involved_user->username }}">
                                <span>{{ $involved_user->username }}</span>
                                @if (!($involved_user->id === auth()->user()->id && $expense->groups->first()->id === $default_group->id))
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
                <x-tooltip side="bottom" icon="fa-solid fa-tag" :tooltip="__('Choose a category')">
                    <div class="expense-category">
                        <!-- TODO: Expense Category selector -->
                    </div>
                </x-tooltip>
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

                    <div class="expense-expand-dropdown" id="expense-paid-dropdown">
                        <h4 class="margin-bottom-sm">{{ __('Who paid for this expense?') }}</h4>

                        <div class="paid-dropdown-empty-warning hidden">
                            {{ __('You must add users to the expense before choosing who paid.') }}
                        </div>

                        <ul class="expense-paid-dropdown-list" id="expense-paid-dropdown-list">
                            @if ($expense === null) <!-- Creating a new Expense -->
                                @if ($group) <!-- Expense was added from a Group, so show the Group members by default -->
                                    @foreach ($group->group_members as $member)
                                        <li>
                                            <label class="split-equal-item" for="paid-dropdown-item-{{ $member->id }}" data-user-id="{{ $member->id }}" data-username="{{ $member->username }}" onclick="setExpensePayer(this)">
                                                <input type="radio" id="paid-dropdown-item-{{ $member->id }}" class="radio" name="expense-paid" value="{{ $member->id }}" {{ $member->id === auth()->user()->id ? 'checked' : '' }} />
                                                <div class="user-photo-name">
                                                    <div class="profile-circle-sm-placeholder"></div> <!-- TODO: profile image -->
                                                    <div class="split-equal-item-name">{{ $member->username }}</div>
                                                </div>
                                            </label>
                                        </li>
                                    @endforeach
                                @else <!-- Expense was not added from a Group (or it was added from "Individual Expenses") -->
                                    <li>
                                        <label class="split-equal-item" for="paid-dropdown-item-{{ auth()->user()->id }}" data-user-id="{{ auth()->user()->id }}" data-username="{{ auth()->user()->username }}" onclick="setExpensePayer(this)">
                                            <input type="radio" id="paid-dropdown-item-{{ auth()->user()->id }}" class="radio" name="expense-paid" value="{{ auth()->user()->id }}" checked/>
                                            <div class="user-photo-name">
                                                <div class="profile-circle-sm-placeholder"></div> <!-- TODO: profile image -->
                                                <div class="split-equal-item-name">{{ auth()->user()->username }}</div>
                                            </div>
                                        </label>
                                    </li>

                                    @if ($friend) <!-- Expense was added from a Friend -->
                                        <li>
                                            <label class="split-equal-item" for="paid-dropdown-item-{{ $friend->id }}" data-user-id="{{ $friend->id }}" data-username="{{ $friend->username }}" onclick="setExpensePayer(this)">
                                                <input type="radio" id="paid-dropdown-item-{{ $friend->id }}" class="radio" name="expense-paid" value="{{ $friend->id }}" />
                                                <div class="user-photo-name">
                                                    <div class="profile-circle-sm-placeholder"></div> <!-- TODO: profile image -->
                                                    <div class="split-equal-item-name">{{ $friend->username }}</div>
                                                </div>
                                            </label>
                                        </li>
                                    @endif
                                @endif
                            @else <!-- Updating an existing Expense -->
                                @foreach ($expense->involvedUsers() as $involved_user)
                                    <li>
                                        <label class="split-equal-item" for="paid-dropdown-item-{{ $involved_user->id }}" data-user-id="{{ $involved_user->id }}" data-username="{{ $involved_user->username }}" onclick="setExpensePayer(this)">
                                            <input type="radio" id="paid-dropdown-item-{{ $involved_user->id }}" class="radio" name="expense-paid" value="{{ $involved_user->id }}" {{ $expense?->payer === $involved_user->id ? 'checked' : '' }}/>
                                            <div class="user-photo-name">
                                                <div class="profile-circle-sm-placeholder"></div>
                                                <div class="split-equal-item-name">{{ $involved_user->username }}</div>
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

                    <div class="expense-expand-dropdown" id="expense-split-dropdown">
                        <h4 class="margin-bottom-sm">{{ __('How do you want to divvy this up?') }}</h4>

                        <div class="expense-split-tabs-wrapper">
                            @include('expenses.partials.split-tabs.expense-tab-headers')

                            <x-blur-background-button class="expense-split-tabs-scroll-btn expense-split-tabs-left-btn" icon="fa-solid fa-chevron-left" onclick="splitTabsScrollLeft()" />
                            <x-blur-background-button class="expense-split-tabs-scroll-btn expense-split-tabs-right-btn" icon="fa-solid fa-chevron-right" onclick="splitTabsScrollRight()" />
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

                    <div class="expense-expand-dropdown" id="expense-group-dropdown">
                        <h4 class="margin-bottom-sm">{{ __('Choose a group') }}</h4>

                        <ul class="expense-paid-dropdown-list" id="expense-group-dropdown-list">
                            @foreach ($groups as $dropdown_group)
                                <li>
                                    <label class="split-equal-item" for="group-dropdown-item-{{ $dropdown_group->id }}" data-group-id="{{ $dropdown_group->id }}" data-group-name="{{ $dropdown_group->name }}" onclick="setExpenseGroup(this)">
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
                                        <div class="split-equal-item-name">{{ $dropdown_group->name }}</div>
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
                            <div id="flowbite-datepicker" inline-datepicker datepicker-buttons datepicker-format="yyyy-mm-dd" data-date="{{ $expense ? $expense->date : $today }}"></div>
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
        <li>
            <label class="split-equal-item" for="" data-user-id="" data-username="" onclick="setExpensePayer(this)">
                <input type="radio" id="" class="radio" name="expense-paid" value="" />
                <div class="user-photo-name">
                    <div class="profile-circle-sm-placeholder"></div>
                    <div class="split-equal-item-name"></div>
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
    const splitTabs = document.getElementById('expense-split-tabs');
    const splitTabsContent = document.getElementById('expense-split-tabs-content');
    const groupDropdownList = document.getElementById('expense-group-dropdown-list');
    const datePicker = document.getElementById('flowbite-datepicker');

    const splitEqualList = document.getElementById('split-equal-list');
    const splitAmountList = document.getElementById('split-amount-list');
    const splitReimbursementList = document.getElementById('split-reimbursement-list');

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
                'group_id': currentGroupInput.value,
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

    // TODO: This function must update the lists in all sections that use "involved users"
    // TODO: Change this function name
    // TODO: For the "split-x" lists, only add/remove users that were added/removed from the involved 
    //    section to preserve settings (i.e. don't start by emptying this list)
    function updatePaidDropdownList() {
        $(paidDropdownList).empty();
        $(splitEqualList).empty();
        $(splitAmountList).empty();
        $(splitReimbursementList).empty();

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

                let paidDropdownItemContent = $('#paid-dropdown-item-template').html();
                let paidDropdownItem = $(paidDropdownItemContent).clone();

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

                let splitEqualDropdownItemContent = $('#split-equal-dropdown-item-template').html();
                let splitEqualDropdownItem = $(splitEqualDropdownItemContent).clone();

                splitEqualDropdownItem.find('.split-equal-item').attr('for', 'split-equal-item-' + user.dataset.userId);
                splitEqualDropdownItem.find('.split-equal-item-checkbox').attr('id', 'split-equal-item-' + user.dataset.userId);
                splitEqualDropdownItem.find('.split-equal-item-checkbox').attr('value', user.dataset.userId);
                splitEqualDropdownItem.find('.split-equal-item-name').text(user.dataset.username);

                $(splitEqualList).append(splitEqualDropdownItem);

                // Create "Split Amount" dropdown list with split-amount-dropdown-item-template

                let splitAmountDropdownItemContent = $('#split-amount-dropdown-item-template').html();
                let splitAmountDropdownItem = $(splitAmountDropdownItemContent).clone();

                splitAmountDropdownItem.find('.split-amount-item').attr('for', 'split-amount-item-' + user.dataset.userId);
                splitAmountDropdownItem.find('.split-equal-item-name').text(user.dataset.username);
                splitAmountDropdownItem.find('.text-input-prepend').attr('id', 'split-amount-item-' + user.dataset.userId);
                splitAmountDropdownItem.find('.text-input-prepend').attr('name', 'split-amount-item-' + user.dataset.userId);

                $(splitAmountList).append(splitAmountDropdownItem);

                // Create "Split Amount" dropdown list with split-reimbursement-dropdown-item-template

                let splitReimbursementDropdownItemContent = $('#split-reimbursement-dropdown-item-template').html();
                let splitReimbursementDropdownItem = $(splitReimbursementDropdownItemContent).clone();

                splitReimbursementDropdownItem.find('.split-equal-item').attr('for', 'split-reimbursement-item-' + user.dataset.userId);
                splitReimbursementDropdownItem.find('.split-reimbursement-item-checkbox').attr('id', 'split-reimbursement-item-' + user.dataset.userId);
                splitReimbursementDropdownItem.find('.split-reimbursement-item-checkbox').attr('value', user.dataset.userId);
                splitReimbursementDropdownItem.find('.split-equal-item-name').text(user.dataset.username);

                $(splitReimbursementList).append(splitReimbursementDropdownItem);
            });

            // Check if current payer was removed from the involved list
            if (!Array.from(usersInvolved).map(user => parseInt(user.dataset.userId)).includes(currentPayer)) {
                const firstPaidDropdownItem = paidDropdownList.firstElementChild;
                currentPayerInput.value = firstPaidDropdownItem.dataset.userId;
                $(paidBtn).children('.expense-round-btn-text').text($(firstPaidDropdownItem).find('.split-equal-item').attr('data-username'));
                $(firstPaidDropdownItem).find('.radio').attr('checked', 'checked');
            }
        }

        // Make sure the "select/deselect all" checkboxes match current selection state
        updateSplitDropdownSelectAll();
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

        // Resize the "Note" textarea to fit it's content
        resizeTextarea(currentNoteInput);

        // Update the "split-x" dropdown list "select/deselect all" checkboxes with the initial selection state
        updateSplitDropdownSelectAll();
    })

    // Update the amounts shown in the "split-x" dropdown lists when the expense amount input is changed
    function updateSplitDropdownAmounts() {
        splitEqualUpdatePriceBreakdown();
        splitAmountUpdateTotal();
        splitReimbursementUpdatePriceBreakdown();
    }

    function updateSplitDropdownSelectAll() {
        splitEqualUpdateSelectAll();
        splitReimbursementUpdateSelectAll();
    }
</script>
