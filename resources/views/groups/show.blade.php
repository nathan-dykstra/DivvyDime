<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ $group->name }}
    </x-slot>

    <x-slot name="back_btn"></x-slot>

    <x-slot name="header_image">
        <div class="group-img-header-container">
            <img src="{{ $group->getGroupImageUrlAttribute() }}" alt="Group image" class="group-img-md">
        </div>
    </x-slot>

    <x-slot name="header_title">
        {{ $group->name }}
    </x-slot>

    <x-slot name="header_buttons">
        <x-primary-button icon="fa-solid fa-receipt icon" :href="route('expenses.create', $group->is_default ? '' : ['group' => $group->id])">{{ __('Add Expense') }}</x-primary-button>
        <x-primary-button icon="fa-solid fa-scale-balanced icon" :href="route('payments.create', $group->is_default ? '' : ['group' => $group->id])">{{ __('Settle Up') }}</x-primary-button>
    </x-slot>

    <x-slot name="overflow_options">
        <a class="dropdown-item" href="{{ route('groups.balances', $group) }}">
            <i class="fa-solid fa-scale-unbalanced"></i>
            <div>{{ __('Balances') }}</div>
        </a>
        <a class="dropdown-item" href="{{ route('groups.totals', $group) }}">
            <i class="fa-solid fa-calculator"></i>
            <div>{{ __('Totals') }}</div>
        </a>
        @if (!$group->is_default)
            <a class="dropdown-item" href="{{ route('groups.settings', $group) }}">
                <i class="fa-solid fa-gear"></i>
                <div>{{ __('Group Settings') }}</div>
            </a>
        @endif
    </x-slot>

    <x-slot name="mobile_overflow_options">
        <a class="dropdown-item" href="{{ route('expenses.create', $group->is_default ? '' : ['group' => $group->id]) }}">
            <i class="fa-solid fa-receipt"></i>
            <div>{{ __('Add Expense') }}</div>
        </a>
        <a class="dropdown-item" href="{{ route('payments.create', $group->is_default ? '' : ['group' => $group->id]) }}">
            <i class="fa-solid fa-scale-balanced"></i>
            <div>{{ __('Settle Up') }}</div>
        </a>
    </x-slot>

    <!-- Session Status Messages -->

    @if (session('status') === 'group-created')
        <x-session-status>{{ __('Group created.') }}</x-session-status>
    @endif

    <!-- Content -->

    <div class="metrics-container margin-bottom-lg">
        @if ($overall_balance > 0)
            <div class="metric-container text-success">
                <span class="text-small">{{ __('Overall, you are owed') }}</span>
                <span class="metric-number">{{ __('$') . number_format($overall_balance, 2) }}</span>
            </div>
        @elseif ($overall_balance < 0)
            <div class="metric-container text-warning">
                <span class="text-small">{{ __('Overall, you owe') }}</span>
                <span class="metric-number">{{ __('$') . number_format(abs($overall_balance), 2) }}</span>
            </div>
        @elseif ($group->is_settled_up)
            <div class="metric-container text-success">
                <span class="text-small">{{ __('Overall, you are') }}</span>
                <span class="metric-number">{{ __('Settled Up!') }}</span>
            </div>
        @else
            <div class="metric-container text-success">
                <span class="text-small">{{ __('Overall, you owe') }}</span>
                <span class="metric-number">{{ __('$0.00') }}</span>
            </div>
        @endif
        
        @foreach ($individual_balances as $individual_balance)
            @if ($individual_balance->balance > 0)
                <div class="metric-container">
                    <span class="text-primary text-small"><span class="bold-username">{{ $individual_balance->username }}</span>{{ __(' owes you') }}</span>
                    <span class="text-success metric-number">{{ __('$') . number_format($individual_balance->balance, 2) }}</span>
                </div>
            @elseif ($individual_balance->balance < 0)
                <div class="metric-container">
                    <span class="text-primary text-small">{{ __('You owe ') }}<span class="bold-username">{{ $individual_balance->username }}</span></span>
                    <span class="text-warning metric-number">{{ __('$') . number_format(abs($individual_balance->balance), 2) }}</span>
                </div>   
            @else
                <div class="metric-container">
                    <span class="text-primary text-small">{{ __('You and ') }}<span class="bold-username">{{ $individual_balance->username }}</span>{{ __(' are') }}</span>
                    <span class="text-success metric-number">{{ __('Settled Up!') }}</span>
                </div>
            @endif
        @endforeach

        @if ($hidden_balances_count > 0)
            <div class="metric-container">
                <span class="text-primary text-small">{{ __('Plus ') . $hidden_balances_count . __(' other ') }} {{ $hidden_balances_count > 1 ? __('balances') : __('balance') }}</span>
                <x-no-background-button class="width-content" :href="route('groups.show', $group)">{{ __('View All') }}</x-no-background-button> <!-- TODO: Change this to link to the Group Balances page -->
            </div>
        @endif
    </div>

    <div class="section-search">
        <div class="restrict-max-width">
            <x-searchbar-secondary placeholder="{{ __('Search Group Expenses') }}" id="search-group-expenses"></x-searchbar-secondary>
        </div>
    </div>

    <div class="expenses-list-container">
        <!-- No expenses message -->
        <div class="notifications-empty-container hidden" id="no-group-expenses">
            <div class="notifications-empty-icon"><i class="fa-solid fa-receipt"></i></div>
            <div class="notifications-empty-text">{{ __('No expenses!') }}</div>
        </div>

        <div class="expenses" id="group-expenses-list"></div>

        <!-- Loading animation -->
        <div id="group-expenses-loading">
            <x-list-loading></x-list-loading>
        </div>
    </div>
</x-app-layout>

<script>
    let page = 1;
    let loading = false;
    let lastPage = false;
    let query = '';

    function fetchExpenses(query, replace = false) {
        const loadingPlaceholder = document.getElementById('group-expenses-loading');
        const expensesList = document.getElementById('group-expenses-list');
        const noExpensesMessage = document.getElementById('no-group-expenses');

        loading = true;

        if (replace) {
            expensesList.innerHTML = '';
            lastPage = false;
            page = 1;
        }

        if (lastPage) {
            loading = false;
            return;
        }

        noExpensesMessage.classList.add('hidden');
        loadingPlaceholder.classList.remove('hidden');

        $.ajax({
            url: '{{ route('groups.get-group-expenses', $group->id) }}' + '?page=' + page,
            method: 'GET',
            data: {
                'query': query
            },
            success: function(response) {
                if (response.is_last_page) lastPage = true;
                page = parseInt(response.current_page) + 1;

                const html = response.html;

                setTimeout(() => {
                    loadingPlaceholder.classList.add('hidden');

                    if (replace) { // Replace the content on search or page load
                        if (html.trim().length == 0) {
                            expensesList.innerHTML = '';
                            noExpensesMessage.classList.remove('hidden');
                        } else {
                            noExpensesMessage.classList.add('hidden');
                            expensesList.innerHTML = html;
                        }
                    } else { // Append to the content on scroll
                        expensesList.insertAdjacentHTML('beforeend', html); 
                    }
                }, replace ? 300 : 600);

                loading = false;
            },
            error: function(error) {
                loadingPlaceholder.classList.add('hidden');
                loading = false;
                console.error(error);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchExpenses(query, true);

        const searchInput = document.getElementById("search-group-expenses");
        searchInput.addEventListener('input', function() {
            query = searchInput.value.trim();
            fetchExpenses(query, true);
        });

        function handleScroll() {
            if (loading) return;

            if (window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 100) {
                fetchExpenses(query);
            }
        }
        document.addEventListener('scroll', handleScroll);
    });
</script>
