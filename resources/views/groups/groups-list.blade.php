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
            <x-searchbar-secondary placeholder="Search Groups" id="search-groups"/>
        </div>
    </div>

    <div class="groups-list-container">
        @include('groups.partials.groups')
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
    groupsSearchbar = document.getElementById("search-groups");
    groupsSearchbar.addEventListener('input', function(event) {
        var searchString = event.target.value;

        $.ajax({
            url: "{{ route('groups.search') }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'search_string': searchString,
            },
            success: function(html) {
                groups = $('.groups');
                groups.replaceWith(html);
            },
            error: function(error) {
                console.log(error);
            }
        });
    });
</script>
