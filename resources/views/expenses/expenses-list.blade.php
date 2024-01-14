<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Expenses') }}</h2>
            <div class="btn-container-end">
                <x-primary-button icon="fa-solid fa-receipt icon" :href="route('expenses.create')">{{ __('Add Expense') }}</x-primary-button>
                <x-primary-button icon="fa-solid fa-scale-balanced icon">{{ __('Settle Up') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    @if (session('status') === 'expense-deleted')
        <x-session-status>{{ __('Expense deleted.') }}</x-session-status>
    @endif

    <div class="section-search">
        <div class="restrict-max-width">
            <x-searchbar-secondary placeholder="Search Expenses" id="search-expenses"></x-searchbar-secondary>
        </div>
    </div>

    <div class="expenses-list-container">
        @include('expenses.partials.expenses')
    </div>
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

    function openExpense(expense) {
        window.location.href = expense;
    }
</script>
