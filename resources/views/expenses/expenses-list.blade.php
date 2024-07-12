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

    @if (count($expenses) === 0)
        <div class="notifications-empty-container">
            <div class="notifications-empty-icon"><i class="fa-solid fa-receipt"></i></div>
            <div class="notifications-empty-text">{{ __('No expenses!') }}</div>
        </div>
    @else
        <div class="section-search">
            <div class="restrict-max-width">
                <x-searchbar-secondary placeholder="Search Expenses" id="search-expenses"></x-searchbar-secondary>
            </div>
        </div>

        <div class="expenses-list-container">
            @include('expenses.partials.expenses')
        </div>
    @endif
</x-app-layout>

<script>
    expensesSearchbar = document.getElementById("search-expenses");
    expensesSearchbar.addEventListener('input', function(event) {
        var searchString = event.target.value;

        $.ajax({
            url: "{{ route('expenses.search') }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'search_string': searchString,
            },
            success: function(html) {
                expenses = $('.expenses');
                expenses.replaceWith(html);
            },
            error: function(error) {
                console.log(error);
            }
        });
    });
</script>
