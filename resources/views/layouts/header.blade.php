<header class="page-header" id="page-header">
    <div class="header-wrapper" id="header-wrapper">
        <div class="header-content" id="header-content">
            <div class="header-left">
                @if (isset($back_link))
                    <x-no-background-button class="mobile-header-btn" icon="fa-solid fa-arrow-left" :href="$back_link" />
                @elseif (isset($back_btn))
                    {{ $back_btn }}
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
                                <div class="desktop-hidden">
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

<style>
    .page-header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 4;
        background-color: var(--background);
        transition: background-color 0.3s;
    }

    .page-header.header-scrolling {
        background-color: var(--background-blur-color);
        backdrop-filter: var(--background-blur-filter);
        border-bottom: 1px solid var(--border-grey);
    }

    .header-content {
        max-width: var(--main-content-width);
        margin: 0 auto;
        height: calc(3 * var(--navbar-height));
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        transition: height 0.3s ease;
    }

    .header-content.header-content-scrolling {
        height: var(--navbar-height);
    }

    @media screen and (min-width: 768px) and (max-width: 1312px) {
        .header-content {
            margin-left: var(--container-padding);
            margin-right: var(--container-padding);
        }
    }

    @media screen and (max-width: 768px) {
        .header-content {
            margin-left: var(--container-small-padding);
            margin-right: var(--container-small-padding);
        }
    }

    .header-left {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        gap: 10px;
        max-width: 60%;
    }

    @media screen and (max-width: 768px) {
        .header-left {
            max-width: 70%;
        }
    }

    .header-right {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
    }

    .header-image {
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    
    .header-image-hidden {
        opacity: 0;
        transform: scale(0.7);
    }

    .header-title {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        transition: transform 0.3s ease;
    }

    .header-title-shift {
        transform: translateX(-75px); /* 65px image width + 10px gap */
    }

    .mobile-header-btn {
        min-width: 36px;
        max-width: 36px;
        min-height: 36px;
        max-height: 36px;
        color: var(--text-primary);
        padding: 0;
        border-radius: 50%;
    }

    .mobile-header-btn:focus-visible {
        border-radius: 50%;
    }

    .mobile-search-btn {
        display: none;
    }

    @media screen and (max-width: 768px) {
        .mobile-search-btn {
            display: block;
        }
    }

    .desktop-header-buttons {
        display: flex;
        gap: 10px;
    }

    @media screen and (max-width: 768px) {
        .desktop-header-buttons {
            display: none;
        }
    }
</style>

<script>
    function logOut() {
        document.getElementById('header-log-out-form').submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const header = document.getElementById('page-header');
        const headerContent = document.getElementById('header-content');
        const headerImage = document.getElementById('header-image');
        const headerTitle = document.getElementById('header-title');

        window.addEventListener('scroll', function(e) {
            const scrollTop = window.scrollY;

            if (scrollTop > 0) {
                header.classList.add('header-scrolling');
                headerContent.classList.add('header-content-scrolling');

                if (headerImage) {
                    headerImage.classList.add('header-image-hidden');
                    if (headerTitle) {
                        headerTitle.classList.add('header-title-shift');
                    }
                }
            } else {
                header.classList.remove('header-scrolling');
                headerContent.classList.remove('header-content-scrolling');

                if (headerImage) {
                    headerImage.classList.remove('header-image-hidden');
                    if (headerTitle) {
                        headerTitle.classList.remove('header-title-shift');
                    }
                }
            }
        });
    });
</script>
