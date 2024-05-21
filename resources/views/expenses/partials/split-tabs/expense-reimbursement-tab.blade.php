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

    <div class="paid-dropdown-empty-warning hidden">
        {{ __('You must add users to the reimbursement before you can divvy it up.') }}
    </div>

    <ul class="split-reimbursement-list" id="split-reimbursement-list">
        <!-- TODO: Show user profile photo in this list -->
        @if ($expense === null) <!-- Creating a new Expense -->
            @if ($group) <!-- Expense was added from a Group, so show the Group members by default -->
                @foreach ($group->group_members as $member)
                    <li>
                        <label class="split-equal-item" for="split-reimbursement-item-{{ $member->id }}" onclick="splitReimbursementUpdateSelectAll()">
                            <input type="checkbox" id="split-reimbursement-item-{{ $member->id }}" class="checkbox split-reimbursement-item-checkbox" name="split-reimbursement-user[]" value="{{ $member->id }}" checked />
                            <div class="user-photo-name">
                                <div class="profile-circle-sm-placeholder"></div>
                                <div class="split-equal-item-name">{{ $member->username }}</div>
                            </div>
                        </label>
                    </li>
                @endforeach
            @else <!-- Expense was not added from a Group (or it was added from "Individual Expenses") -->
                <li>
                    <label class="split-equal-item" for="split-reimbursement-item-{{ auth()->user()->id }}" onclick="splitReimbursementUpdateSelectAll()">
                        <input type="checkbox" id="split-reimbursement-item-{{ auth()->user()->id }}" class="checkbox split-reimbursement-item-checkbox" name="split-reimbursement-user[]" value="{{ auth()->user()->id }}" checked />
                        <div class="user-photo-name">
                            <div class="profile-circle-sm-placeholder"></div>
                            <div class="split-equal-item-name">{{ auth()->user()->username }}</div>
                        </div>
                    </label>
                </li>

                @if ($friend) <!-- Expense was added from a Friend -->
                    <li>
                        <label class="split-equal-item" for="split-reimbursement-item-{{ $friend->id }}" onclick="splitReimbursementUpdateSelectAll()">
                            <input type="checkbox" id="split-reimbursement-item-{{ $friend->id }}" class="checkbox split-reimbursement-item-checkbox" name="split-reimbursement-user[]" value="{{ $friend->id }}" checked />
                            <div class="user-photo-name">
                                <div class="profile-circle-sm-placeholder"></div>
                                <div class="split-equal-item-name">{{ $friend->username }}</div>
                            </div>
                        </label>
                    </li>
                @endif
            @endif
        @else <!-- Updating an existing Expense -->
            @foreach ($expense->involvedUsers() as $involved_user)
                <li>
                    <label class="split-equal-item" for="split-reimbursement-item-{{ $involved_user->id }}" onclick="splitReimbursementUpdateSelectAll()">
                        <input type="checkbox" id="split-reimbursement-item-{{ $involved_user->id }}" class="checkbox split-reimbursement-item-checkbox" name="split-reimbursement-user[]" value="{{ $involved_user->id }}" {{ $expense->participants->contains('id', $involved_user->id) ? 'checked' : '' }}/>
                        <div class="user-photo-name">
                            <div class="profile-circle-sm-placeholder"></div>
                            <div class="split-equal-item-name">{{ $involved_user->username }}</div>
                        </div>
                    </label>
                </li>
            @endforeach
        @endif
    </ul>
</div>

<template id="split-reimbursement-dropdown-item-template">
    <li>
        <label class="split-equal-item" for="" onclick="splitReimbursementUpdateSelectAll()">
            <input type="checkbox" id="" class="checkbox split-reimbursement-item-checkbox" name="split-reimbursement-user[]" value="" checked/>
            <div class="user-photo-name">
                <div class="profile-circle-sm-placeholder"></div>
                <div class="split-equal-item-name"></div>
            </div>
        </label>
    </li>
</template>

<script>
    function splitReimbursementSelectAll(box) {
        $('.split-reimbursement-item-checkbox').prop('checked', box.checked);

        splitReimbursementUpdatePriceBreakdown();
    }

    function splitReimbursementUpdateSelectAll() {
        $('#split-reimbursement-select-all').prop('checked', $('.split-reimbursement-item-checkbox:checked').length === $('.split-reimbursement-item-checkbox').length);

        splitReimbursementUpdatePriceBreakdown();
    }

    function splitReimbursementUpdatePriceBreakdown() {
        const currentParticipantCount = parseInt($('.split-reimbursement-item-checkbox:checked').length);
        const amountPerParticipant = currentParticipantCount === 0 || currentAmountInput.value === '' ? 0 : parseFloat(currentAmountInput.value) / currentParticipantCount;

        $('#split-reimbursement-price-breakdown').text(amountPerParticipant.toFixed(2));
        $('#split-reimbursement-participant-count').text(currentParticipantCount);
        $('#split-reimbursement-participant-count-label').text(currentParticipantCount === 1 ? "{{ __(' person') }}" : "{{ __(' people') }}");
    }
</script>
