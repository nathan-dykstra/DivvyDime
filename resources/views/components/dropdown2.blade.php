@props(['align' => 'right'])

@php
    switch ($align) {
        case 'left':
            $alignment = 'align-left';
            break;
        case 'right':
            $alignment = 'align-right';
            break;
    }
@endphp

<div class="dropdown-trigger-container">
    <!-- Menu trigger -->
    <div class="dropdown-trigger">
        {{ $trigger }}
    </div>

    <!-- Desktop menu -->
    <div class="dropdown2-container {{ $alignment }} hidden" tabindex="0">
        <div class="dropdown-menu">
            {{ $content }}
        </div>
    </div>

    <!-- Mobile menu -->
    <div class="modal-transparent-container hidden">
        <div class="modal-transparent-bg"></div>
    </div>
    <div class="dropdown2-mobile hidden" tabindex="0">
        <div class="dropdown-mobile-handle"></div>

        {{ $content }}
    </div>
</div>

<style>
    .align-right {
        direction: ltr;
        transform-origin: top left;
        right: 0; 
    }

    .align-left {
        direction: ltr;
        transform-origin: top right;
        left: 0
    }

    .dropdown-trigger-container {
        position: relative;
    }

    .dropdown2-container {
        position: absolute;
        z-index: 50;
        opacity: 0;
        transform: scale(0.95);
        transition: opacity 100ms ease-out, transform 100ms ease-out;
    }

    .dropdown2-container.open {
        opacity: 1;
        transform: scale(1);
        transition: opacity 100ms ease-in, transform 100ms ease-in;
    }

    .dropdown2-container-down {
        top: 100%;
        margin-top: 8px;
    }

    .dropdown2-container-up {
        bottom: 100%;
        margin-bottom: 8px;
    }

    .dropdown2-container:focus, .dropdown2-submenu:focus {
        outline: none;
    }

    .dropdown-menu {
        display: flex;
        flex-direction: column;
        background-color: var(--secondary-grey);
        border-radius: var(--border-radius-lg);
        color: var(--text-primary);
        padding: 8px;
        box-shadow: var(--box-shadow);
    }

    .dropdown2-submenu {
        display: flex;
        flex-direction: column;
        position: absolute;
        top: -8px;
        left: 100%;
        background-color: var(--secondary-grey);
        color: var(--text-primary);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--box-shadow);
        padding: 8px;
        overflow-y: auto;
    }

    @media screen and (max-width: 768px) {
        .dropdown2-submenu {
            position: unset;
            box-shadow: none;
            border-radius: 0;
            padding: 0;
            background-color: inherit;
            overflow-y: hidden;
            width: 100%;
            max-height: 0;
            transition: max-height 0.3s ease;
        }

        .dropdown2-submenu.open {
            max-height: 1000px;
        }
    }

    .dropdown2-item-parent-wrapper {
        position: relative;
    }

    .dropdown2-item-parent {
        grid-template-columns: auto 10px;
    }

    .dropdown2-item-child {
        grid-template-columns: 20px auto;
    }

    .dropdown2-item-child-lg {
        grid-template-columns: 40px auto;
    }

    .dropdown2-item {
        display: grid;
        gap: 8px;
        padding: 8px 16px;
        border-radius: var(--border-radius);
        transition: background-color 0.1s ease, color 0.1s ease;
        color: var(--text-heading);
        /*max-width: 250px;*/
        text-wrap: nowrap;
        cursor: pointer;
    }

    .dropdown2-item.active {
        background-color: var(--secondary-grey-hover);
        color: var(--text-primary-highlight);
    }

    .dropdown2-item > * {
        display: flex;
        flex-direction: row;
        align-items: center;
    }

    .dropdown2-item > .fa-solid {
        justify-content: center;
    }

    .dropdown-divider {
        border-top: 1px solid var(--border-grey);
        margin: 8px;
    }

    .dropdown2-mobile {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 999;
        height: 0px;
        display: flex;
        flex-direction: column;
        box-shadow: var(--box-shadow);
        background-color: var(--secondary-grey);
        color: var(--text-primary);
        padding: 0 8px 32px;
        border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
        transition: height 0.2s ease-in-out;
        overflow: hidden;
    }

    .dropdown2-mobile:focus {
        outline: none;
    }

    .dropdown2-mobile.open {
        height: 60%;
    }

    .dropdown2-mobile.full {
        height: 95% !important;
        overflow-y: auto;
    }

    .dropdown-mobile-handle {
        width: 40px;
        min-height: 4px;
        max-height: 4px;
        background-color: var(--icon-grey);
        border-radius: 2px;
        margin: 10px auto;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownTriggers = document.querySelectorAll('.dropdown-trigger');
        const dropdownMenus = document.querySelectorAll('.dropdown2-container');
        const mobileMenus = document.querySelectorAll('.dropdown2-mobile');
        const swipeThreshold = 30;
        const body = document.body;
        //const header = document.querySelector('header');

        let enteredSubmenu = false;
        let hoveringTimeout;
        let mouseInMenu = false;
        let touchStart;
        let touchEnd;

        // Open menu (trigger click/press)
        dropdownTriggers.forEach(trigger => {
            trigger.addEventListener('click', (event) => {
                if (checkIfMobile()) {
                    const menu = trigger.parentNode.querySelector('.dropdown2-mobile');
                    const transparentBg = trigger.parentNode.querySelector('.modal-transparent-container');
                    if (menu.classList.contains('open')) return;

                    event.stopPropagation();

                    body.classList.add('prevent-scroll')
                    transparentBg.classList.remove('hidden');
                    menu.classList.remove('hidden');

                    setTimeout(() => {
                        transparentBg.classList.add('show');
                        menu.classList.add('open');
                    }, 10); // Ensure the "display" property change has taken effect

                    menu.focus();
                } else {
                    const menu = trigger.parentNode.querySelector('.dropdown2-container');
                    const triggerRect = trigger.getBoundingClientRect();
                    const viewportHeight = window.innerHeight;
                    if (menu.classList.contains('open')) return;

                    event.stopPropagation();

                    /*if (triggerRect.top > viewportHeight / 2) {
                        menu.classList.remove('dropdown2-container-down');
                        menu.classList.add('dropdown2-container-up');
                    } else {
                        menu.classList.remove('dropdown2-container-up');
                        menu.classList.add('dropdown2-container-down');
                    }*/

                    menu.classList.add('dropdown2-container-down');
                    menu.classList.remove('hidden');

                    setTimeout(() => {
                        menu.classList.add('open');
                    }, 10); // Ensure the "display" property change has taken effect

                    menu.focus();
                }
            });
        });

        function closeMenu(menu) {
            if (checkIfMobile()) {
                const transparentBg = menu.parentNode.querySelector('.modal-transparent-container');

                const openSubmenu = menu.querySelector('.dropdown2-submenu.open');
                if (openSubmenu) {
                    const openMenuParent = openSubmenu.closest('.dropdown2-item-parent-wrapper');
                    toggleMobileSubmenu(openMenuParent);
                }

                transparentBg.classList.remove('show');
                menu.classList.remove('open', 'full');
                menu.blur();

                setTimeout(() => {
                    transparentBg.classList.add('hidden');
                    menu.classList.add('hidden');
                }, 200); // Match transition duration on .dropdown2-mobile

                body.classList.remove('prevent-scroll');
            } else {
                menu.classList.remove('open');
                menu.blur();

                setTimeout(() => {
                    menu.classList.add('hidden');
                }, 100); // Match transition duration on .dropdown2-container
            }
        }

        // Close menu (click outside)
        document.addEventListener('click', (event) => {
            if (checkIfMobile()) {
                const menu = document.querySelector('.dropdown2-mobile.open');
                if (!menu) return;
                if (menu.contains(event.target)) return;

                closeMenu(menu);
            } else {
                const menu = document.querySelector('.dropdown2-container.open');
                if (!menu) return;

                closeMenu(menu);
            }
        });

        // Close menu (ESC)
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                if (checkIfMobile()) {
                    const menu = document.querySelector('.dropdown2-mobile.open');
                    if (!menu) return;

                    closeMenu(menu);
                } else {
                    const menu = document.querySelector('.dropdown2-container.open');
                    if (!menu) return;

                    closeMenu(menu);
                }
            }
        });

        function openSubmenu(parent) {
            const submenu = parent.querySelector('.dropdown2-submenu');
            const viewportHeight = window.innerHeight;

            clearTimeout(hoveringTimeout);

            hoveringTimeout = setTimeout(() => {
                if (enteredSubmenu) return;
                if (!mouseInMenu) return;

                submenu.classList.remove('hidden');
                submenu.style.maxHeight = `${viewportHeight - 16}px`;

                const rect = submenu.getBoundingClientRect();
                if (rect.bottom > viewportHeight) {
                    const overflow = rect.bottom - viewportHeight + 16;
                    submenu.style.top = `-${overflow}px`;
                }

                submenu.focus();
            }, 250);
        }

        function closeSubmenu(parent) {
            const submenu = parent.querySelector('.dropdown2-submenu');
            const parentMenu = parent.closest('.dropdown2-container');

            setTimeout(() => {
                if (enteredSubmenu) return;
                submenu.classList.add('hidden');
                submenu.style = '';
            }, 250);

            parentMenu.focus();
        }

        function toggleMobileSubmenu(parent) {
            const submenu = parent.querySelector('.dropdown2-submenu');

            if (submenu.classList.contains('open')) {
                const parentMenu = parent.closest('.dropdown2-mobile');

                submenu.classList.remove('open');

                setTimeout(() => {
                    submenu.classList.add('hidden');
                }, 300); // Match transition duration on .dropdown2-submenu

                parentMenu.focus();
            } else {
                submenu.classList.remove('hidden');

                setTimeout(() => {
                    submenu.classList.add('open');
                }, 10); // Ensure the "display" property change has taken effect

                submenu.focus();
            }
        }

        document.querySelectorAll('.dropdown2-item-parent-wrapper').forEach(item => {
            // Toggle mobile submenu (parent menu item click)
            item.addEventListener('click', (event) => {
                if (!checkIfMobile()) return;

                const openMenu = document.querySelector('.dropdown2-submenu.open');
                if (openMenu) {
                    const openMenuParent = openMenu.closest('.dropdown2-item-parent-wrapper');
                    if (openMenuParent !== item) toggleMobileSubmenu(openMenuParent);
                }

                toggleMobileSubmenu(item);
            });

            // Open submenu (parent menu item mouse enter)
            item.addEventListener('mouseenter', () => {
                if (checkIfMobile()) return;
                openSubmenu(item);
            });

            // Close subumenu (parent menu item mouse leave)
            item.addEventListener('mouseleave', () => {
                if (checkIfMobile()) return;
                closeSubmenu(item);
            });
        });

        dropdownMenus.forEach(menu => {
            // Menu mouse enter
            menu.addEventListener('mouseenter', () => {
                mouseInMenu = true;
            });

            // Menu mouse leave
            menu.addEventListener('mouseleave', () => {
                mouseInMenu = false;
            });
        });

        mobileMenus.forEach(menu => {
            // Expand/collapse mobile menu (menu swipe)

            menu.addEventListener('touchstart', (event) => {
                touchStart = event.changedTouches[0].clientY;
            });

            menu.addEventListener('touchend', (event) => {
                touchEnd = event.changedTouches[0].clientY;
                const swipeDistance = Math.abs(touchEnd - touchStart);

                if (touchStart > touchEnd && swipeDistance > swipeThreshold) {
                    menu.classList.add('full');
                } else if (touchStart < touchEnd && swipeDistance > swipeThreshold && menu.scrollTop === 0) {
                    if (menu.classList.contains('full')) {
                        menu.classList.remove('full');
                    } else {
                        closeMenu(menu);
                    }
                }
            });
        });

        document.querySelectorAll('.dropdown2-submenu').forEach(submenu => {
            // Submenu mouse enter
            submenu.addEventListener('mouseenter', () => {
                enteredSubmenu = true;
                //body.classList.add('prevent-scroll', 'prevent-scroll-padding');
                //header.classList.add('prevent-scroll-padding');
            });

            // Submenu mouse leave
            submenu.addEventListener('mouseleave', () => {
                enteredSubmenu = false;
                //body.classList.remove('prevent-scroll', 'prevent-scroll-padding');
                //header.classList.remove('prevent-scroll-padding');
            });

            const submenuItems = submenu.querySelectorAll('.dropdown2-item');
            submenuItems.forEach(item => {
                // Close menu (submenu item click)
                item.addEventListener('click', () => {
                    const menu = item.closest('.dropdown2-mobile');
                    closeMenu(menu);
                });
            });
        });

        document.querySelectorAll('.dropdown2-item').forEach(item => {
            // Menu item mouse over
            item.addEventListener('mouseover', () => {
                item.classList.add('active');
            });

            // Menu item mouse leave
            item.addEventListener('mouseleave', () => {
                item.classList.remove('active');
            });
        });
    });
</script>
