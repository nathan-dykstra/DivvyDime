<div class="show-sidebar-btn" id="show-sidebar-btn">
    <i class="fa-solid fa-angle-right"></i>
</div>

<div class="mobile-search-wrapper" id="mobile-search-wrapper">
    <div class="mobile-searchbar-container" id="mobile-searchbar-container">
        <div class="mobile-search-input-container" id="mobile-search-input-container">
            <x-mobile-searchbar-primary type="text" class="mobile-search-input" id="mobile-search-input" placeholder="Search..."></x-searchbar-primary>
        </div>

        <div class="mobile-search-icon-btn" id="mobile-search-icon-btn">
            <i class="fa-solid fa-xmark fa-sm mobile-search-close" id="mobile-search-close" onclick="closeMobileSearch()"></i>
            <i class="fa-solid fa-magnifying-glass fa-sm" id="mobile-search-icon"></i>
        </div>
    </div>

    <div class="mobile-search-results" id="mobile-search-results">
        <div class="mobile-search-recent-expenses" id="mobile-search-recent-expenses">
            <h3 class="mobile-search-header">Recent Expenses</h3>
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
            <h3 class="mobile-search-header">Friends</h3>
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

            <h3 class="mobile-search-header">Groups</h3>
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

            <h3 class="mobile-search-header">Expenses</h3>
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
                                <x-searchbar-primary type="text" class="search-input" id="search-input" placeholder="Search Expenses, Groups, and Friends..."></x-searchbar-primary>
                            </div>

                            <div class="search-icon-btn" id="search-icon-btn">
                                <i class="fa-solid fa-xmark fa-sm search-close" id="search-close" onclick="closeSearchbar()"></i>
                                <i class="fa-solid fa-magnifying-glass fa-sm search-icon" id="search-icon" onclick="animateSearchIcon(this), expandSearchbar()"></i>
                            </div>

                            <div class="search-results" id="search-results">
                                <div class="search-recent-expenses" id="search-recent-expenses">
                                    <h3 class="search-header">Recent Expenses</h3>
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
                                    <h3 class="search-header">Friends</h3>
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

                                    <h3 class="search-header">Groups</h3>
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

                                    <h3 class="search-header">Expenses</h3>
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
                            </div>
                        </div>
                    </li>
                    <li>
                        <a href="{{ route('profile.edit') }}">
                            <x-button-nobackground class="profile-btn" icon="fa-solid fa-user fa-sm icon">Profile</x-button-nobackground>
                        </a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <!--<button type="submit">Log Out</button>-->
                            <x-button-nobackground type="submit" class="log-out-btn" icon="fa-solid fa-arrow-right-from-bracket fa-sm icon">Log Out</x-button-nobackground>
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
                            <i class="fa-solid fa-magnifying-glass search-icon" id="mobile-open-search-icon" onclick="openMobileSearch()"></i>
                        </div>
                    </li>
                    <li>
                        <a href="{{ route('profile.edit') }}">
                            <x-button-nobackground class="profile-btn" id="mobile-profile-btn" icon="fa-solid fa-user fa-sm icon">Profile</x-button-nobackground>
                        </a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <!--<button type="submit">Log Out</button>-->
                            <x-button-nobackground type="submit" class="log-out-btn" id="mobile-log-out-btn" icon="fa-solid fa-arrow-right-from-bracket fa-sm icon">Log Out</x-button-nobackground>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
