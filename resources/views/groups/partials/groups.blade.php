<div class="groups">
    @foreach ($groups as $group)
        <a class="group" href="{{ route('groups.show', $group->id) }}">
            <div class="group-img"> 
                
            </div>

            <div class="group-details">
                <h4>{{ $group->name }}</h4>
                <div class="text-success">You are owed $210.96</div>
            </div>
        </a>
    @endforeach
</div>
