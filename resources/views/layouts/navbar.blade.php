<!-- TODO: delete this file after search has been implemented -->

<div class="show-sidebar-btn" id="show-sidebar-btn">
    <i class="fa-solid fa-angle-right"></i>
</div>

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

<nav>
    <!-- Top Navigation Menu -->

    <div class="navbar">
        <div class="navbar-content navbar-content-solid" id="navbar-content">
            <div class="right-items">
                <ul>
                    <li>
                        <div class="searchbar" id="searchbar">
                            <div class="search-input-container" id="search-input-container">
                                <x-searchbar-primary type="text" class="search-input" id="search-input" placeholder="{{ __('Search Expenses, Groups, and Friends') }}"></x-searchbar-primary>
                            </div>

                            <div class="search-icon-btn" id="search-icon-btn">
                                <i class="fa-solid fa-xmark search-close" id="search-close" onclick="closeSearchbar()"></i>
                                <x-topnav-button icon="fa-solid fa-magnifying-glass search-icon" iconId="search-icon" onclick="animateSearchIcon(this), expandSearchbar()"></x-topnav-button>
                            </div>

                            <div class="search-results" id="search-results">
                                <div class="search-recent-expenses" id="search-recent-expenses">
                                    <h3 class="search-header">{{ __('Recent Expenses') }}</h3>
                                    <div class="search-section">
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

                                <div class="search-results-list" id="search-results-list">
                                    <h3 class="search-header">{{ __('Friends') }}</h3>
                                    <div class="search-section">
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

                                    <h3 class="search-header">{{ __('Groups') }}</h3>
                                    <div class="search-section">
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

                                    <h3 class="search-header">{{ __('Expenses') }}</h3>
                                    <div class="search-section">
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

                                <div class="search-hint text-center">
                                    <p class="text-shy">Tip: Press <span class="key-symbol">Ctrl</span> + <span class="key-symbol">Shift</span> + <span class="key-symbol">S</span> to search!</p>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li>
                        <x-topnav-button icon="fa-solid fa-circle-user icon" :href="route('profile.edit')">{{ __('Profile') }}</x-topnav-button>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-topnav-button type="submit" icon="fa-solid fa-right-from-bracket icon">{{ __('Log Out') }}</x-topnav-button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>


    <!-- Mobile Navigation Menu -->

    <div class="mobile-navbar">
        <div class="navbar-content navbar-content-solid" id="mobile-navbar-content">
            <div class="right-items">
                <ul>
                    <li>
                        <div class="open-mobile-search-btn" id="open-mobile-search-btn">
                            <x-topnav-button icon="fa-solid fa-magnifying-glass search-icon" iconId="mobile-open-search-icon" onclick="openMobileSearch()"></x-topnav-button>
                        </div>
                    </li>
                    <li>
                        <a href="{{ route('profile.edit') }}">
                            <x-topnav-button id="mobile-profile-btn" icon="fa-solid fa-circle-user icon">{{ __('Profile') }}</x-topnav-button>
                        </a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-topnav-button type="submit" id="mobile-log-out-btn" icon="fa-solid fa-right-from-bracket icon">{{ __('Log Out') }}</x-topnav-button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
