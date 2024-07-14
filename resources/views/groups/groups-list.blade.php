<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ __('Groups') }}
    </x-slot>

    <x-slot name="header_title">
        {{ __('Groups') }}
    </x-slot>

    <x-slot name="header_buttons">
        <x-primary-button icon="fa-solid fa-plus icon" :href="route('groups.create')">{{ __('New Group') }}</x-primary-button>
    </x-slot>

    <x-slot name="mobile_overflow_options">
        <a class="dropdown-item" href="{{ route('groups.create') }}">
            <i class="fa-solid fa-plus"></i>
            <div>{{ __('New Group') }}</div>
        </a>
    </x-slot>

    <!-- Session Status Messages -->

    @if (session('status') === 'left-group')
        <x-session-status>{{ __('Left group.') }}</x-session-status>
    @elseif (session('status') === 'group-deleted')
        <x-session-status>{{ __('Group deleted.') }}</x-session-status>
    @endif

    <!-- Content -->

    <div class="section-search">
        <div class="restrict-max-width">
            <x-searchbar-secondary placeholder="{{ __('Search Groups') }}" id="search-groups"></x-searchbar-secondary>
        </div>
    </div>

    <div class="expenses-list-container">
        <!-- No groups message -->
        <div class="notifications-empty-container hidden" id="no-groups">
            <div class="notifications-empty-icon"><i class="fa-solid fa-user-group"></i></div>
            <div class="notifications-empty-text">{{ __('No groups!') }}</div>
        </div>

        <div class="expenses" id="groups-list"></div>

        <!-- Loading animation -->
        <div id="groups-loading">
            <x-list-loading></x-list-loading>
        </div>
    </div>

    <!-- Modals -->

    <x-modal name="default-group-info" :show="false">
        <div>
            <h3>{{ __('About Individual Expenses') }}</h3>
            <p class="text-shy">
                {{ __('You can choose whether or not your expenses are part of a group. Expenses in a group can be seen and modified by anyone in the group, while non-group expenses can only be seen and modified by those involved.') }}
            </p>
            <p class="text-shy">
                {{ __('All of your non-group expenses show up here.') }}
            </p>
        </div>
    </x-modal>
</x-app-layout>

<script>
    let page = 1;
    let loading = false;
    let lastPage = false;
    let query = '';

    function fetchGroups(query, replace = false) {
        const loadingPlaceholder = document.getElementById('groups-loading');
        const groupsList = document.getElementById('groups-list');
        const noGroupsMessage = document.getElementById('no-groups');

        loading = true;

        if (replace) {
            groupsList.innerHTML = '';
            lastPage = false;
            page = 1;
        }

        if (lastPage) {
            loading = false;
            return;
        }

        noGroupsMessage.classList.add('hidden');
        loadingPlaceholder.classList.remove('hidden');

        $.ajax({
            url: '{{ route('groups.get-groups') }}' + '?page=' + page,
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
                            groupsList.innerHTML = '';
                            noGroupsMessage.classList.remove('hidden');
                        } else {
                            noGroupsMessage.classList.add('hidden');
                            groupsList.innerHTML = html;
                        }
                    } else { // Append to the content on scroll
                        groupsList.insertAdjacentHTML('beforeend', html); 
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
        fetchGroups(query, true);

        const searchInput = document.getElementById("search-groups");
        searchInput.addEventListener('input', function() {
            query = searchInput.value.trim();
            fetchGroups(query, true);
        });

        function handleScroll() {
            if (loading) return;

            if (window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 100) {
                fetchGroups(query);
            }
        }
        document.addEventListener('scroll', handleScroll);
    });
</script>
