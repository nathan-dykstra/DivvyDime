<div class="container margin-bottom-lg">
    <div class="restrict-max-width">
        <form method="post" action="{{ $expense ? route('expenses.update', $expense) : route('expenses.store') }}" class="space-bottom-lg">
            @csrf
            @if ($expense)
                @method('patch')
            @endif

            <div class="expense-involved-container">
                <div class="involved-chips-container" id="involved-chips-container">
                    <div class="involved-chip involved-chip-fixed" data-user-id="{{ auth()->user()->id }}" data-username="{{ auth()->user()->username }}">
                        <span>{{ auth()->user()->username }}</span>
                        <!--<x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />-->
                        <!-- TODO: Allow removeal of current user when adding in a Group -->
                    </div>

                    @foreach($expense?->participants()->orderBy('username', 'ASC')->get() ?? [] as $participant)
                        <div class="involved-chip" data-user-id="{{ $participant->id }}" data-username="{{ $participant->username }}">
                            <span>{{ $participant->username }}</span>
                            <x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />
                        </div>
                    @endforeach

                    <input id="expense-involved" class="expense-involved" name="expense-involved" type="text" placeholder="{{ __('Who was involved?') }}" autofocus autocomplete="off" />
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
                        <input id="expense-name" class="expense-name" name="name" type="text" placeholder="{{ __('Describe the expense') }}" autocomplete="off" required />
                    </div>

                    <div class="expense-input-container">
                        <span class="expense-currency">{{ __('$') }}</span><input id="expense-amount" class="expense-amount" name="amount" type="number" step="0.01" min="0" max="99999999" placeholder="{{ __('0.00') }}" autocomplete="off" required />
                    </div>
                </div>
            </div>

            <div class="expense-paid-split-container">
                <div>
                    <div class="expense-paid-split">
                        {{ __('Who paid?') }}

                        <x-primary-button class="expense-round-btn" id="expense-paid-btn" onclick="togglePaidDropdown()">
                            <div class="expense-round-btn-text">
                                {{ auth()->user()->username }}
                            </div>
                        </x-primary-button>
                    </div>

                    <div class="expense-expand-dropdown" id="expense-paid-dropdown">
                        <h4 class="margin-bottom-sm">{{ __('Who paid for this expense?') }}</h4>

                        <div class="paid-dropdown-empty-warning hidden">
                            {{ __('You must add users to the expense before choosing who paid.') }}
                        </div>

                        <div class="expense-paid-dropdown-list" id="expense-paid-dropdown-list">
                            <div class="paid-dropdown-item" data-user-id="{{ auth()->user()->id }}" data-username="{{ auth()->user()->username }}" onclick="setExpensePayer(this)">
                                <div class="paid-dropdown-item-name">{{ auth()->user()->username }}</div>

                                <i class="fa-solid fa-check text-success"></i>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="expense-paid" name="expense-paid" value="{{ auth()->user()->id }}" />
                </div>

                <div>
                    <div class="expense-paid-split">
                        {{ __('How was it split?') }} <!-- TODO: this section -->

                        <x-primary-button class="expense-round-btn" id="expense-split-btn" onclick="toggleSplitDropdown()">
                            <div class="expense-round-btn-text">
                                {{ __('Equally') }}
                            </div>
                        </x-primary-button>
                    </div>

                    <div class="expense-expand-dropdown" id="expense-split-dropdown">
                        <h4>{{ __('How should we divvy this up?') }}</h4>

                    </div>
                </div>
            </div>

            <div class="expense-group-date-media-container">
                <div>
                    <div class="expense-group-date-media">
                        <x-primary-button class="expense-round-btn expense-round-btn-equal-width" id="expense-group-btn" onclick="toggleGroupDropdown()">
                            <div class="expense-round-btn-text">
                                {{ $expense?->group()->name ?? __('Individual Expenses') }}
                            </div>
                        </x-primary-button>
                    </div>
    
                    <div class="expense-expand-dropdown" id="expense-group-dropdown">
                        <h4 class="margin-bottom-sm">{{ __('Choose a group') }}</h4>
    
                        <div class="expense-paid-dropdown-list" id="expense-group-dropdown-list">
                            <div class="paid-dropdown-item" data-group-id="{{ $default_group->id }}" data-group-name="{{ $default_group->name }}" onclick="setExpenseGroup(this)">
                                <div class="paid-dropdown-item-name">{{ $default_group->name }}</div>
    
                                <i class="fa-solid fa-check text-success"></i>
                            </div>
    
                            @foreach ($groups as $group)
                                <div class="paid-dropdown-item" data-group-id="{{ $group->id }}" data-group-name="{{ $group->name }}" onclick="setExpenseGroup(this)">
                                    <div class="paid-dropdown-item-name">{{ $group->name }}</div>
    
                                    <i class="fa-solid fa-check text-success hidden"></i>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <input type="hidden" id="expense-group" name="expense-group" value="{{ $default_group_id }}" />
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

                        <!--<div class="expense-datepicker" id="expense-datepicker"></div>-->
                        <div class="expense-datepicker-container">
                            <div inline-datepicker datepicker-buttons datepicker-format="yyyy-mm-dd" data-date="{{ $today }}"></div>
                        </div>
                    </div>

                    <input type="hidden" id="expense-date" name="expense-date" value="{{ $today }}" />
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
                        <x-text-area id="expense-note" name="expense-note" maxlength="65535" />
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
        <div class="paid-dropdown-item" data-user-id="" data-username="" onclick="setExpensePayer(this)">
            <div class="paid-dropdown-item-name"></div>

            <i class="fa-solid fa-check text-success hidden"></i>
        </div>
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

        background-color: var(--background);
        border: 1px solid var(--border-grey);
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
        background-color: var(--primary-grey);
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
        font-size: 1.1em;
        font-weight: 600;
    }

    .expense-currency {
        padding: 4px 0 4px 8px;
        font-size: 1.1em;
        font-weight: 600;
    }

    .expense-amount {
        font-size: 1.6em;
        font-weight: 600;
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
        max-height: 500px !important;
        border-top: 1px solid var(--border-grey);
        border-bottom: 1px solid var(--border-grey);
        margin: 16px 0 0 0;
        padding: 16px 0;
        opacity: 100%;
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
        background-color: var(--accent-color);
        color: var(--text-primary-highlight);
    }

    .paid-dropdown-empty-warning {
        display: flex;
        justify-content: center;
        width: 100%;
        padding: 8px 0;
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
</style>

<!--<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>-->

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
    const groupDropdownList = document.getElementById('expense-group-dropdown-list');

    const currentPayerInput = document.getElementById('expense-paid');
    const currentGroupInput = document.getElementById('expense-group');
    const currentDateInput = document.getElementById('expense-date');

    const paidBtn = document.getElementById('expense-paid-btn');
    const splitBtn = document.getElementById('expense-split-btn');
    const groupBtn = document.getElementById('expense-group-btn');
    const dateBtn = document.getElementById('expense-date-btn');
    const mediaBtn = document.getElementById('expense-media-btn');

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
                lastChip.remove();
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

    /*jQuery.noConflict();

    jQuery(function($) {
        $( "#expense-datepicker" ).datepicker({
            onSelect: function(dateText) {
                // Set the hidden input value when a date is selected
                $(currentDateInput).val(dateText);
            }
        });
    });*/

    function updatePaidDropdownList() {
        $(paidDropdownList).empty();

        const usersInvolved = Array.from(involvedChipsContainer.children).slice(0, -1);

        const currentPayer = parseInt(currentPayerInput.value);

        if (usersInvolved.length === 0) { // No users in the involved list
            $(paidDropdown).find('.paid-dropdown-empty-warning').removeClass('hidden');
        } else {
            $(paidDropdownList).find('.paid-dropdown-empty-warning').addClass('hidden');

            usersInvolved.forEach(user => {
                var paidDropdownItemContent = $('#paid-dropdown-item-template').html();
                var paidDropdownItem = $(paidDropdownItemContent).clone();

                const paidDropdownName = paidDropdownItem.children('.paid-dropdown-item-name');
                $(paidDropdownName).text(user.dataset.username);

                paidDropdownItem.attr('data-user-id', user.dataset.userId);
                paidDropdownItem.attr('data-username', user.dataset.username);

                if (parseInt(user.dataset.userId) === currentPayer) {
                    paidDropdownItem.children('.fa-check').removeClass('hidden');
                }

                $(paidDropdownList).append(paidDropdownItem);
            });

            // Check if current payer was removed from the involved list
            if (!Array.from(usersInvolved).map(user => parseInt(user.dataset.userId)).includes(currentPayer)) {
                const firstPaidDropdownItem = paidDropdownList.firstElementChild;
                currentPayerInput.value = firstPaidDropdownItem.dataset.userId;
                $(paidBtn).children('.expense-round-btn-text').text(firstPaidDropdownItem.dataset.username);
                $(firstPaidDropdownItem).children('.fa-check').removeClass('hidden');
            }
        }
    }

    function setExpensePayer(payer) {
        newPayer = parseInt(payer.dataset.userId);
        currentPayerInput.value = newPayer;

        $(paidBtn).children('.expense-round-btn-text').text(payer.dataset.username);

        $(paidDropdownList).find('.fa-check').addClass('hidden');
        $(payer).children('.fa-check').removeClass('hidden');
    }

    function setExpenseGroup(group) {
        newGroup = parseInt(group.dataset.groupId);
        currentGroupInput.value = newGroup;

        $(groupBtn).children('.expense-round-btn-text').text(group.dataset.groupName);

        $(groupDropdownList).find('.fa-check').addClass('hidden');
        $(group).children('.fa-check').removeClass('hidden');
    }
</script>
