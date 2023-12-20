<div class="friends-to-invite" id="friends-to-invite">
    @foreach ($friends as $friend)
        <div class="group-settings-member">
            @if (!in_array($friend->id, $group->members()->pluck('users.id')->toArray()))
                <div>
                    <div class="text-primary">{{ $friend->username }}</div>
                    <div class="text-shy">{{ $friend->email }}</div>
                </div>
                <div class="vertical-center">
                    <i class="fa-solid fa-user-plus add-friend-icon" onclick="addFriendEmail(event)"></i>
                </div>
            @else
                <div>
                    <div class="text-primary existing-member">{{ $friend->username }}</div>
                    <div class="text-shy existing-member">{{ __('Already in group') }}</div>
                </div>
            @endif
        </div>
    @endforeach
</div>
