import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


// Constants
const sidebarWidth = "250px";
const autoCloseSidebarWidth = 1330;
const mobileWidth = 768;

// App
const app = document.getElementById("app-wrapper");
const mainContent = document.getElementById("main-content");
const body = document.body;

// Sidebar
const sidebar = document.getElementById("sidebar");
const sidebarButton = document.getElementById("show-sidebar-btn");
const pinSidebarTooltip = document.getElementById("pin-sidebar-tooltip");
const pinSidebarBtn = document.getElementById("pin-sidebar-icon");
const sidebarItems = document.getElementById("sidebar-items");

// Navbar
const navbar = document.getElementById("navbar-content");

// Header
const header = document.getElementById("page-header");
const headerContent = document.getElementById("header-content");
const headerWrapper = document.getElementById("header-content-wrapper");

// Search
const searchIcon = document.getElementById("search-icon");
const searchClose = document.getElementById("search-close");
const searchIconBtn = document.getElementById("search-icon-btn");
const searchInputContainer = document.getElementById("search-input-container");
const searchInput = document.getElementById("search-input");
const searchResults = document.getElementById("search-results");
const searchResultsList = document.getElementById("search-results-list");
const searchRecentExpenses = document.getElementById("search-recent-expenses");


// Mobile Constants

// App

// Sidebar

// Navbar
const mobileNavbar = document.getElementById("mobile-navbar-content");
const mobileProfileBtn = document.getElementById("mobile-profile-btn");
const mobileLogOutBtn = document.getElementById("mobile-log-out-btn");

// Header

// Search
const mobileSearchWrapper = document.getElementById("mobile-search-wrapper");
const mobileSearchIcon = document.getElementById("mobile-search-icon");
const mobileSearchClose = document.getElementById("mobile-search-close");
const mobileSearchIconBtn = document.getElementById("mobile-search-icon-btn");
const mobileSearchInputContainer = document.getElementById("mobile-search-input-container");
const mobileSearchInput = document.getElementById("mobile-search-input");
const mobileSearchResults = document.getElementById("mobile-search-results");
const mobileSearchResultsList = document.getElementById("mobile-search-results-list");
const mobileSearchRecentExpenses = document.getElementById("mobile-search-recent-expenses");
const mobileSearchbarContainer = document.getElementById("mobile-searchbar-container");


// Web responsiveness


// Save sidebar state to local storage
window.saveSidebarState = function(isCollapsed) {
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

// Retrieve sidebar state from local storage
window.loadSidebarState = function() {
    const isCollapsed = localStorage.getItem('sidebarCollapsed');
    return isCollapsed === 'true'; // Return true if 'sidebarCollapsed' is 'true', otherwise false
}

var sidebarCollapsed = loadSidebarState();

window.checkIfMobile = function() {
    if (window.innerWidth <= 768) {
        return true;
    } else {
        return false;
    }
}

window.autoCloseSidebar = function() {
    console.log("Sidebar closed automatically\n");

    sidebar.classList.remove("sidebar-expanded");
    navbar.style.marginLeft = "0";
    headerWrapper.style.marginLeft = "0";
    app.style.marginLeft = "0";
    sidebarButton.style.display = "flex";
    pinSidebarBtn.classList.add("hidden");
}

window.autoOpenSidebar = function() {
    console.log("Sidebar opened automatically\n");

    if (!sidebarCollapsed) {
        sidebar.classList.add("sidebar-expanded");
        navbar.style.marginLeft = sidebarWidth;
        headerWrapper.style.marginLeft = sidebarWidth;
        app.style.marginLeft = sidebarWidth;
        sidebarButton.style.display = "none";
    }
}

window.adjustSearchResultsHeight = function() {
    if (searchResults.classList.contains("search-results-active")) {
        const windowHeight = window.innerHeight;
        searchResults.style.maxHeight = `calc(${windowHeight}px - 100px)`
    }
}

window.addEventListener("resize", function() {
    if (!checkIfMobile()) {
        adjustSearchResultsHeight();
    }

    if (window.innerWidth < autoCloseSidebarWidth) {
        autoCloseSidebar();
    } else  {
        autoOpenSidebar();
    }
})


// Sidebar


// Set initial sidebar state
console.log("Initial sidebar collapsed: " + sidebarCollapsed);

if (checkIfMobile()) {
    // No need to adjust sidebar when it's hidden for mobile
} else if(window.innerWidth < autoCloseSidebarWidth) {
    autoCloseSidebar();
} else if (sidebarCollapsed === 'true' || sidebarCollapsed === true) {
    sidebar.classList.remove("sidebar-expanded");
    navbar.style.marginLeft = "0";
    headerWrapper.style.marginLeft = "0";
    app.style.marginLeft = "0";
    sidebarButton.style.display = "flex";
    pinSidebarTooltip.innerHTML = "Pin Sidebar";
} else {
    sidebar.classList.add("sidebar-expanded");
    navbar.style.marginLeft = sidebarWidth;
    headerWrapper.style.marginLeft = sidebarWidth;
    app.style.marginLeft = sidebarWidth;
    sidebarButton.style.display = "none";
    pinSidebarTooltip.innerHTML = "Unpin Sidebar";
}

sidebarButton.addEventListener("mouseover", function(event) {
    if (sidebarCollapsed || window.innerWidth < autoCloseSidebarWidth) {
        sidebar.classList.add("sidebar-expanded");
        sidebarButton.style.display = "none";
    }
});

sidebar.addEventListener("mouseover", function(event) {
    if (window.innerWidth >= autoCloseSidebarWidth) {
        pinSidebarBtn.classList.remove("hidden");
    }
})

sidebar.addEventListener("mouseleave", function(event) {
    if (sidebarCollapsed || window.innerWidth < autoCloseSidebarWidth) {
        sidebar.classList.remove("sidebar-expanded");
        sidebarButton.style.display = "flex";
    }
    pinSidebarBtn.classList.add("hidden");
});

window.pinSidebar = function() {
    console.log("Sidebar collapsed: " + sidebarCollapsed + " -> " + !sidebarCollapsed + "\n");

    if (sidebarCollapsed) {
        sidebar.classList.add("sidebar-expanded");
        navbar.style.marginLeft = sidebarWidth;
        headerWrapper.style.marginLeft = sidebarWidth;
        app.style.marginLeft = sidebarWidth;
        pinSidebarTooltip.innerHTML = "Unpin Sidebar";
    } else {
        navbar.style.marginLeft = "0";
        headerWrapper.style.marginLeft = "0";
        app.style.marginLeft = "0";
        pinSidebarTooltip.innerHTML = "Pin Sidebar";
    }

    sidebarCollapsed = !sidebarCollapsed;
    saveSidebarState(sidebarCollapsed);
}

window.animateSidebarIcon = function() {
    let icon = document.getElementById("pin-sidebar-icon");
    icon.style.animation = "scaleSidebarIcon 0.3s";
    setTimeout(() => {
        icon.style.animation = '';
    }, 300);
}


// Search


window.openMobileSearch = function() {
    body.classList.add("prevent-scroll");
    mobileSearchInput.value = "";
    mobileSearchWrapper.classList.add("mobile-search-wrapper-active");
    mobileSearchResultsList.classList.remove("mobile-search-results-list-active")
    mobileSearchRecentExpenses.classList.remove("hidden");
    mobileSearchInput.focus();
}

window.closeMobileSearch = function() {
    body.classList.remove("prevent-scroll");
    mobileSearchWrapper.classList.remove("mobile-search-wrapper-active");
    mobileSearchInput.blur();
}

mobileSearchInput.addEventListener("input", function(event) {
    let value = event.target.value;

    if (value !== "") {
        mobileSearchResultsList.classList.add("mobile-search-results-list-active")
        mobileSearchRecentExpenses.classList.add("hidden");
    } else {
        mobileSearchResultsList.classList.remove("mobile-search-results-list-active")
        mobileSearchRecentExpenses.classList.remove("hidden");
    }
})

mobileSearchWrapper.addEventListener("scroll", function() {
    if (mobileSearchWrapper.scrollTop > 0) {
        mobileSearchbarContainer.classList.add("mobile-searchbar-container-scrolling");
    } else {
        mobileSearchbarContainer.classList.remove("mobile-searchbar-container-scrolling");
    }
});

window.expandSearchbar = function() {
    adjustSearchResultsHeight();
    searchInput.value = "";
    body.classList.add("prevent-scroll");
    searchResultsList.classList.remove("search-results-list-active")
    searchRecentExpenses.classList.remove("search-recent-expenses-inactive");
    navbar.classList.add("navbar-content-solid");
    header.classList.remove("header-content-scrolling");
    searchInputContainer.classList.add("search-input-active");
    searchIconBtn.classList.add("search-icon-active");
    searchIcon.classList.add("search-icon-disabled");
    searchResults.classList.add("search-results-active");
    searchClose.classList.add("search-close-active");
    body.classList.add("prevent-scroll");
    searchInput.focus();
}

window.closeSearchbar = function() {
    body.classList.remove("prevent-scroll");
    if (window.scrollY > 0) {
        navbar.classList.remove("navbar-content-solid");
        header.classList.add("header-content-scrolling");
    }
    searchInputContainer.classList.remove("search-input-active");
    searchIconBtn.classList.remove("search-icon-active");
    searchResults.classList.remove("search-results-active");
    searchClose.classList.remove("search-close-active");
    searchIcon.classList.remove("search-icon-disabled");
    body.classList.remove("prevent-scroll");
    searchInput.blur();
}

window.animateSearchIcon = function(icon) {
    icon.style.animation = "scaleSearchIcon 0.3s";
    setTimeout(() => {
        icon.style.animation = '';
    }, 300);
}

document.addEventListener("keydown", function(event) {
    if (event.key === "Escape") {
        if (checkIfMobile()) {
            closeMobileSearch();
        } else {
            closeSearchbar();
        }
    }

    if (event.ctrlKey && event.shiftKey && event.key === "S") {
        if (checkIfMobile()) {
            openMobileSearch();
        } else {
            expandSearchbar();
        }
    }
});

document.addEventListener("click", function(event) {
    if (!checkIfMobile()) {
        const isClickedSearch = searchInputContainer.contains(event.target);
        const isClickedIcon = searchIconBtn.contains(event.target);
        const isClickedResults = searchResults.contains(event.target);
    
        if (!isClickedSearch && !isClickedIcon && !isClickedResults) {
            closeSearchbar();
        }
    }
});

searchInput.addEventListener("input", function(event) {
    let value = event.target.value;

    if (!checkIfMobile()) {
        adjustSearchResultsHeight();

        if (value !== "") {
            searchResultsList.classList.add("search-results-list-active")
            searchRecentExpenses.classList.add("search-recent-expenses-inactive");
        } else {
            searchResultsList.classList.remove("search-results-list-active")
            searchRecentExpenses.classList.remove("search-recent-expenses-inactive");
        }
    }
})


// Navbar


window.addEventListener("scroll", function() {
    if (checkIfMobile()) {
        if (window.scrollY > 0) {
            mobileNavbar.classList.remove("navbar-content-solid");
        } else {
            mobileNavbar.classList.add("navbar-content-solid");
            header.classList.remove("header-content-scrolling");
        }
    } else {
        if (window.scrollY > 0) {
            navbar.classList.remove("navbar-content-solid");
    
            // Mobile
            mobileNavbar.classList.remove("navbar-content-solid");
        } else {
            navbar.classList.add("navbar-content-solid");
            header.classList.remove("header-content-scrolling");
        }
    }
    
});


// Page Header


let lastScrollTop = 0;

window.addEventListener("scroll", function() {
    const currentScroll = window.scrollY;

    if (currentScroll > lastScrollTop) {
        // Scrolling down
        header.classList.add("header-content-hidden");
        header.classList.remove("header-content-scrolling");
    } else {
        // Scrolling up
        header.classList.remove("header-content-hidden");
        if (currentScroll !== 0) {
            header.classList.add("header-content-scrolling");
        }
    }

    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll; // For Mobile or negative scrolling
});
