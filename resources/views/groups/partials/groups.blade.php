<div class="groups">
    @foreach ($groups as $group)
        <a class="group" href="{{ route('groups.show', $group->id) }}">
            <div class="group-img"></div>

            <div class="group-details">
                @if ($group->is_default)
                    <div class="default-group-title">
                        <h4>{{ $group->name }}</h4>

                        <x-tooltip side="bottom" tooltip="{{ __('These expenses are not attached to any groups') }}">
                            <span class="text-shy mobile-hidden"><i class="fa-solid fa-circle-info"></i></span>
                        </x-tooltip>
                    </div>
                @else
                    <h4>{{ $group->name }}</h4>
                @endif

                @if ($group->overall_balance > 0)
                    <span class="text-success">{{ __('You are owed $') . number_format($group->overall_balance, 2) }}</span>
                @elseif ($group->overall_balance < 0)
                    <span class="text-warning">{{ __('You owe $') . number_format(abs($group->overall_balance), 2) }}</span>
                @elseif ($group->is_settled_up)
                    <span class="text-success">{{ __('Your balances are settled') }}</span>
                @else
                    <span class="text-success">{{ __('You owe $0.00') }}</span>
                @endif
            </div>
        </a>
    @endforeach
</div>
