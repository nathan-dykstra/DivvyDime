<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Group settings') }}</h2>
            <div class="btn-container-end">
                <x-primary-button icon="fa-solid fa-right-from-bracket icon">{{ __('Leave') }}</x-primary-button>
                <x-danger-button icon="fa-solid fa-trash-can icon">{{ __('Delete') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

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
                        <div class="">
                            <x-primary-button icon="fa-solid fa-user-minus icon">{{ __('Remove') }}</x-primary-button>
                        </div>
                    @endif
                </div>
            @endforeach
        </section>
    </div>


    <x-modal name="send-group-invite" :show="$errors->groupInvite->isNotEmpty()" focusable>
        <form method="post" action="{{ route('groups.invite', $group->id) }}" class="space-bottom-sm">
            @csrf

            <div>
                <h3>{{ __('Invite to group') }}</h3>
                <p class="text-shy">
                    {{ __('If a user with this email address already exists, they\'ll recieve a notification in the "Activity" section with your invite. Otherwise, we\'ll send an email inviting them to the app.') }}
                </p>
            </div>

            <div>
                <x-input-label for="user-email" value="{{ __('Type an email address and press Enter') }}" class="screen-reader-only" />
                <x-text-input id="user-email" name="user-email" type="email" placeholder="{{ __('Type an email address and press Enter') }}" />
                <x-input-error :messages="$errors->userDeletion->get('user-email')" />
            </div>

            <div class="invited-emails-container" id="invited-emails-container"></div>

            <template id="invite-chip-template">
                <div class="invite-chip">
                    <div class="invite-chip-text">Test</div>
                    <x-icon-button icon="fa-solid fa-circle-xmark fa-sm" onclick="removeEmail(this)" />
                </div>
            </template>

            @if (auth()->user()->friends()->count() > 0)
                <h4>Your friends</h4>

                <div class="space-top-xs" id="invite-friends-container">
                    @foreach (auth()->user()->friends()->orderBy('username', 'asc')->get() as $friend)
                        @if (!in_array($friend->id, $group->members()->pluck('users.id')->toArray()))
                            <div class="group-settings-member">
                                <div>
                                    <div class="text-primary">{{ $friend->username }}</div>
                                    <div class="text-shy">{{ $friend->email }}</div>
                                </div>
                                <div class="vertical-center">
                                    <i class="fa-solid fa-user-plus add-friend-icon" onclick="addFriendEmail(event)" ></i>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                <x-primary-button type="submit" class="primary-color-btn">{{ __('Send Invite') }}</x-danger-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>

<script>
    const emailInput = document.getElementById('user-email');
    const inviteChipContainer = document.getElementById('invited-emails-container');

    emailInput.addEventListener('keydown', function(event) {
        if (event.keyCode === 13) { // Enter key
            event.preventDefault();

            if ($(emailInput).val() === '') {
                return;
            }

            var inputValue = $(emailInput).val().trim();

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
                    let emailValue = $(this).find('.text-shy').text().trim();
                
                    if (emailValue === inputValue) {
                        let icon = $(this).find('.fa-solid');
                        icon.removeClass('add-friend-icon fa-user-plus').addClass('friend-added-icon fa-user-check');
                    }
                });
            }

            $(emailInput).val(''); // Clear input
        }
    })

    function removeEmail(btn) {
        inviteChip = btn.closest('.invite-chip');
        inviteChipEmail = $(btn).prev('.invite-chip-text').text().trim();

        $('#invite-friends-container').children().each(function() {
            let emailValue = $(this).find('.text-shy').text().trim();

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

        btn = event.target;

        var emailText = $(btn).closest('div').prev().find('.text-shy').text().trim();

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

            $(btn).removeClass('fa-user-plus add-friend-icon');
            $(btn).addClass('fa-user-check friend-added-icon')
        }
    }
</script>
