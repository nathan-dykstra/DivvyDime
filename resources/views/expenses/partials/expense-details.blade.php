<div class="container margin-bottom-lg">
    <div class="restrict-max-width space-bottom-sm">
        <form method="post" action="{{ $expense ? route('expenses.update', $expense) : route('expenses.store') }}" class="space-top-sm">
            @csrf
            <!--method('patch')-->

            <div class="expense-involved-container">
                <div class="involved-chips-container" id="involved-chips-container">
                    <div class="involved-chip" data-user-id="{{ auth()->user()->id }}">
                        <span>{{ auth()->user()->username }}</span>
                        <x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />
                    </div>

                    @foreach($expense?->participants()->orderBy('username', 'ASC')->get() ?? [] as $participant)
                        <div class="involved-chip" data-user-id="{{ $participant->id }}">
                            <span>{{ $participant->username }}</span>
                            <x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />
                        </div>
                    @endforeach

                    <input id="expense-involved" class="expense-involved" name="expense-involved" type="text" placeholder="{{ __('Who was involved?') }}" autofocus autocomplete="off" />
                </div>

                <div class="expense-involved-dropdown hidden" id="expense-involved-dropdown"></div>
            </div>

            <div class="btn-container-start">
                <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
            </div>
        </form>
    </div>

    <!-- HTML Templates -->

    <template id="involved-chip-template">
        <div class="involved-chip" data-user-id="">
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
        transition: background-color 0.3s ease-in-out;

        color: var(--text-primary);
        height: 2em;
        border-radius: 1em;
        padding: 0 10px;
    }

    .involved-chip-selected {
        background-color: var(--primary-grey-hover);
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
</style>

<script>
    const involvedFriendsInput = document.getElementById('expense-involved');
    const involvedChipsContainer = document.getElementById('involved-chips-container');
    const involvedDropdown = document.getElementById('expense-involved-dropdown');

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
                        involvedDropdown.classList.add('hidden');
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

        const searchInput = $(involvedChipsContainer).children('.expense-involved');
        searchInput.before(userChip);

        involvedFriendsInput.value = '';
        involvedFriendsInput.focus();
    }

    involvedChipsContainer.addEventListener('click', function() {
        involvedFriendsInput.focus();
    });

    function removeUserChip(removeBtn) {
        userChip = removeBtn.closest('.involved-chip');
        $(userChip).remove();

        involvedFriendsInput.value = '';
        involvedFriendsInput.focus();
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
                lastChip.addClass('involved-chip-selected');
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
</script>
