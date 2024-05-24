<section class="space-top-sm">
    <header>
        <h3>{{ __('Group settings') }}</h3>
    </header>

    <ul>
        @foreach ($groups as $group)
            <li>
                <a class="item-list-selector" href="{{ route('groups.settings', $group->id) }}">
                    <div class="dropdown-user-item-img-name">
                        <div class="group-img-sm-container">
                            <img class="group-img-sm" src="{{ $group->group_image_url }}" alt="{{ __('Group image for ') . $group->name }}">
                        </div>
                        <div>{{ $group->name }}</div>
                    </div>
                </a>
            </li>
        @endforeach
    </ul>
</section>
