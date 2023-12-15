<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Groups') }}</h2>
            <div class="btn-container-end">
                <x-primary-button icon="fa-solid fa-plus icon" :href="route('groups.create')">{{ __('New Group') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    <div class="section-search">
        <div class="restrict-max-width">
            <x-searchbar-secondary placeholder="Search Groups" id="search-groups"></x-searchbar-secondary>
        </div>
    </div>

    <div class="groups-list-container">
        @include('groups.partials.groups')
    </div>
</x-app-layout>

<style>

</style>

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