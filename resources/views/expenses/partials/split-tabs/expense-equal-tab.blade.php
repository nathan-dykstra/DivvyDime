<div class="expense-split-equal-container">
    <div class="split-equal-select-all-container">
        <x-checkbox id="split-equal-select-all" onclick="splitEqualSelectAll(this)">{{ __('Select/deselect all') }}</x-checkbox> 

        <div class="split-equal-price-breakdown-container">
            <div>{{__('$') }}<span class="split-equal-price-breakdown">{{$expense && $expense->participants->count() !== 0 ? number_format(round($expense->amount / $expense->participants->count(), 2), 2) : '0.00' }}</span>{{ __('/person')}}</div>
            <div class="text-shy">(<span id="split-equal-participant-count">{{ $expense?->participants->count() ?? '1' }}</span><span id="split-equal-participant-count-label">{{ $expense?->participants->count() === 1 || $expense === null ? __(' person') : __(' people') }}</span>)</div>
        </div>
    </div>

    <div class="expense-dropdown-empty-warning hidden">
        {{ __('You must add users to the expense before you can divvy it up.') }}
    </div>

    <ul class="split-equal-list" id="split-equal-list">
        @if ($expense === null) <!-- Creating a new Expense -->
            @if ($group) <!-- Expense was added from a Group, so show the Group members by default -->
                @foreach ($group->group_members as $member)
                    <li>
                        <label class="expand-dropdown-item" for="split-equal-item-{{ $member->id }}" data-user-id="{{ $member->id }}" onclick="splitEqualUpdateSelectAll()">
                            <input type="checkbox" id="split-equal-item-{{ $member->id }}" class="checkbox split-equal-item-checkbox" name="split-equal-user[]" value="{{ $member->id }}" checked />
                            <div class="dropdown-user-item-img-name">
                                <div class="profile-img-sm-container">
                                    <img src="{{ $member->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img">
                                </div>
                                <div class="dropdown-user-item-name">{{ $member->username }}</div>
                            </div>
                        </label>
                    </li>
                @endforeach
            @else <!-- Expense was not added from a Group (or it was added from "Individual Expenses") -->
                <li>
                    <label class="expand-dropdown-item" for="split-equal-item-{{ $current_user->id }}" data-user-id="{{ $current_user->id }}" onclick="splitEqualUpdateSelectAll()">
                        <input type="checkbox" id="split-equal-item-{{ $current_user->id }}" class="checkbox split-equal-item-checkbox" name="split-equal-user[]" value="{{ $current_user->id }}" checked />
                        <div class="dropdown-user-item-img-name">
                            <div class="profile-img-sm-container">
                                <img src="{{ $current_user->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img">
                            </div>
                            <div class="dropdown-user-item-name">{{ $current_user->username }}</div>
                        </div>
                    </label>
                </li>

                @if ($friend) <!-- Expense was added from a Friend -->
                    <li>
                        <label class="expand-dropdown-item" for="split-equal-item-{{ $friend->id }}" data-user-id="{{ $friend->id }}" onclick="splitEqualUpdateSelectAll()">
                            <input type="checkbox" id="split-equal-item-{{ $friend->id }}" class="checkbox split-equal-item-checkbox" name="split-equal-user[]" value="{{ $friend->id }}" checked />
                            <div class="dropdown-user-item-img-name">
                                <div class="profile-img-sm-container">
                                    <img src="{{ $friend->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img">
                                </div>
                                <div class="dropdown-user-item-name">{{ $friend->username }}</div>
                            </div>
                        </label>
                    </li>
                @endif
            @endif
        @else <!-- Updating an existing Expense -->
            @foreach ($expense->involvedUsers() as $involved_user)
                <li>
                    <label class="expand-dropdown-item" for="split-equal-item-{{ $involved_user->id }}" data-user-id="{{ $involved_user->id }}" onclick="splitEqualUpdateSelectAll()">
                        <input type="checkbox" id="split-equal-item-{{ $involved_user->id }}" class="checkbox split-equal-item-checkbox" name="split-equal-user[]" value="{{ $involved_user->id }}" {{ $expense->participants->contains('id', $involved_user->id) ? 'checked' : '' }}/>
                        <div class="dropdown-user-item-img-name">
                            <div class="profile-img-sm-container">
                                <img src="{{ $involved_user->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img">
                            </div>
                            <div class="dropdown-user-item-name">{{ $involved_user->username }}</div>
                        </div>
                    </label>
                </li>
            @endforeach
        @endif
    </ul>
</div>

<template id="split-equal-dropdown-item-template">
    <li>
        <label class="expand-dropdown-item" for="" data-user-id="" onclick="splitEqualUpdateSelectAll()">
            <input type="checkbox" id="" class="checkbox split-equal-item-checkbox" name="split-equal-user[]" value="" checked/>
            <div class="dropdown-user-item-img-name">
                <div class="profile-img-sm-container">
                    <img src="" alt="User profile image" class="profile-img">
                </div>
                <div class="dropdown-user-item-name"></div>
            </div>
        </label>
    </li>
</template>
