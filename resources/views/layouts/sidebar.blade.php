@php
    $sidebar = config('sidebar');
    $mobile_nav = config('mobilenav')
@endphp

<nav>
    <!-- Sidebar Navigation Menu -->

    <div class="sidebar" id="sidebar">
        <div class="pin-sidebar-btn-container-end">
            <div class="tooltip tooltip-left pin-sidebar-tooltip">
                <x-icon-button class="hidden" icon="fa-solid fa-bars pin-sidebar-icon" id="pin-sidebar-icon" onclick="animateSidebarIcon(), pinSidebar()"></x-icon-button>
                <span class="tooltip-text" id="pin-sidebar-tooltip">Pin Sidebar</span>
            </div>
        </div>
        

        <a href="{{ route('dashboard') }}">
            <h1 class="logo-container">
                DivvyDime
            </h1>
        </a>

        <ul class="sidebar-items" id="sidebar-items">
            @foreach($sidebar as $item)
                <li>
                    <a href="{{ route($item['route']) }}" >
                        <div class="sidebar-item"><i class="{{ $item['icon'] }} sidebar-icon"></i>{{ $item['text'] }}</div>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>


    <!-- Sidebar Navigation Menu -->

    <div class="mobile-navigation-wrapper">
        <ul class="mobile-navigation">
            @foreach($mobile_nav as $item)
                <li>
                    <a href="{{ route($item['route']) }}" class="mobile-navigation-item" onclick="mobileHighlightNavSelection()">
                        <div>
                            <i class="{{ $item['icon'] }}"></i>
                        </div>
                        <div>{{ $item['text'] }}</div>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</nav>

<script>
    function highlightSidebarItem() {
        const currentRoute = '{{ route(Route::currentRouteName()) }}';

        const sidebarItems = document.querySelectorAll(".sidebar-items li");

        // Loop through sidebar items to find a match with the current route
        sidebarItems.forEach((item, index) => {
            const anchor = item.querySelector('a');
            const href = anchor.getAttribute('href');

            if (href === currentRoute) {
                console.log(currentRoute);
                console.log(href);
                item.querySelector('.sidebar-item').classList.add('sidebar-item-active');
            }
        });
    }

    function mobileHighlightNavSelection() {
        const currentRoute = '{{ route(Route::currentRouteName()) }}';

        const sidebarItems = document.querySelectorAll(".mobile-navigation li");

        // Loop through sidebar items to find a match with the current route
        sidebarItems.forEach((item, index) => {
            const anchor = item.querySelector('a');
            const href = anchor.getAttribute('href');

            if (href === currentRoute) {
                console.log(currentRoute);
                console.log(href);
                item.querySelector('.mobile-navigation-item').classList.add('mobile-nav-item-active');
            }
        });
    }

    highlightSidebarItem();
    mobileHighlightNavSelection();
</script>
