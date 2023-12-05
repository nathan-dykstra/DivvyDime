@php
    $sidebar = config('sidebar');
@endphp

<nav>
    <!-- Sidebar Navigation Menu -->

    <div class="sidebar" id="sidebar">
        <div class="pin-sidebar-btn-container">
            <div class="tooltip tooltip-left pin-sidebar-tooltip">
                <i class="fa-solid fa-thumbtack pin-sidebar-icon hidden" id="pin-sidebar-icon" onclick="animateSidebarIcon(), pinSidebar()"></i>
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
                        <div class="sidebar-item" onclick="highlightSidebarItem(this)"><i class="{{ $item['icon'] }} sidebar-icon"></i>{{ $item['text'] }}</div>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>


    <!-- Sidebar Navigation Menu -->

    <div class="mobile-navigation-wrapper">
        <ul class="mobile-navigation">
            @foreach($sidebar as $item)
                <li>
                    <a href="{{ route($item['route']) }}" class="mobile-navigation-item">
                        <div class="">
                            <i class="{{ $item['icon'] }}"></i>
                        </div>
                        <div onclick="highlightNavigationItem(this)">{{ $item['text'] }}</div>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</nav>
