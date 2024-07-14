<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ __('Friends') }}
    </x-slot>

    <x-slot name="header_title">
        {{ __('Friends') }}
    </x-slot>

    <x-slot name="header_buttons">
        <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'send-friend-invite')" icon="fa-solid fa-user-plus icon">{{ __('Add Friend') }}</x-primary-button>
    </x-slot>

    <x-slot name="mobile_overflow_options">
        <div class="dropdown-item" x-data="" x-on:click.prevent="$dispatch('open-modal', 'send-friend-invite')">
            <i class="fa-solid fa-user-plus"></i>
            <div>{{ __('Add Friend') }}</div>
        </div>
    </x-slot>

    <!-- Session Status Messages -->

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

    <!-- Content -->

    <div class="section-search">
        <div class="restrict-max-width">
            <x-searchbar-secondary placeholder="{{ __('Search Friends') }}" id="search-friends"></x-searchbar-secondary>
        </div>
    </div>

    <div class="expenses-list-container">
        <!-- No groups message -->
        <div class="notifications-empty-container hidden" id="no-friends">
            <div class="notifications-empty-icon"><i class="fa-solid fa-user-slash"></i></div>
            <div class="notifications-empty-text">{{ __('No friends!') }}</div>
        </div>

        <div class="expenses" id="friends-list"></div>

        <!-- Loading animation -->
        <div id="friends-loading">
            <x-list-loading></x-list-loading>
        </div>
    </div>

    <!-- Modals -->

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

<script>
    let page = 1;
    let loading = false;
    let lastPage = false;
    let query = '';

    function fetchFriends(query, replace = false) {
        const loadingPlaceholder = document.getElementById('friends-loading');
        const friendsList = document.getElementById('friends-list');
        const noFriendsMessage = document.getElementById('no-friends');

        loading = true;

        if (replace) {
            friendsList.innerHTML = '';
            lastPage = false;
            page = 1;
        }

        if (lastPage) {
            loading = false;
            return;
        }

        noFriendsMessage.classList.add('hidden');
        loadingPlaceholder.classList.remove('hidden');

        $.ajax({
            url: '{{ route('friends.get-friends') }}' + '?page=' + page,
            method: 'GET',
            data: {
                'query': query
            },
            success: function(response) {
                if (response.is_last_page) lastPage = true;
                page = parseInt(response.current_page) + 1;

                const html = response.html;

                setTimeout(() => {
                    loadingPlaceholder.classList.add('hidden');

                    if (replace) { // Replace the content on search or page load
                        if (html.trim().length == 0) {
                            friendsList.innerHTML = '';
                            noFriendsMessage.classList.remove('hidden');
                        } else {
                            noFriendsMessage.classList.add('hidden');
                            friendsList.innerHTML = html;
                        }
                    } else { // Append to the content on scroll
                        friendsList.insertAdjacentHTML('beforeend', html); 
                    }
                }, replace ? 300 : 600);

                loading = false;
            },
            error: function(error) {
                loadingPlaceholder.classList.add('hidden');
                loading = false;
                console.error(error);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchFriends(query, true);

        const searchInput = document.getElementById("search-friends");
        searchInput.addEventListener('input', function() {
            query = searchInput.value.trim();
            fetchFriends(query, true);
        });

        function handleScroll() {
            if (loading) return;

            if (window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 100) {
                fetchFriends(query);
            }
        }
        document.addEventListener('scroll', handleScroll);
    });
</script>
