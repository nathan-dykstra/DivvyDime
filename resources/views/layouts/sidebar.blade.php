@php
    $sidebar = config('sidebar');
    $mobile_nav = config('mobilenav');
@endphp

<div class="show-sidebar-btn" id="show-sidebar-btn">
    <i class="fa-solid fa-angle-right"></i>
</div>

<nav>
    <!-- Sidebar Navigation Menu -->

    <div class="sidebar" id="sidebar">
        <div class="pin-sidebar-btn-container-end">
            <div class="tooltip tooltip-left">
                <x-icon-button class="hidden" icon="fa-solid fa-bars pin-sidebar-icon" id="pin-sidebar-icon" onclick="animateSidebarIcon(), toggleSidebar()"></x-icon-button>
                <span class="tooltip-text" id="pin-sidebar-tooltip"></span>
            </div>
        </div>

        <a href="{{ route('dashboard') }}">
            <h1 class="logo-container">DivvyDime</h1>
        </a>

        <ul class="sidebar-items">
            @foreach($sidebar as $item)
                <li>
                    <a href="{{ route($item['route']) }}" >
                        <div class="sidebar-item">
                            <div class="sidebar-item-content">
                                <i class="{{ $item['icon'] }}"></i>
                                {{ __($item['text']) }}
                            </div>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Mobile Navigation Menu -->

    <div class="mobile-nav-wrapper">
        <ul class="mobile-nav">
            @foreach($mobile_nav as $item)
                <li>
                    <a href="{{ route($item['route']) }}" class="mobile-nav-item" onclick="mobileHighlightNavSelection()">
                        <div class="mobile-nav-item-icon">
                            <i class="{{ $item['icon'] }}"></i>
                        </div>
                        <div class="mobile-nav-item-text">
                            {{ $item['text'] }}
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</nav>

<script>
    function highlightSidebarItem() {
        let tempCurrentRoute = window.location.href;
        const currentRoute = tempCurrentRoute.replace(/\/payments/g, "/expenses"); // "Expenses" sidebar item should be highlighted for payments pages

        const sidebarItems = document.querySelectorAll(".sidebar-items li");

        // Loop through sidebar items to find a match with the current route
        sidebarItems.forEach((item, index) => {
            const anchor = item.querySelector('a');
            const href = anchor.getAttribute('href');
            const hrefPattern = new RegExp('^' + href + '\/.*');

            if (href === currentRoute || hrefPattern.test(currentRoute)) {
                item.querySelector('.sidebar-item').classList.add('sidebar-item-active');
            }
        });
    }

    function mobileHighlightNavSelection() {
        const currentRoute = window.location.href;

        const sidebarItems = document.querySelectorAll(".mobile-nav li");

        // Loop through sidebar items to find a match with the current route
        sidebarItems.forEach((item, index) => {
            const anchor = item.querySelector('a');
            const href = anchor.getAttribute('href');
            const hrefPattern = new RegExp('^' + href + '\/.*');

            if (href === currentRoute || hrefPattern.test(currentRoute)) {
                item.querySelector('.mobile-nav-item').classList.add('mobile-nav-item-active');
            }
        });
    }

    highlightSidebarItem();
    mobileHighlightNavSelection();
</script>
