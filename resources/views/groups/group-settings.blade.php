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
                    <div class="group-settings-member">
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
                                            <div class="dropdown-item" x-data="" x-on:click.prevent="$dispatch('open-modal', 'remove-member')" data-user-id="{{ $member->id }}" data-username="{{ $member->username }}" onclick="configureRemoveMemberModal(this)">
                                                <i class="fa-solid fa-user-minus icon"></i>
                                                <div>{{ __('Remove') }}</div>
                                            </div>
                                        @else
                                            <div class="dropdown-item" x-data="" x-on:click.prevent="$dispatch('open-modal', 'leave-group')">
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

            <div>
                <x-input-label for="user-email" value="{{ __('Search friends or type email and press Enter') }}" class="screen-reader-only" />
                <x-text-input id="user-email" name="user-email" type="email" placeholder="{{ __('Search friends or type email and press Enter') }}" />
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <div class="invited-emails-container" id="invited-emails-container"></div>

            <template id="invite-chip-template">
                <div class="invite-chip">
                    <div class="invite-chip-text"></div>
                    <x-icon-button icon="fa-solid fa-circle-xmark fa-sm" onclick="removeEmail(this)" />
                </div>
            </template>

            @if (auth()->user()->friends()->count() > 0)
                <h4>Your friends</h4>

                <div class="space-top-xs" id="invite-friends-container">
                    @include('groups.partials.friends-to-invite')
                </div>
            @endif

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                <x-primary-button class="primary-color-btn" onclick="sendInvite()">{{ __('Send Invite') }}</x-primary-button>
            </div>
        </div>
    </x-modal>
</x-app-layout>

<script>
    const emailInput = document.getElementById('user-email');
    const inviteChipContainer = document.getElementById('invited-emails-container');

    emailInput.addEventListener('input', function(event) {
        var searchString = event.target.value;
        var inviteChips = inviteChipContainer.querySelectorAll('.invite-chip .invite-chip-text');

        $.ajax({
            url: "{{ route('groups.search-friends-to-invite', $group) }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'search_string': searchString,
            },
            success: function(html) {
                friendsToInvite = $('.friends-to-invite');
                friendsToInvite.replaceWith(html);

                var emails = [];
                inviteChips.forEach(function(chip) {
                    emails.push(chip.textContent.trim());
                });

                $('#friends-to-invite').children().each(function() {
                    let friendEmail = $(this).find('.text-shy:not(.existing-member)').text().trim();
                
                    if (emails.includes(friendEmail)) {
                        let icon = $(this).find('.fa-solid');
                        icon.removeClass('add-friend-icon fa-user-plus').addClass('friend-added-icon fa-user-check');
                    }
                });
            },
            error: function(error) {
                console.log(error);
            }
        });
    });

    emailInput.addEventListener('keydown', function(event) {
        if (event.keyCode === 13) { // Enter key
            event.preventDefault();

            if ($(emailInput).val() === '') {
                return;
            }

            var inputValue = $(emailInput).val().trim().toLowerCase();

            if ($(inviteChipContainer).children().length === 0) {
                inviteChipContainer.style.display = 'flex';
            }

            var chipExists = false;

            $('#invited-emails-container').children().each(function() {
                if ($(this).find('.invite-chip-text').text().trim() === inputValue) {
                    chipExists = true;
                    return;
                }
            });

            if (!chipExists) {
                var inviteChipContent = $('#invite-chip-template').html();
                var inviteChip = $(inviteChipContent).clone();
                inviteChip.find('div').text(inputValue);
                $(inviteChipContainer).append(inviteChip);

                $('#friends-to-invite').children().each(function() {
                    let emailValue = $(this).find('.text-shy:not(.existing-member)').text().trim();
                
                    if (emailValue === inputValue) {
                        let icon = $(this).find('.fa-solid');
                        icon.removeClass('add-friend-icon fa-user-plus').addClass('friend-added-icon fa-user-check');
                    }
                });
            }

            // Clear and reset input field

            emailInput.value = ''; 

            let inputEvent = new Event('input', {
                bubbles: true,
                cancelable: true,
            });

            emailInput.dispatchEvent(inputEvent);
        }
    })

    function removeEmail(btn) {
        inviteChip = btn.closest('.invite-chip');
        inviteChipEmail = $(btn).prev('.invite-chip-text').text().trim();

        $('#friends-to-invite').children().each(function() {
            let emailValue = $(this).find('.text-shy:not(.existing-member)').text().trim();

            if (emailValue === inviteChipEmail) {
                let icon = $(this).find('.fa-solid');
                icon.removeClass('friend-added-icon fa-user-check').addClass('add-friend-icon fa-user-plus');
            }
        });

        $(inviteChip).remove();

        if ($(inviteChipContainer).children().length === 0) {
            inviteChipContainer.style.display = 'none';
        }
    }

    function addFriendEmail(event) {
        if ($(inviteChipContainer).children().length === 0) {
            inviteChipContainer.style.display = 'flex';
        }

        addFriendBtn = event.target;

        var emailText = $(addFriendBtn).closest('div').prev().find('.text-shy').text().trim();

        var chipExists = false;

        $('#invited-emails-container').children().each(function() {
            if ($(this).find('.invite-chip-text').text().trim() === emailText) {
                chipExists = true;
                return;
            }
        });

        if (!chipExists) {
            var inviteChipContent = $('#invite-chip-template').html();
            var inviteChip = $(inviteChipContent).clone();
            inviteChip.find('div').text(emailText);

            $(inviteChipContainer).append(inviteChip);

            $(addFriendBtn).removeClass('fa-user-plus add-friend-icon');
            $(addFriendBtn).addClass('fa-user-check friend-added-icon')
        }

        // Clear and reset input field

        emailInput.value = ''; 

        let inputEvent = new Event('input', {
            bubbles: true,
            cancelable: true,
        });

        emailInput.dispatchEvent(inputEvent);
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
        var inviteChips = inviteChipContainer.querySelectorAll('.invite-chip .invite-chip-text');

        var emails = [];
        inviteChips.forEach(function(chip) {
            emails.push(chip.textContent.trim());
        });

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
