<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Groups') }}</h2>
            <div class="btn-container-end">
                <x-primary-button icon="fa-solid fa-plus icon" :href="route('groups.create')">{{ __('New Group') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'left-group')
        <x-session-status>{{ __('Left group.') }}</x-session-status>
    @elseif (session('status') === 'group-deleted')
        <x-session-status>{{ __('Group deleted.') }}</x-session-status>
    @endif

    <div class="section-search">
        <div class="restrict-max-width">
            <x-searchbar-secondary placeholder="Search Groups" id="search-groups"/>
        </div>
    </div>

    <div class="groups-list-container">
        @include('groups.partials.groups')
    </div>

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
