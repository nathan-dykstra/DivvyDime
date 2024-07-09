<div class="friends">
    @foreach($friends as $friend)
        <a class="friend" href="{{ route('friends.show', $friend->id) }}">
            @if (count($friend->group_balances) > 1)
                <div class="expense-info-breakdown">
                    <div class="expense-info-breakdown-left">
                        <div class="profile-img-sm-container">
                            <img class="profile-img" src="{{ $friend->profile_image_url }}" alt="{{ __('Profile image for ') . $friend->username }}">
                        </div>

                        <div class="expense-info-breakdown-line-container">
                            <div class="expense-info-breakdown-line"></div>
                        </div>
                    </div>

                    <div class="expense-info-breakdown-right">
                        <div class="expense-info-breakdown-payer-container">
                            @include('friends.partials.friend-name')
                        </div>

                        <div class="space-top-xs">
                            @foreach ($friend->group_balances as $group_balance)
                                <div class="expense-info-participant text-shy">
                                    @if ($group_balance->balance > 0)
                                        {{ __('You are owed $') . number_format($group_balance->balance, 2) . __(' in ') . $group_balance->name }}
                                    @else
                                        {{ __('You owe $') . number_format(abs($group_balance->balance), 2) . __(' in ') . $group_balance->name }}
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="friend-name-container">
                    <div class="profile-img-sm-container">
                        <img class="profile-img" src="{{ $friend->profile_image_url }}" alt="{{ __('Profile image for ') . $friend->username }}">
                    </div>
                    @include('friends.partials.friend-name')
                </div>
            @endif
        </a>
    @endforeach
</div>
