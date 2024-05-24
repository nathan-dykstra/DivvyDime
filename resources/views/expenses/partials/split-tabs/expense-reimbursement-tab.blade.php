<div class="expense-split-equal-container">
    <div class="margin-bottom-sm">
        <div class="info-container blue-background-text margin-top-sm">
            <div>
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div class="space-top-xs">
                <div>{{ __('About group reimbursements') }}</div>
                <div class="text-small">{{ __('Each user selected in this menu receives a reimbursement from the user selected in the "Who paid?" menu.') }}</div>
            </div>
        </div>
    </div>

    <div class="split-equal-select-all-container">
        <x-checkbox id="split-reimbursement-select-all" onclick="splitReimbursementSelectAll(this)">{{ __('Select/deselect all') }}</x-checkbox> 

        <div class="split-equal-price-breakdown-container">
            <div>{{__('$') }}<span id="split-reimbursement-price-breakdown">{{$expense && $expense->participants->count() !== 0 ? number_format(round($expense->amount / $expense->participants->count(), 2), 2) : '0.00' }}</span>{{ __('/person')}}</div>
            <div class="text-shy">(<span id="split-reimbursement-participant-count">{{ $expense?->participants->count() ?? '1' }}</span><span id="split-reimbursement-participant-count-label">{{ $expense?->participants->count() === 1 || $expense === null ? __(' person') : __(' people') }}</span>)</div>
        </div>
    </div>

    <div class="expense-dropdown-empty-warning hidden">
        {{ __('You must add users to the reimbursement before you can divvy it up.') }}
    </div>

    <ul class="split-reimbursement-list" id="split-reimbursement-list">
        @if ($expense === null) <!-- Creating a new Expense -->
            @if ($group) <!-- Expense was added from a Group, so show the Group members by default -->
                @foreach ($group->group_members as $member)
                    <li>
                        <label class="expand-dropdown-item" for="split-reimbursement-item-{{ $member->id }}" data-user-id="{{ $member->id }}" onclick="splitReimbursementUpdateSelectAll()">
                            <input type="checkbox" id="split-reimbursement-item-{{ $member->id }}" class="checkbox split-reimbursement-item-checkbox" name="split-reimbursement-user[]" value="{{ $member->id }}" checked />
                            <div class="dropdown-user-item-img-name">
                                <div class="profile-img-sm-container">
                                    <img src="{{ $member->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img-sm">
                                </div>
                                <div class="dropdown-user-item-name">{{ $member->username }}</div>
                            </div>
                        </label>
                    </li>
                @endforeach
            @else <!-- Expense was not added from a Group (or it was added from "Individual Expenses") -->
                <li>
                    <label class="expand-dropdown-item" for="split-reimbursement-item-{{ $current_user->id }}" data-user-id="{{ $current_user->id }}" onclick="splitReimbursementUpdateSelectAll()">
                        <input type="checkbox" id="split-reimbursement-item-{{ $current_user->id }}" class="checkbox split-reimbursement-item-checkbox" name="split-reimbursement-user[]" value="{{ $current_user->id }}" checked />
                        <div class="dropdown-user-item-img-name">
                            <div class="profile-img-sm-container">
                                <img src="{{ $current_user->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img-sm">
                            </div>
                            <div class="dropdown-user-item-name">{{ $current_user->username }}</div>
                        </div>
                    </label>
                </li>

                @if ($friend) <!-- Expense was added from a Friend -->
                    <li>
                        <label class="expand-dropdown-item" for="split-reimbursement-item-{{ $friend->id }}" data-user-id="{{ $friend->id }}" onclick="splitReimbursementUpdateSelectAll()">
                            <input type="checkbox" id="split-reimbursement-item-{{ $friend->id }}" class="checkbox split-reimbursement-item-checkbox" name="split-reimbursement-user[]" value="{{ $friend->id }}" checked />
                            <div class="dropdown-user-item-img-name">
                                <div class="profile-img-sm-container">
                                    <img src="{{ $friend->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img-sm">
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
                    <label class="expand-dropdown-item" for="split-reimbursement-item-{{ $involved_user->id }}" data-user-id="{{ $involved_user->id }}" onclick="splitReimbursementUpdateSelectAll()">
                        <input type="checkbox" id="split-reimbursement-item-{{ $involved_user->id }}" class="checkbox split-reimbursement-item-checkbox" name="split-reimbursement-user[]" value="{{ $involved_user->id }}" {{ $expense->participants->contains('id', $involved_user->id) ? 'checked' : '' }}/>
                        <div class="dropdown-user-item-img-name">
                            <div class="profile-img-sm-container">
                                <img src="{{ $involved_user->getProfileImageUrlAttribute() }}" alt="User profile image" class="profile-img-sm">
                            </div>
                            <div class="dropdown-user-item-name">{{ $involved_user->username }}</div>
                        </div>
                    </label>
                </li>
            @endforeach
        @endif
    </ul>
</div>

<template id="split-reimbursement-dropdown-item-template">
    <li>
        <label class="expand-dropdown-item" for="" onclick="splitReimbursementUpdateSelectAll()" data-user-id="">
            <input type="checkbox" id="" class="checkbox split-reimbursement-item-checkbox" name="split-reimbursement-user[]" value="" checked/>
            <div class="dropdown-user-item-img-name">
                <div class="profile-img-sm-container">
                    <img src="" alt="User profile image" class="profile-img-sm">
                </div>
                <div class="dropdown-user-item-name"></div>
            </div>
        </label>
    </li>
</template>
