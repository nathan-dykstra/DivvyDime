<ul id="payment-balances-list">
    @foreach ($users_selection as $user)
        @if ($user->total_balance < 0)
            <li>
                <label class="payment-group-selector-item" for="choose-balance-item-all-{{ $user->id }}" data-user-id="{{ $user->id }}" data-group-name="{{ __('All Balances') }}" data-balance="{{ $user->total_balance }}" onclick="setPaymentBalance(this)">
                    <div class="payment-user-selector-radio">
                        <input type="radio" id="choose-balance-item-all-{{ $user->id }}" class="radio" name="payment-balance" value="-1" {{ $payment?->is_settle_all_balances }}/>
                        <div class="user-photo-name">
                            <div class="profile-circle-sm-placeholder"></div>
                            <div class="split-equal-item-name">{{ __('All Balances') }}</div>
                        </div>
                    </div>

                    <div class="payment-user-amount">
                        <div class="text-small text-warning">{{ __('You owe $') . number_format(abs($user->total_balance), 2) }}</div>
                    </div>
                </label>
            </li>
        @endif
    @endforeach

    @foreach ($balances_selection as $balance)
        <li>
            <label class="payment-group-selector-item" for="choose-balance-item-{{ $balance->id }}" data-user-id="{{ $balance->friend }}" data-group-name="{{ $balance->group_name }}" data-balance="{{ $balance->balance }}" onclick="setPaymentBalance(this)">
                <div class="payment-user-selector-radio">
                    <input type="radio" id="choose-balance-item-{{ $balance->id }}" class="radio" name="payment-balance" value="{{ $balance->id }}" {{ (!$payment?->is_settle_all_balances && $payment?->groups->first()->id === $balance->group_id) || $group?->id === $balance->group_id ? 'checked' : '' }}/>
                    <div class="user-photo-name">
                        <div class="profile-circle-sm-placeholder"></div>
                        <div class="split-equal-item-name">{{ $balance->group_name }}</div>
                    </div>
                </div>

                @if ($balance->balance < 0) <!-- Current user owes money in this group -->
                    <div class="payment-user-amount">
                        <div class="text-small text-warning">{{ __('You owe $') . number_format(abs($balance->balance), 2) }}</div>
                    </div>
                @elseif ($balance->balance > 0) <!-- Current user is owed money in this group -->
                    <div class="text-small text-success">{{ __('You are owed $') . number_format(abs($balance->balance), 2) }}</div>
                @else
                    <div class="text-shy">{{ __('Settled up') }}</div>
                @endif
            </label>
        </li>
    @endforeach
</ul>
