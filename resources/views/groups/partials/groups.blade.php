<div class="groups">
    @foreach ($groups as $group)
        <a class="group" href="{{ route('groups.show', $group->id) }}">
            <div class="group-img"> 
                
            </div>

            <div class="group-details">
                @if ($group->is_default)
                    <div class="default-group-title">
                        <h4>{{ $group->name }}</h4>

                        <x-tooltip side="bottom" tooltip="{{ __('These expenses are not attached to any groups.') }}">
                            <span class="text-shy mobile-hidden"><i class="fa-solid fa-circle-info"></i></span>
                        </x-tooltip>
                    </div>
                @else
                    <h4>{{ $group->name }}</h4>
                @endif
                <div class="text-success">You are owed $210.96</div>
            </div>
        </a>
    @endforeach
</div>
