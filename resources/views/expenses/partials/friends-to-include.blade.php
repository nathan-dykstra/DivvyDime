<div class="friends-to-include" id="friends-to-include">
    @foreach ($users as $user)
        <div class="group-settings-member">
            @if (!in_array($user->id, $expense?->participants()->pluck('users.id')->toArray() ?? []))
                <div>
                    <div class="text-primary">{{ $user->username }}</div>
                    <div class="text-shy">{{ $user->email }}</div>
                </div>
                <div class="vertical-center">
                    <i class="fa-solid fa-user-plus add-friend-icon" onclick="addInvolvedFriend(event)"></i>
                </div>
            @else
                <div>
                    <div class="text-primary existing-member">{{ $user->username }}</div>
                    <div class="text-shy existing-member">{{ __('Already involved') }}</div>
                </div>
            @endif
        </div>
    @endforeach
</div>
