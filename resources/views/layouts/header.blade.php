<header class="page-header" id="page-header">
    <div class="header-wrapper" id="header-wrapper">
        <div class="header-content" id="header-content">
            <div class="header-left">
                @if (isset($back_btn))
                    <x-no-background-button class="mobile-header-btn" icon="fa-solid fa-arrow-left" onclick="handleBackBtnClick()" />
                @endif

                @if (isset($header_image))
                    <div class="header-image" id="header-image">
                        {{ $header_image }}
                    </div>
                @endif

                @if (isset($header_title))
                    <h2 class="header-title" id="header-title">{{ $header_title }}</h2>
                @endif
            </div>

            <div class="header-right">
                @if (isset($header_buttons))
                    <div class="desktop-header-buttons">
                        {{ $header_buttons }}
                    </div>
                @endif

                <div class="mobile-search-btn">
                    <x-no-background-button class="mobile-header-btn" icon="fa-solid fa-magnifying-glass" onclick="openMobileSearch()" />
                </div>

                <div class="overflow-menu">
                    <x-dropdown>
                        <x-slot name="trigger">
                            <x-no-background-button class="mobile-header-btn" icon="fa-solid fa-ellipsis-vertical" />
                        </x-slot>

                        <x-slot name="content">
                            @if (isset($mobile_overflow_options))
                                <div class="mobile-overflow-options">
                                    {{ $mobile_overflow_options }}
                                    <div class="dropdown-divider"></div>
                                </div>
                            @endif

                            @if (isset($overflow_options))
                                {{ $overflow_options }}
                                <div class="dropdown-divider"></div>
                            @endif

                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fa-solid fa-gear"></i>
                                <div>{{ __('Profile & Settings') }}</div>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" id="header-log-out-form">
                                @csrf
                                <div class="dropdown-item" onclick="logOut()">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    <div>{{ __('Log Out') }}</div>
                                </div>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </div>
    </div>
</header>
