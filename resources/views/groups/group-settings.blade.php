<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ $group->name . __(' Settings') }}
    </x-slot>

    <x-slot name="back_btn"></x-slot>

    <x-slot name="header_title">
        {{ __('Group settings') }}
    </x-slot>

    <x-slot name="header_buttons">
        <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'leave-group')" icon="fa-solid fa-right-from-bracket icon">{{ __('Leave') }}</x-primary-button>
        @if (auth()->user()->id === $group->owner)
            <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-group')" icon="fa-solid fa-trash-can icon">{{ __('Delete') }}</x-primary-button>
        @endif
    </x-slot>

    <x-slot name="mobile_overflow_options">
        <div class="dropdown-item" x-data="" x-on:click.prevent="$dispatch('open-modal', 'leave-group')">
            <i class="fa-solid fa-right-from-bracket"></i>
            <div>{{ __('Leave') }}</div>
        </div>
        <div class="dropdown-item warning-hover" x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-group')">
            <i class="fa-solid fa-trash-can"></i>
            <div>{{ __('Delete') }}</div>
        </div>
    </x-slot>

    <!-- Session Status Messages -->

    @if (session('status') === 'invite-sent')
        <x-session-status>{{ __('Invite sent.') }}</x-session-status>
    @elseif (session('status') === 'invite-sent-with-errors')
        <x-session-status>{{ __('Invite sent. There were issues with some of the emails in your invite.') }}</x-session-status>
    @elseif (session('status') === 'invite-errors')
        <x-session-status innerClass="text-warning">{{ __('There were issues with all of the emails in your invite!') }}</x-session-status>
    @elseif (session('status') === 'member-removed')
        <x-session-status>{{ __('Member removed.') }}</x-session-status>
    @elseif (session('status') === 'member-deactivated')
        <x-session-status>{{ __('Member deactivated.') }}</x-session-status>
    @elseif (session('status') === 'member-reactivated')
        <x-session-status>{{ __('Member reactivated.') }}</x-session-status>
    @elseif (session('status') === 'group-image-uploaded')
        <x-session-status>{{ __('Group image uploaded.') }}</x-session-status>
    @elseif (session('status') === 'group-image-deleted')
        <x-session-status>{{ __('Group image deleted.') }}</x-session-status>
    @endif

    <!-- Content -->

    @include('groups.partials.group-details')

    <div class="container">
        <div class="restrict-max-width">
            <section class="space-top-sm">
                <header>
                    <div class="btn-container-apart">
                        <div>
                            <h3>{{ __('Members') }}</h3>
                        </div>
                        <x-icon-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'send-group-invite')" icon="fa-solid fa-user-plus icon">{{ __('Invite') }}</x-icon-button>
                    </div>
                </header>

                @foreach ($group_members as $member)
                    <div class="group-settings-member" data-user-id="{{ $member->id }}">
                        <div class="dropdown-user-item-img-name">
                            <div class="profile-img-sm-container">
                                <img src="{{ $member->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img">
                            </div>
                            <div>
                                <div class="text-primary">{{ $member->username }}</div>
                                <div class="text-shy">{{ $member->email }}</div>
                            </div>
                        </div>

                        <div class="group-settings-member-options">
                            @if ($member->id === $group->owner)
                                <div class="info-chip info-chip-truncate info-chip-blue">{{ __('Admin') }}</div>
                            @endif

                            @if (!$member->pivot->is_active)
                                <div class="info-chip info-chip-truncate info-chip-yellow">{{ __('Inactive') }}</div>
                            @endif

                            @if (auth()->user()->id === $group->owner)
                                <x-dropdown>
                                    <x-slot name="trigger">
                                        <x-no-background-button class="mobile-header-btn" icon="fa-solid fa-ellipsis-vertical" />
                                    </x-slot>

                                    <x-slot name="content">
                                        @if (auth()->user()->id !== $member->id)
                                            <div class="dropdown-item warning-hover" x-data="" x-on:click.prevent="$dispatch('open-modal', 'remove-member')" data-user-id="{{ $member->id }}" data-username="{{ $member->username }}" onclick="configureRemoveMemberModal(this)">
                                                <i class="fa-solid fa-user-minus icon"></i>
                                                <div>{{ __('Remove') }}</div>
                                            </div>
                                        @else
                                            <div class="dropdown-item warning-hover" x-data="" x-on:click.prevent="$dispatch('open-modal', 'leave-group')">
                                                <i class="fa-solid fa-right-from-bracket icon"></i>
                                                <div>{{ __('Leave Group') }}</div>
                                            </div>
                                        @endif

                                        @if ($member->pivot->is_active)
                                            <div class="dropdown-item" x-data="" x-on:click.prevent="$dispatch('open-modal', 'deactivate-member')" data-user-id="{{ $member->id }}" data-username="{{ $member->username }}" onclick="configureDeactivateMemberModal(this)">
                                                <i class="fa-solid fa-user-lock icon"></i>
                                                <div>{{ __('Deactivate') }}</div>
                                            </div>
                                        @else
                                            <div class="dropdown-item" x-data="" x-on:click.prevent="$dispatch('open-modal', 'reactivate-member')" data-user-id="{{ $member->id }}" data-username="{{ $member->username }}" onclick="configureReactivateMemberModal(this)">
                                                <i class="fa-solid fa-user-check icon"></i>
                                                <div>{{ __('Reactivate') }}</div>
                                            </div>
                                        @endif
                                    </x-slot>
                                </x-dropdown>
                            @endif
                        </div>
                    </div>
                @endforeach
            </section>
        </div>
    </div>

    <!-- Modals -->

    <x-modal name="remove-member" id="remove-member-modal" focusable>
        <div class="space-bottom-sm">
            <div>
                <h3></h3>
                <p class="text-shy">
                    @if (true) <!-- TODO: handle logic for preventing removing user with outstanding balances -->
                        {{ __('Are you sure you want to remove this member from the group? Any group expenses they are involved in will be updated to show a "DivvyDime User". This action cannot be undone.') }}
                    @else
                        {{ __('This user must settle all their balances in this group before they can be removed.') }}
                    @endif
                </p>
            </div>

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                @if  (true) <!-- TODO: hide button if user's group balances not settled -->
                    <x-danger-button id="remove-member-btn" onclick="">{{ __('Remove') }}</x-danger-button>
                @endif
            </div>
        </div>
    </x-modal>

    <x-modal name="deactivate-member" id="deactivate-member-modal" focusable>
        <div class="space-bottom-sm">
            <div>
                <h3></h3>
                <p class="text-shy">
                    {{ __('Are you sure you want to deactivate this member? Inactive members will not be added to new expenses created in this group by default. However, they can still be added to group expenses manually.') }}
                </p>
            </div>

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                <x-primary-button class="primary-color-btn" id="deactivate-member-btn" onclick="">{{ __('Deactivate') }}</x-primary-button>
            </div>
        </div>
    </x-modal>

    <x-modal name="reactivate-member" id="reactivate-member-modal" focusable>
        <div class="space-bottom-sm">
            <div>
                <h3></h3>
                <p class="text-shy">
                    {{ __('Are you sure you want to reactivate this member? Active members will be added to new expenses created in this group by default.') }}
                </p>
            </div>

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                <x-primary-button class="primary-color-btn" id="reactivate-member-btn" onclick="">{{ __('Reactivate') }}</x-primary-button>
            </div>
        </div>
    </x-modal>

    <x-modal name="leave-group" focusable>
        <div class="space-bottom-sm">
            <div>
                <h3>{{ __('Leave group') }}</h3>
                <p class="text-shy">
                    @if (auth()->user()->id === $group->owner && count($group->members()->get()) > 1)
                        {{ __('Are you sure you want to leave this group? You are currently the group admin. If you leave, administrative privileges will be transferred to another member. This action cannot be undone.') }}
                    @elseif (auth()->user()->id === $group->owner)
                        {{ __('Are you sure you want to leave this group? You are the only member. If you leave, the group will be deleted, along with all group expenses. This action cannot be undone.') }}
                    @else
                        {{ __('Are you sure you want to leave this group?') }}
                    @endif
                </p>
            </div>

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                <x-primary-button class="primary-color-btn" onclick="leaveGroup()">{{ __('Leave') }}</x-primary-button>
            </div>
        </div>
    </x-modal>

    <x-modal name="delete-group" focusable>
        <div class="space-bottom-sm">
            <div>
                <h3>{{ __('Delete group') }}</h3>
                <p class="text-shy">
                    {{ __('Are you sure you want to delete this group? Any group expenses will be deleted with the group. This action cannot be undone.') }}
                </p>
            </div>

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                <x-danger-button onclick="deleteGroup()">{{ __('Delete') }}</x-danger-button>
            </div>
        </div>
    </x-modal>

    <x-modal name="send-group-invite" :show="$errors->isNotEmpty()" focusable>
        <div class="space-bottom-sm">
            <div>
                <h3>{{ __('Invite to group') }}</h3>
                <p class="text-shy">
                    {{ __('Existing users will receive a notification in the "Activity" section with your invite. New users will be sent an email inviting them to the app.') }}
                </p>
            </div>

            <div class="expense-involved-container">
                <div class="involved-chips-container" id="user-chips-container">
                    <input id="invite-users" class="expense-involved" type="search" placeholder="{{ __('Search friends or type email') }}" autofocus autocomplete="off" />
                </div>

                <div class="expense-involved-dropdown hidden" id="invite-users-dropdown"></div>
            </div>

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')" onclick="clearUserChips()">{{ __('Cancel') }}</x-secondary-button>
                <x-primary-button class="primary-color-btn" onclick="sendInvite()">{{ __('Send Invite') }}</x-primary-button>
            </div>
        </div>
    </x-modal>

    <!-- Templates -->

    <template id="invite-chip-template">
        <div class="involved-chip" data-user-email="">
            <div class="involved-chip-text"></div>
            <x-icon-button icon="fa-solid fa-xmark fa-sm" onclick="removeUserChip(this)" />
        </div>
    </template>

    <template id="dropdown-item-already-added-template">
        <div class="involved-dropdown-item" onmouseover="highlightDropdownItem(this)">
            <div class="dropdown-user-item-img-name">
                <div class="profile-img-sm-container">
                    <img src="" alt="User profile image" class="profile-img">
                </div>
                <div>
                    <div class="involved-dropdown-user-name"></div>
                    <div class="text-shy">{{ __('Already added') }}</div>
                </div>
            </div>
            <i class="fa-solid fa-user-check friend-added-icon"></i>
        </div>
    </template>

    <template id="dropdown-item-not-added-template">
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

    <template id="dropdown-item-not-friend">
        <div class="involved-dropdown-item" onmouseover="highlightDropdownItem(this)">
            <div class="dropdown-user-item-img-name">
                <div class="profile-img-sm-container">
                    <img src="" alt="User profile image" class="profile-img">
                </div>
                <div>
                    <div class="involved-dropdown-user-name"></div>
                    <div class="text-shy">{{ __('Add email') }}</div>
                </div>
            </div>
            <i class="fa-solid fa-user-plus add-friend-icon"></i>
        </div>
    </template>

    <template id="dropdown-divider-template">
        <div class="involved-dropdown-divider"></div>
    </template>
</x-app-layout>

<script>
    const inviteUsersInput = document.getElementById('invite-users');
    const inviteChipsContainer = document.getElementById('user-chips-container');
    const inviteDropdown = document.getElementById('invite-users-dropdown');

    let selectedDropdownItemIndex = 0;

    inviteUsersInput.addEventListener('input', function(event) {
        const searchString = event.target.value;

        // Remove backspace highlight on last user chip (if it exists)
        if (inviteChipsContainer.children.length >= 2 && searchString !== '') {
            const lastChip = inviteChipsContainer.children[inviteChipsContainer.children.length - 2];
            lastChip.classList.remove('involved-chip-selected');
        }

        $.ajax({
            url: "{{ route('groups.search-friends-to-invite') }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'search_string': searchString,
            },
            success: function(response) {
                if (searchString === '') {
                    inviteDropdown.classList.add('hidden');
                } else {
                    displaySearchResults(response);
                }
            },
            error: function(error) {
                console.error(error);
            }
        });
    });

    inviteUsersInput.addEventListener('blur', function() {
        // Remove backspace highlight on last user chip (if it exists)
        if (inviteChipsContainer.children.length >= 2) {
            const lastChip = inviteChipsContainer.children[inviteChipsContainer.children.length - 2];
            lastChip.classList.remove('involved-chip-selected');
        }
    });

    function displaySearchResults(response) {
        // Clear the dropdown from any previous results
        inviteDropdown.innerHTML = '';

        if (response.friends.length) {
            // Get an array of user IDs that are already involved
            const inviteChipUsersAlreadyAdded = Array.from(inviteChipsContainer.children).map(child => parseInt(child.dataset.userId));
            const membersAlreadyAdded = Array.from(document.querySelectorAll('.group-settings-member')).map(member => parseInt(member.dataset.userId));
            const usersAlreadyAdded = inviteChipUsersAlreadyAdded.concat(membersAlreadyAdded);

            response.friends.forEach(user => {
                let dropdownItem;

                if (usersAlreadyAdded.includes(parseInt(user.id))) { // This user has already been added
                    let dropdownItemTemplate = document.getElementById('dropdown-item-already-added-template');
                    dropdownItem = dropdownItemTemplate.content.cloneNode(true);

                    dropdownItem.querySelector('.profile-img').src = user.profile_image_url;
                    dropdownItem.querySelector('.involved-dropdown-user-name').textContent = user.username;

                    dropdownItem.querySelector('.involved-dropdown-item').addEventListener('click', () => {
                        inviteDropdown.classList.add('hidden');
                        inviteUsersInput.value = '';
                        inviteUsersInput.focus();
                    });
                } else { // This user has not yet been added
                    let dropdownItemTemplate = document.getElementById('dropdown-item-not-added-template');
                    dropdownItem = dropdownItemTemplate.content.cloneNode(true);

                    dropdownItem.querySelector('.profile-img').src = user.profile_image_url;
                    dropdownItem.querySelector('.involved-dropdown-user-name').textContent = user.username;
                    dropdownItem.querySelector('.involved-dropdown-user-email').textContent = user.email;

                    dropdownItem.querySelector('.involved-dropdown-item').addEventListener('click', () => {
                        addUserChip(user);
                    });
                }

                // Add the item to the involved users search results dropdown
                inviteDropdown.appendChild(dropdownItem);
            });
        }

        const inputEmail = String(inviteUsersInput.value).trim().toLowerCase();
        const isValidEmail = validateEmail(inputEmail);

        if (isValidEmail) {
            if (response.friends.length) {
                // Add a divider between the search results and the "Add email" option
                let dropdownDividerTemplate = document.getElementById('dropdown-divider-template');
                dropdownDivider = dropdownDividerTemplate.content.cloneNode(true);
                inviteDropdown.appendChild(dropdownDivider);
            }

            // Add the "Add email" option for valid emails

            let dropdownItemTemplate = document.getElementById('dropdown-item-not-friend');
            let dropdownItem = dropdownItemTemplate.content.cloneNode(true);

            dropdownItem.querySelector('.involved-dropdown-user-name').textContent = inputEmail;
            dropdownItem.querySelector('.involved-dropdown-item').addEventListener('click', () => {
                addUserChip({ username: inputEmail, email: inputEmail });
            });

            inviteDropdown.appendChild(dropdownItem);
        }

        if (response.friends.length || isValidEmail) {
            // Highlight the first item and display the dropdown
            selectedDropdownItemIndex = 0;
            inviteDropdown.children[0].classList.add('involved-dropdown-item-selected');
            inviteDropdown.classList.remove('hidden');
        } else {
            inviteDropdown.classList.add('hidden');
        }
    }

    function validateEmail(email) {
        const emailRegex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return emailRegex.test(email);
    }

    function addUserChip(user) {
        let userChipTemplate = document.getElementById('invite-chip-template');
        let userChip = userChipTemplate.content.cloneNode(true);

        // Configure the chip content
        userChip.querySelector('.involved-chip-text').textContent = user.username;
        userChip.querySelector('.involved-chip').dataset.userEmail = user.email;

        // Add the chip
        const inviteSearchInput = inviteChipsContainer.querySelector('.expense-involved');
        inviteSearchInput.parentNode.insertBefore(userChip, inviteSearchInput);

        // Clear and hide search dropdown results
        inviteDropdown.classList.add('hidden');
        inviteUsersInput.value = '';
        inviteUsersInput.focus();
    }

    inviteChipsContainer.addEventListener('click', function() {
        inviteUsersInput.focus();
    });

    function removeUserChip(removeBtn) {
        userChip = removeBtn.closest('.involved-chip');
        userChip.parentNode.removeChild(userChip);

        inviteUsersInput.value = '';
        inviteUsersInput.focus();
    }

    document.addEventListener('click', function(event) {
        const clickedElement = event.target;

        if (!inviteDropdown.contains(clickedElement)) {
            // Hide dropdown and reset the highlighted dropdown item
            inviteDropdown.classList.add('hidden');
            selectedDropdownItemIndex = 0;
        }
    });

    function highlightDropdownItem(item) {
        // Highlight the specified involved users search dropdown item
        if (!item.classList.contains('involved-dropdown-item-selected')) {
            inviteDropdown.querySelector('.involved-dropdown-item-selected').classList.remove('involved-dropdown-item-selected');
            item.classList.add('involved-dropdown-item-selected');

            const itemIndex = Array.from(inviteDropdown.querySelectorAll(':scope > :not(.involved-dropdown-divider)')).indexOf(item);
            selectedDropdownItemIndex = itemIndex;
        }
    }

    inviteUsersInput.addEventListener('keydown', function(event) {
        const dropdownItemsCount = inviteDropdown.querySelectorAll(':scope > :not(.involved-dropdown-divider)').length;

        if ((event.key === 'Backspace' || event.keyCode === 8) && event.target.value === '' && inviteChipsContainer.children.length >= 2) { // Backspace
            // Highlight/delete the last User chip
            const lastChip = inviteChipsContainer.children[inviteChipsContainer.children.length - 2];
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
            const selectedDropdownItem = inviteDropdown.querySelector('.involved-dropdown-item-selected');
            selectedDropdownItem.click();
        } else if (event.key === 'ArrowUp' || event.keyCode === 38) { // Arrow Up
            event.preventDefault();

            // Update highlighted dropdown item
            inviteDropdown.querySelectorAll(':scope > :not(.involved-dropdown-divider)')[selectedDropdownItemIndex].classList.remove('involved-dropdown-item-selected');
            if (selectedDropdownItemIndex === 0) {
                selectedDropdownItemIndex = dropdownItemsCount - 1;
            } else {
                selectedDropdownItemIndex--;
            }
            inviteDropdown.querySelectorAll(':scope > :not(.involved-dropdown-divider)')[selectedDropdownItemIndex].classList.add('involved-dropdown-item-selected');
        } else if (event.key === 'ArrowDown' || event.keyCode === 40) { // Arrow Down
            event.preventDefault();

            // Update highlighted dropdown item
            inviteDropdown.querySelectorAll(':scope > :not(.involved-dropdown-divider)')[selectedDropdownItemIndex].classList.remove('involved-dropdown-item-selected');
            if (selectedDropdownItemIndex === dropdownItemsCount - 1) {
                selectedDropdownItemIndex = 0;
            } else {
                selectedDropdownItemIndex++;
            }
            inviteDropdown.querySelectorAll(':scope > :not(.involved-dropdown-divider)')[selectedDropdownItemIndex].classList.add('involved-dropdown-item-selected');
        } else if (event.key === 'Escape' || event.keyCode === 27) { // Escape
            // Hide the dropdown
            inviteDropdown.classList.add('hidden');
        }
    });

    function clearUserChips() {
        const userChips = inviteChipsContainer.querySelectorAll('.involved-chip');

        userChips.forEach(chip => {
            chip.remove();
        });
    }

    function configureRemoveMemberModal(removeMemberBtn) {
        const userId = removeMemberBtn.dataset.userId;
        const username = removeMemberBtn.dataset.username;

        const modal = document.getElementById('remove-member-modal');

        modal.querySelector('h3').textContent = '{{ __('Remove ') }}' + username;
        document.getElementById('remove-member-btn').setAttribute('onclick', 'removeMember(' + userId + ')');
    }

    function configureDeactivateMemberModal(deactivateMemberBtn) {
        const userId = deactivateMemberBtn.dataset.userId;
        const username = deactivateMemberBtn.dataset.username;

        const modal = document.getElementById('deactivate-member-modal');

        modal.querySelector('h3').textContent = '{{ __('Deactivate ') }}' + username;
        document.getElementById('deactivate-member-btn').setAttribute('onclick', 'deactivateMember(' + userId + ')');
    }

    function configureReactivateMemberModal(reactivateMemberBtn) {
        const userId = reactivateMemberBtn.dataset.userId;
        const username = reactivateMemberBtn.dataset.username;

        const modal = document.getElementById('reactivate-member-modal');

        modal.querySelector('h3').textContent = '{{ __('Reactivate ') }}' + username;
        document.getElementById('reactivate-member-btn').setAttribute('onclick', 'reactivateMember(' + userId + ')');
    }

    function sendInvite() {
        var emails = Array.from(inviteChipsContainer.querySelectorAll('.involved-chip')).map(child => String(child.dataset.userEmail));

        if (emails.length === 0) {
            return;
        }

        $.ajax({
            url: "{{ route('groups.invite', $group) }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'emails': emails,
            },
            success: function(response) {
                window.location.href = response.redirect;
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    function removeMember(memberId) {
        $.ajax({
            url: "{{ route('groups.remove-member', $group) }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'member_id': memberId,
            },
            success: function(response) {
                window.location.href = response.redirect;
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    function deactivateMember(memberId) {
        $.ajax({
            url: "{{ route('groups.deactivate-member', $group) }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'member_id': memberId,
            },
            success: function(response) {
                window.location.href = response.redirect;
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    function reactivateMember(memberId) {
        $.ajax({
            url: "{{ route('groups.reactivate-member', $group) }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'member_id': memberId,
            },
            success: function(response) {
                window.location.href = response.redirect;
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    function leaveGroup() {
        $.ajax({
            url: "{{ route('groups.leave-group', $group) }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            success: function(response) {
                window.location.href = response.redirect;
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    function deleteGroup() {
        $.ajax({
            url: "{{ route('groups.destroy', $group) }}",
            method: 'DELETE',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
            },
            success: function(response) {
                window.location.href = response.redirect;
            },
            error: function(error) {
                console.log(error);
            }
        });
    }
</script>
