<div class="friends">
    @foreach($friends as $friend)
        <a class="friend" href="{{ route('friends.show', $friend->id) }}">
            <div class="btn-container-apart">
                <div class="friend-name">{{ $friend->username }}</div>
                <div class="friend-amount"><!-- TODO: show total amount oweing/owed --></div>
            </div>
            <div><!-- TODO: show amounts oweing/owed in each group (if applicable) --></div>
        </a>
    @endforeach
</div>