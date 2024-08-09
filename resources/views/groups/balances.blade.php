<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ $group->name . __(' Balances') }}
    </x-slot>

    <x-slot name="back_btn"></x-slot>

    <x-slot name="header_title">
        {{ $group->name . __(' Balances') }}
    </x-slot>

    <!-- Session Status Messages -->

    <!-- Content -->
    @php
        $accordion_group = 'balances-accordions';
    @endphp

    <div class="container">
        <div class="restrict-max-width space-top-sm">
            <div class="btn-container-end">
                <x-primary-button
                    class="expense-round-btn"
                    data-state="closed"
                    onclick="toggleAllAccordions(this, '{{ __('Expand All') }}', '{{ __('Collapse All') }}', '{{ $accordion_group }}')"
                >
                    {{ __('Expand All') }}
                </x-primary-button>
            </div>

            @foreach ($users as $user)
                <x-accordion group="{{ $accordion_group }}">
                    <x-slot name="toggle">
                        <div class="balance-summary">
                            <div class="balance-name-image">
                                <div class="profile-img-sm-container">
                                    <img class="profile-img" src="{{ $user->getProfileImageUrlAttribute() }}" alt="{{ __('Profile image for ') . $user->username }}"/>
                                </div>
                                <div>
                                    <span class="bold-username">{{ $user->username }}</span>
                                    @if ($user->balances_sum < 0)
                                        {{ __(' is owed ') }}
                                        <span class="{{ $user->id === $current_user->id ? 'text-success' : '' }} bold-username">{{ __('$') . number_format(abs($user->balances_sum), 2) }}</span>
                                        {{ __(' in total') }}
                                    @else
                                        {{ __(' owes ') }}
                                        <span class="{{ $user->id === $current_user->id ? 'text-warning' : '' }} bold-username">{{ __('$') . number_format(abs($user->balances_sum), 2) }}</span>
                                        {{ __(' in total') }}
                                    @endif
                                </div>
                            </div>
                            <x-icon-button icon="fa-solid fa-chevron-down"/>
                        </div>
                    </x-slot>

                    <x-slot name="content">
                        <div class="balance-breakdown space-top-xs">
                            @foreach ($user->balances as $balance)
                                <div class="expense-info-participant text-shy">
                                    @if ($balance->balance > 0)
                                        <span class="bold-username">{{ $balance->username }}</span>
                                        {{ __(' is owed ') }}
                                        <span class="{{ $balance->user_id === $current_user->id ? 'text-success' : ($user->id === $current_user->id ? 'text-warning' : '') }} bold-username">{{ __(' $') . number_format(abs($balance->balance), 2) }}</span>
                                        @if ($user->id === $current_user->id)
                                            <a class="info-chip info-chip-link info-chip-grey" href="{{ route('payments.create', ['group' => $group->id, 'friend' => $balance->user_id]) }}">{{ __('Settle Up') }}</a>
                                        @endif
                                    @else
                                        <span class="bold-username">{{ $balance->username }}</span>
                                        {{ __(' owes ') }}
                                        <span class="{{ $balance->user_id === $current_user->id && $balance->balance != 0 ? 'text-warning' : ($balance->user_id === $current_user->id && $balance->balance == 0 ? 'text-success' : ($user->id === $current_user->id && $balance->balance != 0 ? 'text-success' : '')) }} bold-username">{{ __(' $') . number_format(abs($balance->balance), 2) }}</span>
                                        @if ($balance->user_id === $current_user->id && $balance->balance != 0)
                                            <a class="info-chip info-chip-link info-chip-grey" href="{{ route('payments.create', ['group' => $group->id, 'friend' => $user->id]) }}">{{ __('Settle Up') }}</a>
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </x-slot>
                </x-accordion>
            @endforeach
        </div>
    </div>
</x-app-layout>

<style>
    .balance-summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 32px;
        padding: 8px 16px;
        border-radius: var(--border-radius-lg);
    }

    .balance-summary:hover {
        background-color: var(--secondary-grey-hover);
        cursor: pointer;
    }

    .balance-name-image {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .balance-breakdown {
        margin-left: 64px;
    }
</style>
