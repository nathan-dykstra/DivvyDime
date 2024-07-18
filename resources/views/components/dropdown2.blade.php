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
    <div class="dropdown-trigger">
        {{ $trigger }}
    </div>

    <div class="dropdown2-container {{ $alignment }}" tabindex="0">
        <div class="dropdown-menu">
            {{ $content }}
        </div>
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
        display: none;
    }

    .dropdown2-container.open {
        opacity: 1;
        transform: scale(1);
        transition: opacity 100ms ease-in, transform 100ms ease-in;
    }

    .dropdown2-container.show {
        display: block;
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
        max-width: 250px;
        text-wrap: nowrap;
        cursor: pointer;
    }

    .dropdown2-item.active {
        background-color: var(--secondary-grey-hover);
        color: var(--text-primary-highlight);
    }

    /*.dropdown2-item:focus-visible {
        outline: 3px solid var(--blue-text);
        outline-offset: 1px;
    }*/

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
</style>

<script>
    /*function mod(n, m) {
        return ((n % m) + m) % m;
    }*/

    document.addEventListener('DOMContentLoaded', function() {
        let enteredSubmenu = false;
        //let enteredSubmenuFromKeyboard = false;
        //let submenuOpen = false;
        let hoveringTimeout;
        let mouseInMenu = false;
        //let activeIndex = -1;
        //let activeSubIndex = -1;

        const dropdownTriggers = document.querySelectorAll('.dropdown-trigger');
        const dropdownMenus = document.querySelectorAll('.dropdown2-container');
        const body = document.body;
        const header = document.querySelector('header');

        function closeAllDropdowns() {
            dropdownMenus.forEach(menu => {
                menu.classList.remove('open');
                setTimeout(() => {
                    menu.classList.remove('show');
                }, 100); // Match transition duration on .dropdown2-container

                //const menuItems = menu.querySelectorAll('.dropdown2-item:not(.dropdown2-item-child):not(.dropdown2-item-child-lg)');
                //if (activeIndex >= 0) menuItems[activeIndex].classList.remove('active');
            });

            //activeIndex = -1;
            //activeSubIndex = -1;

            //enteredSubmenuFromKeyboard = false;
            //submenuOpen = false;

            //body.classList.remove('prevent-scroll', 'prevent-scroll-padding');
            //header.classList.remove('prevent-scroll-padding');
        }

        function dropdownIsOpen() {
            let open = false;

            dropdownMenus.forEach(menu => {
                if (menu.classList.contains('show')) open = true;
            });

            return open;
        }

        function openSubmenu(parent) {
            const submenu = parent.querySelector('.dropdown2-submenu');
            const viewportHeight = window.innerHeight;

            clearTimeout(hoveringTimeout);

            /*if (enteredSubmenuFromKeyboard) {
                submenu.classList.remove('hidden');
                submenu.style.maxHeight = `${viewportHeight - 16}px`;

                const rect = submenu.getBoundingClientRect();
                if (rect.bottom > viewportHeight) {
                    const overflow = rect.bottom - viewportHeight + 16;
                    submenu.style.top = `-${overflow}px`;
                }

                submenuOpen = true;
                submenu.focus();
            } else {*/
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

                    //submenuOpen = true;
                submenu.focus();
                }, 250);
            //}
        }

        function closeSubmenu(parent) {
            const submenu = parent.querySelector('.dropdown2-submenu');
            const parentMenu = parent.closest('.dropdown2-container');

            /*if (enteredSubmenuFromKeyboard) {
                submenu.classList.add('hidden');
                submenu.style = '';
            } else {*/
            setTimeout(() => {
                if (enteredSubmenu) return;
                submenu.classList.add('hidden');
                submenu.style = '';
            }, 250);
            //}

            //activeSubIndex = -1;
            //enteredSubmenuFromKeyboard = false;
            //submenuOpen = false;

            parentMenu.focus();
        }

        /*function setActive(index, menu) {
            const menuItems = menu.querySelectorAll('.dropdown2-item:not(.dropdown2-item-child):not(.dropdown2-item-child-lg)');
            if (activeIndex !== -1) {
                menuItems[activeIndex].classList.remove('active');
            }
            activeIndex = index;
            menuItems[activeIndex].classList.add('active');
        }*/

        /*function setSubActive(index, menu) {
            menuItems = menu.querySelectorAll('.dropdown2-item');
            if (activeSubIndex !== -1) {
                menuItems[activeSubIndex].classList.remove('active');
            }
            activeSubIndex = index;
            menuItems[activeSubIndex].classList.add('active');
        }*/

        dropdownTriggers.forEach(trigger => {
            // Menu trigger click
            trigger.addEventListener('click', (event) => {
                const menu = trigger.parentNode.querySelector('.dropdown2-container');
                const triggerRect = trigger.getBoundingClientRect();
                const viewportHeight = window.innerHeight;

                if (menu.classList.contains('show')) return;

                event.stopPropagation();

                //body.classList.add('prevent-scroll', 'prevent-scroll-padding');
                //header.classList.add('prevent-scroll-padding');

                /*if (triggerRect.top > viewportHeight / 2) {
                    menu.classList.remove('dropdown2-container-down');
                    menu.classList.add('dropdown2-container-up');
                } else {
                    menu.classList.remove('dropdown2-container-up');
                    menu.classList.add('dropdown2-container-down');
                }*/
                menu.classList.add('dropdown2-container-down');
                menu.classList.add('show');
                setTimeout(() => {
                    menu.classList.add('open');
                }, 10); // Ensure the "display" property change has taken effect

                menu.focus();
                //setActive(0, menu);
            });
        });

        document.addEventListener('click', () => {
            if (dropdownIsOpen()) closeAllDropdowns();
        })

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeAllDropdowns();
        })

        document.querySelectorAll('.dropdown2-item-parent-wrapper').forEach(item => {
            // Menu parent mouse enter
            item.addEventListener('mouseenter', () => {
                openSubmenu(item);
            });

            // Menu parent mouse leave
            item.addEventListener('mouseleave', () => {
                closeSubmenu(item);
            });
        });

        document.querySelectorAll('.dropdown2-submenu').forEach(submenu => {
            // Submenu mouse enter
            submenu.addEventListener('mouseenter', () => {
                enteredSubmenu = true;
                body.classList.add('prevent-scroll', 'prevent-scroll-padding');
                header.classList.add('prevent-scroll-padding');
            });

            // Submenu mouse leave
            submenu.addEventListener('mouseleave', () => {
                enteredSubmenu = false;
                body.classList.remove('prevent-scroll', 'prevent-scroll-padding');
                header.classList.remove('prevent-scroll-padding');
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

            /*const menuItems = menu.querySelectorAll('.dropdown2-item:not(.dropdown2-item-child):not(.dropdown2-item-child-lg)');

            menu.addEventListener('keydown', (event) => {
                event.preventDefault();
                const itemIsActive = (submenuOpen && activeSubIndex >= 0) || (!submenuOpen && activeIndex >= 0);

                const menuItem = itemIsActive ? menuItems[activeIndex] : null;
                const submenu = itemIsActive ? menuItem.parentNode.querySelector('.dropdown2-submenu'): null;
                const submenuItems = itemIsActive ? submenu.querySelectorAll('.dropdown2-item'): null;

                if (event.key === 'ArrowDown') {
                    if (submenuOpen) {
                        setSubActive(mod(activeSubIndex + 1, submenuItems.length), submenu);
                    } else {
                        setActive(mod(activeIndex + 1, menuItems.length), menu);
                    }
                } else if (event.key === 'ArrowUp') {
                    if (submenuOpen) {
                        setSubActive(mod(activeSubIndex - 1, submenuItems.length), submenu);
                    } else {
                        setActive(mod(activeIndex - 1, menuItems.length), menu);
                    }
                } else if (event.key === 'Enter' && itemIsActive) {
                    if (menuItem.classList.contains('dropdown2-item-parent')) {
                        enteredSubmenuFromKeyboard = true;
                        openSubmenu(menuItem.parentNode);
                    }
                } else if (event.key === 'Escape' && enteredSubmenuFromKeyboard) {
                    if (menuItem.classList.contains('dropdown2-item-parent')) {
                        event.stopPropagation();
                        closeSubmenu(menuItem.parentNode);
                    }
                }
            })*/
        });

        document.querySelectorAll('.dropdown2-item').forEach(item => {
            item.addEventListener('mouseover', () => {
                item.classList.add('active');
            });

            item.addEventListener('mouseleave', () => {
                item.classList.remove('active');
            });
        })
    });
</script>
