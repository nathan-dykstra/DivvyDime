<div class="dropdown2-submenu hidden" tabindex="0">
    @foreach ($category_group->categories as $category)
        <div class="dropdown2-item dropdown2-item-child-lg"
            data-icon="{{ $category->icon_class }}"
            data-colour="{{ $category_group->colour_class.'-border' }}"
            data-category-id="{{ $category->id }}"
            onclick="setExpenseCategory(this)"
        >
            <div class="expense-category-icon-bg {{ $category_group->colour_class.'-border' }}">
                <i class="{{ $category->icon_class }}"></i>
            </div>
            <div>{{ $category->category }}</div>
        </div>
    @endforeach

    @if ($category_group->other_category)
        <div class="dropdown-divider"></div>

        <div class="dropdown2-item dropdown2-item-child-lg"
            data-icon="{{ $category_group->other_category->icon_class }}"
            data-colour="{{ $category_group->colour_class.'-border' }}"
            data-category-id="{{ $category_group->other_category->id }}"
            onclick="setExpenseCategory(this)"
        >
            <div class="expense-category-icon-bg {{ $category_group->colour_class.'-border' }}">
                <i class="{{ $category_group->other_category->icon_class }}"></i>
            </div>
            <div>{{ $category_group->other_category->category }}</div>
        </div>
    @endif
</div>