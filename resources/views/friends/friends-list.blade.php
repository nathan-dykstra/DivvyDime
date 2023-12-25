<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Friends') }}</h2>
            <div class="btn-container-end">
                <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'send-friend-invite')" icon="fa-solid fa-user-plus icon">{{ __('Add Friend') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'invite-sent')
        <x-session-status>{{ __('Friend request sent.') }}</x-session-status>
    @elseif (session('status') === 'self-request')
        <x-session-status innerClass="text-warning">{{ __('You can\'t send yourself a friend request!') }}</x-session-status>
    @elseif (session('status') === 'existing-friend')
        <x-session-status innerClass="text-warning">{{ __('You\'re already friends with that user!') }}</x-session-status>
    @elseif (session('status') === 'existing-request')
        <x-session-status innerClass="text-warning">{{ __('You already sent that user a friend request!') }}</x-session-status>
    @elseif (session('status') === 'pending-request')
        <x-session-status innerClass="text-warning">{{ __('You have a pending friend request from that user!') }}</x-session-status>
    @endif

    <div class="section-search">
        <div class="restrict-max-width">
            <x-searchbar-secondary placeholder="Search Friends" id="search-friends"></x-searchbar-secondary>
        </div>
    </div>

    <div class="friends-list-container">
        @include('friends.partials.friends')
    </div>

    <x-modal name="send-friend-invite" :show="$errors->friendInvite->isNotEmpty()" focusable>
        <form method="post" action="{{ route('friends.invite') }}" class="space-bottom-sm">
            @csrf

            <div>
                <h3>{{ __('Send a friend request') }}</h3>
                <p class="text-shy">
                    {{ __('If a user with this email address already exists, they\'ll recieve a notification in the "Activity" section with your friend request. Otherwise, we\'ll send an email inviting them to the app.') }}
                </p>
                <p class="text-shy">
                    {{ __('We won\'t share your email address until they accept your friend request.') }}
                </p>
            </div>

            <div>
                <x-input-label for="friend_email" value="{{ __('Email') }}" class="screen-reader-only" />
                <x-text-input id="friend_email" name="friend_email" type="email" placeholder="{{ __('Email') }}" required />
                <x-input-error :messages="$errors->friendInvite->get('friend_email')" />
            </div>

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                <x-primary-button type="submit" class="primary-color-btn">{{ __('Send Request') }}</x-danger-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>

<style>
</style>

<script>
    friendsSearchbar = document.getElementById("search-friends");
    friendsSearchbar.addEventListener('input', function(event) {
        var searchString = event.target.value;

        $.ajax({
            url: "{{ route('friends.search') }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'search_string': searchString,
            },
            success: function(html) {
                friends = $('.friends');
                friends.replaceWith(html);
            },
            error: function(error) {
                console.log(error);
            }
        });
    });
</script>