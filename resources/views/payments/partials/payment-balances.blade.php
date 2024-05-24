<ul id="payment-balances-list">
    @if ($total_balance < 0)
        <li>
            <label class="item-list-selector" for="choose-balance-item-all" data-group-name="{{ __('Settle All Balances') }}" data-balance="{{ $total_balance }}" onclick="setPaymentBalance(this, true)">
                <div class="item-list-selector-radio">
                    <input type="radio" id="choose-balance-item-all" class="radio" name="payment-balance" value="-1" {{ $payment?->is_settle_all_balances ? 'checked' : '' }}/>
                    <div class="dropdown-user-item-img-name">
                        <div class="profile-circle-sm-placeholder"></div>
                        <div class="dropdown-user-item-name">{{ __('Settle All Balances') }}</div>
                    </div>
                </div>

                <div class="payment-user-amount">
                    <div class="text-small text-warning">{{ __('You owe $') . number_format(abs($total_balance), 2) }}</div>
                </div>
            </label>
        </li>
    @endif

    @foreach ($balances_selection as $balance)
        <li>
            <label class="item-list-selector" for="choose-balance-item-{{ $balance->id }}" data-group-name="{{ $balance->group_name }}" data-balance="{{ $balance->display_balance }}" onclick="setPaymentBalance(this)">
                <div class="item-list-selector-radio">
                    <input type="radio" id="choose-balance-item-{{ $balance->id }}" class="radio" name="payment-balance" value="{{ $balance->id }}" {{ (!$payment?->is_settle_all_balances && $payment?->groups->first()->id === $balance->group_id) || $group?->id === $balance->group_id ? 'checked' : '' }}/>
                    <div class="dropdown-user-item-img-name">
                        <div class="profile-circle-sm-placeholder"></div>
                        <div class="dropdown-user-item-name">{{ $balance->group_name }}</div>
                    </div>
                </div>

                @if ($balance->display_balance < 0) <!-- Current user owes money in this group -->
                    <div class="payment-user-amount">
                        <div class="text-small text-warning">{{ __('You owe $') . number_format(abs($balance->display_balance), 2) }}</div>
                    </div>
                @elseif ($balance->display_balance > 0) <!-- Current user is owed money in this group -->
                    <div class="text-small text-success">{{ __('You are owed $') . number_format(abs($balance->display_balance), 2) }}</div>
                @else
                    <div class="text-shy">{{ __('Settled up') }}</div>
                @endif
            </label>
        </li>
    @endforeach
</ul>
