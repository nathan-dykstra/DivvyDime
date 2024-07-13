@foreach ($groups as $group)
    <a class="group" href="{{ route('groups.show', $group->id) }}">
        <div class="friend-name-container">
            <div class="group-img-sm-container">
                <img class="group-img-sm" src="{{ $group->group_image_url }}" alt="{{ __('Group image for ') . $group->name }}">
            </div>

            <div class="friend-name-amount">
                @if ($group->is_default)
                    <div class="default-group-title">
                        <h4>{{ $group->name }}</h4>

                        <x-icon-button icon="fa-solid fa-circle-info" x-data="" x-on:click.prevent="$dispatch('open-modal', 'default-group-info')"/>
                    </div>
                @else
                    <h4>{{ $group->name }}</h4>
                @endif

                @if ($group->overall_balance > 0)
                    <div class="user-amount text-success">
                        <div class="text-small">{{ __('You are owed') }}</div>
                        <div class="user-amount-value">{{ __('$') . number_format($group->overall_balance, 2) }}</div>
                    </div>
                @elseif ($group->overall_balance < 0)
                    <div class="user-amount text-warning">
                        <div class="text-small">{{ __('You owe') }}</div>
                        <div class="user-amount-value">{{ __('$') . number_format(abs($group->overall_balance), 2) }}</div>
                    </div>
                @elseif ($group->is_settled_up)
                    <span class="text-shy">{{ __('Settled up') }}</span>
                @else
                    <div class="user-amount text-success">
                        <div class="text-small">{{ __('You owe ') }}</div>
                        <div class="user-amount-value">{{ __('$0.00') }}</div>
                    </div>
                @endif
            </div>
        </div>
    </a>
@endforeach
