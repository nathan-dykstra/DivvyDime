<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ $group->name . __(' Spending Summary') }}
    </x-slot>

    <x-slot name="back_btn"></x-slot>

    <x-slot name="header_title">
        {{ $group->name . __(' Spending Summary') }}
    </x-slot>

    <!-- Session Status Messages -->

    <!-- Content -->

    <div class="container">
        <div class="restrict-max-width space-top-sm">
            <div class="section-search">
                <div class="btn-container-start">
                    <x-dropdown align="left">
                        <x-slot name="trigger">
                            <x-primary-button class="expense-round-btn" id="totals-period-trigger" icon="fa-solid fa-calendar-days icon">{{ __('Current Month') }}</x-primary-button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="dropdown-item-no-icon" onclick="showSummary('current-month', '{{ __('Current Month') }}')">
                                <div>{{ __('Current Month') }}</div>
                            </div>
                            <div class="dropdown-item-no-icon" onclick="showSummary('last-month', '{{ __('Last Month') }}')">
                                <div>{{ __('Last Month') }}</div>
                            </div>
                            <div class="dropdown-item-no-icon" onclick="showSummary('average-month', '{{ __('Monthly Average') }}')">
                                <div>{{ __('Monthly Average') }}</div>
                            </div>
                            <div class="dropdown-item-no-icon" onclick="showSummary('all-time', '{{ __('All Time') }}')">
                                <div>{{ __('All Time') }}</div>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <div>
                <div id="current-month" class="group-summary space-top-sm">
                    <div>
                        <h4 class="text-grey">{{ __('Group spending') }}</h4>
                        <h3>{{ __('$') . number_format($current_month['group'], 2) }}</h3>
                    </div>

                    <div>
                        <h4 class="text-grey">{{ __('You paid') }}</h4>
                        <h3>{{ __('$') . number_format($current_month['paid'], 2) }}</h3>
                    </div>

                    <div>
                        <h4 class="text-grey">{{ __('Your share') }}</h4>
                        <h3>{{ __('$') . number_format($current_month['share'], 2) }}</h3>
                    </div>
                </div>

                <div id="last-month" class="group-summary space-top-sm hidden">
                    <div>
                        <h4 class="text-grey">{{ __('Group spending') }}</h4>
                        <h3>{{ __('$') . number_format($last_month['group'], 2) }}</h3>
                    </div>
                    <div>
                        <h4 class="text-grey">{{ __('You paid') }}</h4>
                        <h3>{{ __('$') . number_format($last_month['paid'], 2) }}</h3>
                    </div>
                    <div>
                        <h4 class="text-grey">{{ __('Your share') }}</h4>
                        <h3>{{ __('$') . number_format($last_month['share'], 2) }}</h3>
                    </div>
                </div>

                <div id="average-month" class="group-summary space-top-sm hidden">
                    <div>
                        <h4 class="text-grey">{{ __('Group spending') }}</h4>
                        <h3>{{ __('$') . number_format($average_month['group'], 2) }}</h3>
                    </div>
                    <div>
                        <h4 class="text-grey">{{ __('You paid') }}</h4>
                        <h3>{{ __('$') . number_format($average_month['paid'], 2) }}</h3>
                    </div>
                    <div>
                        <h4 class="text-grey">{{ __('Your share') }}</h4>
                        <h3>{{ __('$') . number_format($average_month['share'], 2) }}</h3>
                    </div>
                </div>

                <div id="all-time" class="group-summary space-top-sm hidden">
                    <div>
                        <h4 class="text-grey">{{ __('Group spending') }}</h4>
                        <h3>{{ __('$') . number_format($all_time['group'], 2) }}</h3>
                    </div>
                    <div>
                        <h4 class="text-grey">{{ __('You paid') }}</h4>
                        <h3>{{ __('$') . number_format($all_time['paid'], 2) }}</h3>
                    </div>
                    <div>
                        <h4 class="text-grey">{{ __('Your share') }}</h4>
                        <h3>{{ __('$') . number_format($all_time['share'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function showSummary(id, label) {
        const trigger = document.getElementById('totals-period-trigger');
        trigger.innerHTML = `<i class="fa-solid fa-calendar-days icon"></i>${label}`;
    
        const summaries = document.querySelectorAll('.group-summary');
        summaries.forEach(summary => {
            if (summary.id === id) {
                summary.classList.remove('hidden');
            } else {
                summary.classList.add('hidden');
            }
        });
    }
</script>
