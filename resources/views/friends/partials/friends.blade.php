<div class="friends">
    @foreach($friends as $friend)
        <a class="friend" href="{{ route('friends.show', $friend->id) }}">
            <div class="btn-container-apart">
                <div class="friend-name">{{ $friend->username }}</div>
                <div class="friend-amount"></div>
            </div>
            <div></div>
        </a>
    @endforeach
</div>