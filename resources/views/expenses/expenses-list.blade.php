<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ __('Expenses') }}
    </x-slot>

    <x-slot name="header_title">
        {{ __('Expenses') }}
    </x-slot>

    <x-slot name="header_buttons">
        <x-primary-button icon="fa-solid fa-receipt icon" :href="route('expenses.create')">{{ __('Add Expense') }}</x-primary-button>
        <x-primary-button icon="fa-solid fa-scale-balanced icon" :href="route('payments.create')">{{ __('Settle Up') }}</x-primary-button>
    </x-slot>

    <x-slot name="mobile_overflow_options">
        <a class="dropdown-item" href="{{ route('expenses.create') }}">
            <i class="fa-solid fa-receipt"></i>
            <div>{{ __('Add Expense') }}</div>
        </a>
        <a class="dropdown-item" href="{{ route('payments.create') }}">
            <i class="fa-solid fa-scale-balanced"></i>
            <div>{{ __('Settle Up') }}</div>
        </a>
    </x-slot>

    <!-- Session Status Messages -->

    @if (session('status') === 'expense-deleted')
        <x-session-status>{{ __('Expense deleted.') }}</x-session-status>
    @elseif (session('status') === 'payment-deleted')
        <x-session-status>{{ __('Payment deleted.') }}</x-session-status>
    @endif

    <!-- Content -->

    <div class="section-search">
        <div class="restrict-max-width">
            <x-searchbar-secondary placeholder="{{ __('Search Expenses') }}" id="search-expenses"></x-searchbar-secondary>
        </div>
    </div>

    <div class="expenses-list-container">
        <!-- No expenses message -->
        <div class="notifications-empty-container hidden" id="no-expenses">
            <div class="notifications-empty-icon"><i class="fa-solid fa-receipt"></i></div>
            <div class="notifications-empty-text">{{ __('No expenses!') }}</div>
        </div>

        <div class="expenses" id="expenses-list"></div>

        <!-- Loading animation -->
        <div id="expenses-loading">
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
        const loadingPlaceholder = document.getElementById('expenses-loading');
        const expensesList = document.getElementById('expenses-list');
        const noExpensesMessage = document.getElementById('no-expenses');

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
            url: '{{ route('expenses.get-expenses') }}' + '?page=' + page,
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

        const searchInput = document.getElementById("search-expenses");
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
