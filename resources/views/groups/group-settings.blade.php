<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Group settings') }}</h2>
            <div class="btn-container-end">
                <x-primary-button icon="fa-solid fa-right-from-bracket icon">{{ __('Leave') }}</x-primary-button>
                @if (auth()->user()->id === $group->owner)
                    <x-danger-button icon="fa-solid fa-trash-can icon">{{ __('Delete') }}</x-primary-button>
                @endif
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'invite-sent')
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 4000)"
            class="session-status"
        >
            <p class="text-success">{{ __('Invite sent.') }}</p>
        </div>
    @elseif (session('status') === 'invite-sent-with-errors')
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 4000)"
            class="session-status"
        >
            <p class="text-success">{{ __('Invite sent. There were issues with some of the emails in your invite.') }}</p>
        </div>
    @elseif (session('status') === 'invite-errors')
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 4000)"
            class="session-status"
        >
            <p class="text-warning">{{ __('There were issues with all of the emails in your invite!') }}</p>
        </div>
    @endif

    @if (auth()->user()->id === $group->owner)
        @include('groups.partials.group-details')
    @endif
    
    <div class="container">
        <section class="space-top-sm">
            <header>
                <div class="btn-container-apart">
                    <div>
                        <h3>{{ __('Members') }}</h3>
                    </div>
                    <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'send-group-invite')" icon="fa-solid fa-user-plus icon">{{ __('Add Members') }}</x-primary-button>
                </div>
            </header>

            @foreach ($group->members()->orderBy('username', 'asc')->get() as $member)
                <div class="group-settings-member">
                    <div>
                        <div class="text-primary">{{ $member->username }}</div>
                        <div class="text-shy">{{ $member->email }}</div>
                    </div>
                    @if (auth()->user()->id === $group->owner && auth()->user()->id !== $member->id)
                        <div class="vertical-center">
                            <div class="tooltip tooltip-left">
                                <x-icon-button icon="fa-solid fa-user-minus icon" onclick="" />
                                <span class="tooltip-text" id="pin-sidebar-tooltip">{{ __('Remove ') . $member->username }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </section>
    </div>

    <x-modal name="send-group-invite" :show="$errors->isNotEmpty()" focusable>
        <div class="space-bottom-sm">

            <div>
                <h3>{{ __('Invite to group') }}</h3>
                <p class="text-shy">
                    {{ __('Existing users will receive a notification in the "Activity" section with your invite. New users will be sent an email inviting them to the app. Anyone who accepts your invite will automatically become friends with you on the DivvyDime.') }}
                </p>
            </div>

            <div>
                <x-input-label for="user-email" value="{{ __('Type an email address and press Enter') }}" class="screen-reader-only" />
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
                <x-primary-button class="primary-color-btn" onclick="sendInvite()">{{ __('Send Invite') }}</x-danger-button>
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

                $('#invite-friends-container').children().each(function() {
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

                $('#invite-friends-container').children().each(function() {
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

        $('#invite-friends-container').children().each(function() {
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
</script>
