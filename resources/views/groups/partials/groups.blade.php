<div class="groups">
    @foreach ($groups as $group)
        <a class="group" href="{{ route('groups.show', $group->id) }}">
            <div class="group-img-md-container">
                <img class="group-img-md" src="{{ $group->group_image_url }}" alt="{{ __('Group image for ') . $group->name }}">
            </div>

            <div class="group-details">
                @if ($group->is_default)
                    <div class="default-group-title">
                        <h4>{{ $group->name }}</h4>

                        <x-icon-button icon="fa-solid fa-circle-info" x-data="" x-on:click.prevent="$dispatch('open-modal', 'default-group-info')"/>
                    </div>
                @else
                    <h4>{{ $group->name }}</h4>
                @endif

                @if ($group->overall_balance > 0)
                    <span class="text-success">{{ __('You are owed $') . number_format($group->overall_balance, 2) }}</span>
                @elseif ($group->overall_balance < 0)
                    <span class="text-warning">{{ __('You owe $') . number_format(abs($group->overall_balance), 2) }}</span>
                @elseif ($group->is_settled_up)
                    <span class="text-success">{{ __('Settled Up!') }}</span>
                @else
                    <span class="text-success">{{ __('You owe $0.00') }}</span>
                @endif
            </div>
        </a>
    @endforeach
</div>
