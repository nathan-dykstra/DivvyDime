<!-- Desktop search -->



<!-- Mobile search -->

<div class="mobile-search-wrapper" id="mobile-search-wrapper">
    <div class="mobile-searchbar-container" id="mobile-searchbar-container">
        <div class="mobile-search-input-container" id="mobile-search-input-container">
            <x-mobile-searchbar-primary type="text" class="mobile-search-input" id="mobile-search-input"></x-searchbar-primary>
        </div>

        <div class="mobile-search-icon-btn" id="mobile-search-icon-btn">
            <i class="fa-solid fa-xmark mobile-search-close" id="mobile-search-close" onclick="closeMobileSearch()"></i>
            <i class="fa-solid fa-magnifying-glass mobile-search-icon-open" id="mobile-search-icon"></i>
        </div>
    </div>

    <div class="mobile-search-results" id="mobile-search-results">
        <div class="mobile-search-recent-expenses" id="mobile-search-recent-expenses">
            <h3 class="mobile-search-header">{{ __('Recent Expenses') }}</h3>
            <div class="mobile-search-section">
                <ul>
                    <li>
                        <p>Result 1</p>
                    </li>
                    <li>
                        <p>Result 2</p>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mobile-search-results-list" id="mobile-search-results-list">
            <h3 class="mobile-search-header">{{ __('Friends') }}</h3>
            <div class="mobile-search-section">
                <ul>
                    <li>
                        <p>Expense 1</p>
                    </li>
                    <li>
                        <p>Expense 2</p>
                    </li>
                    <li>
                        <p>Result 3</p>
                    </li>
                    <li>
                        <p>Result 4</p>
                    </li>
                    <li>
                        <p>Result 4</p>
                    </li>
                    <li>
                        <p>Result 4</p>
                    </li>
                </ul>
            </div>

            <h3 class="mobile-search-header">{{ __('Groups') }}</h3>
            <div class="mobile-search-section">
                <ul>
                    <li>
                        <p>Expense 1</p>
                    </li>
                    <li>
                        <p>Expense 2</p>
                    </li>
                    <li>
                        <p>Result 3</p>
                    </li>
                </ul>
            </div>

            <h3 class="mobile-search-header">{{ __('Expenses') }}</h3>
            <div class="mobile-search-section">
                <ul>
                    <li>
                        <p>Expense 1</p>
                    </li>
                    <li>
                        <p>Expense 2</p>
                    </li>
                    <li>
                        <p>Result 3</p>
                    </li>
                    <li>
                        <p>Result 4</p>
                    </li>
                    <li>
                        <p>Result 4</p>
                    </li>
                </ul>
            </div>
        </div>

        @if (false)
            <div class="no-search-results text-center">
                <p>{{ __('There are no results matching your search!') }}</p>
            </div>
        @endif
    </div>
</div>
