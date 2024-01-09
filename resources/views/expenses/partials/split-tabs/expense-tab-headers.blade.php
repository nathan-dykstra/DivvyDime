<ul class="expense-split-tabs" id="expense-split-tabs">
    <li 
        class="expense-split-tab {{ $expense->expense_type_id === $expense_type_ids['equal'] ? 'expense-split-tab-active' : '' }}"
        data-tab-id="expense-split-equal"
        data-tab-name="{{ $expense_type_names[$expense_type_ids['equal']] }}"
        data-expense-type-id="{{ $expense_type_ids['equal'] }}"
        onclick="setExpenseSplit(this)"
    >{{ $expense_type_names[$expense_type_ids['equal']] }}</li>
    <li class="expense-split-tab {{ $expense->expense_type_id === $expense_type_ids['amount'] ? 'expense-split-tab-active' : '' }}"
        data-tab-id="expense-split-amount"
        data-tab-name="{{ $expense_type_names[$expense_type_ids['amount']] }}"
        data-expense-type-id="{{ $expense_type_ids['amount'] }}"
        onclick="setExpenseSplit(this)"
    >{{ $expense_type_names[$expense_type_ids['amount']] }}</li>
    <li class="expense-split-tab {{ $expense->expense_type_id === $expense_type_ids['percentage'] ? 'expense-split-tab-active' : '' }}"
        data-tab-id="expense-split-percentage"
        data-tab-name="{{ $expense_type_names[$expense_type_ids['percentage']] }}"
        data-expense-type-id="{{ $expense_type_ids['percentage'] }}"
        onclick="setExpenseSplit(this)"
    >{{ $expense_type_names[$expense_type_ids['percentage']] }}</li>
    <li class="expense-split-tab {{ $expense->expense_type_id === $expense_type_ids['share'] ? 'expense-split-tab-active' : '' }}"
        data-tab-id="expense-split-share"
        data-tab-name="{{ $expense_type_names[$expense_type_ids['share']] }}"
        data-expense-type-id="{{ $expense_type_ids['share'] }}"
        onclick="setExpenseSplit(this)"
    >{{ $expense_type_names[$expense_type_ids['share']] }}</li>
    <li class="expense-split-tab {{ $expense->expense_type_id === $expense_type_ids['adjustment'] ? 'expense-split-tab-active' : '' }}"
        data-tab-id="expense-split-adjustment"
        data-tab-name="{{ $expense_type_names[$expense_type_ids['adjustment']] }}"
        data-expense-type-id="{{ $expense_type_ids['adjustment'] }}"
        onclick="setExpenseSplit(this)"
    >{{ $expense_type_names[$expense_type_ids['adjustment']] }}</li>
    <li class="expense-split-tab {{ $expense->expense_type_id === $expense_type_ids['reimbursement'] ? 'expense-split-tab-active' : '' }}"
        data-tab-id="expense-split-reimbursement"
        data-tab-name="{{ $expense_type_names[$expense_type_ids['reimbursement']] }}"
        data-expense-type-id="{{ $expense_type_ids['reimbursement'] }}"
        onclick="setExpenseSplit(this)"
    >{{ $expense_type_names[$expense_type_ids['reimbursement']] }}</li>
    <li class="expense-split-tab {{ $expense->expense_type_id === $expense_type_ids['itemized'] ? 'expense-split-tab-active' : '' }}"
        data-tab-id="expense-split-itemized"
        data-tab-name="{{ $expense_type_names[$expense_type_ids['itemized']] }}"
        data-expense-type-id="{{ $expense_type_ids['itemized'] }}"
        onclick="setExpenseSplit(this)"
    >{{ $expense_type_names[$expense_type_ids['itemized']] }}</li>
</ul>
