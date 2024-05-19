import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import jQuery from 'jquery';
import select2 from 'select2';
import Dropzone from 'dropzone';

window.$ = jQuery;
select2();


// Constants
const sidebarWidth = "250px";
const autoCloseSidebarWidth = 1330;
const mobileWidth = 768;

// Document elements
const body = document.body;
const navbar = document.getElementById("navbar-content");
const headerWrapper = document.getElementById("header-wrapper");
const mainContentWrapper = document.getElementById("main-content-wrapper");
const sidebar = document.getElementById("sidebar");
const sidebarButton = document.getElementById("show-sidebar-btn");
const pinSidebarTooltip = document.getElementById("pin-sidebar-tooltip");
const pinSidebarBtn = document.getElementById("pin-sidebar-icon");
const header = document.getElementById("page-header");
const searchIcon = document.getElementById("search-icon");
const searchClose = document.getElementById("search-close");
const searchIconBtn = document.getElementById("search-icon-btn");
const searchInputContainer = document.getElementById("search-input-container");
const searchInput = document.getElementById("search-input");
const searchResults = document.getElementById("search-results");
const searchResultsList = document.getElementById("search-results-list");
const searchRecentExpenses = document.getElementById("search-recent-expenses");

// Mobile document element
const mobileNavbar = document.getElementById("mobile-navbar-content");
const mobileSearchWrapper = document.getElementById("mobile-search-wrapper");
const mobileSearchInput = document.getElementById("mobile-search-input");
const mobileSearchResultsList = document.getElementById("mobile-search-results-list");
const mobileSearchRecentExpenses = document.getElementById("mobile-search-recent-expenses");
const mobileSearchbarContainer = document.getElementById("mobile-searchbar-container");


// Load/set app theme


window.setTheme = function(element, themeToSet) {
    if (!availableThemes.includes(themeToSet)) {
        return;
    }
    localStorage.setItem('theme', themeToSet);
    availableThemes.forEach((theme) => {
        if (theme !== themeToSet && body.classList.contains(theme)) {
            body.classList.remove(theme)
        }
    });
    if (!body.classList.contains(themeToSet)) {
        body.classList.add(themeToSet);
    }

    const themeBtns = Array.from(element.parentNode.children);
    themeBtns.forEach(btn => {
        btn.classList.remove("theme-setting-active");
    });
    element.classList.add("theme-setting-active");
}

window.loadTheme = function() {
    const theme = window.localStorage.getItem('theme');
}


// Dropzone


Dropzone.autoDiscover = false;

const profileDropzoneElement = document.querySelector("#profile-img-form");

if (profileDropzoneElement) {
    const profileImgDropzoneElement = document.getElementById("profile-img-form");

    var previewNode = document.querySelector("#dropzone-preview-template");
    previewNode.id = "";
    var previewTemplate = previewNode.parentNode.innerHTML;
    previewNode.parentNode.removeChild(previewNode);

    let profileImgDropzone = new Dropzone("#profile-img-form", {
        autoProcessQueue: false,
        uploadMultiple: false,
        parallelUploads: 1,
        maxFiles: 1,
        maxFilesize: 5, // In MB
        previewsContainer: "#profile-img-previews",
        previewTemplate: previewTemplate,
        thumbnailWidth: 200,
        thumbnailHeight: 200,
        acceptedFiles: ".jpeg,.jpg,.png",

        removedfile: file => {
            const previewElement = file.previewElement;
            if (previewElement && previewElement.parentNode) {
                previewElement.classList.remove('dz-animating-expand');
                previewElement.classList.add('dz-animating-collapse');
                setTimeout(() => {
                    previewElement.parentNode.removeChild(previewElement);
                }, 500); // Duration of the collapseFadeOut animation
            }
        },
    });

    profileImgDropzone.on("addedfile", file => {
        console.log(`File added: ${file.name}`);
        dropzoneAddPreviewElement(file);
    });

    profileImgDropzone.on('dragover', function() {
        profileImgDropzoneElement.classList.add('dragover');
    });

    profileImgDropzone.on('dragleave', function() {
        profileImgDropzoneElement.classList.remove('dragover');
    });

    profileImgDropzone.on('drop', function() {
        profileImgDropzoneElement.classList.remove('dragover');
    });

    profileImgDropzone.on("uploadprogress", function(file, progress, bytesSent) {
        file.previewElement.querySelector("[data-dz-uploadprogress]").style.width = progress + "%";
    });

    profileImgDropzone.on("success", function(file, response) {
        console.log("Upload successful:", response);
        setTimeout(() => {
            if (response.success && response.redirect) {
                window.location.href = response.redirect;
            }
        }, 500);
    });

    profileImgDropzone.on("error", function(file, response, xhr) {
        console.error("Upload failed:", response);
        file.previewElement.querySelector(".dz-progress").classList.add('hidden');
        if (xhr) {
            let errorMessage = JSON.parse(xhr.responseText).message;
            file.previewElement.querySelector(".dz-file-error").textContent = errorMessage;
        } else {
            file.previewElement.querySelector(".dz-file-error").textContent = response;
        }

        file.previewElement.querySelector(".dz-file-error")
    });

    /**
     * Process the dropzone queue and upload to server
     */
    window.submitProfileImage = function() {
        profileImgDropzone.processQueue();
    }

    /**
     * Clear profile dropzone when modal is closed
     */
    window.clearProfileUploader = function() {
        setTimeout(() => {
            profileImgDropzone.removeAllFiles();
        }, 300);
    }
}

const expenseDropzoneElement = document.querySelector("#expense-img-form");

if (expenseDropzoneElement) {
    const expenseImgDropzoneElement = document.getElementById("expense-img-form");

    var previewNode = document.querySelector("#dropzone-preview-template");
    previewNode.id = "";
    var previewTemplate = previewNode.parentNode.innerHTML;
    previewNode.parentNode.removeChild(previewNode);

    let expenseImgDropzone = new Dropzone("#expense-img-form", {
        autoProcessQueue: false,
        uploadMultiple: true,
        parallelUploads: 5,
        maxFiles: 5,
        maxFilesize: 5, // In MB
        previewsContainer: "#expense-img-previews",
        previewTemplate: previewTemplate,
        thumbnailWidth: 200,
        thumbnailHeight: 200,
        acceptedFiles: ".jpeg,.jpg,.png",

        successmultiple: function(files, response) {
            setTimeout(() => {
                if (response.success && response.redirect) {
                    window.location.href = response.redirect;
                }
            }, 500);
        },

        removedfile: file => {
            const previewElement = file.previewElement;
            if (previewElement && previewElement.parentNode) {
                previewElement.classList.remove('dz-animating-expand');
                previewElement.classList.add('dz-animating-collapse');
                setTimeout(() => {
                    previewElement.parentNode.removeChild(previewElement);
                }, 500); // Duration of the collapseFadeOut animation
            }
        },
    });

    expenseImgDropzone.on("addedfile", file => {
        console.log(`File added: ${file.name}`);
        dropzoneAddPreviewElement(file);
    });

    expenseImgDropzone.on('dragover', function() {
        expenseImgDropzoneElement.classList.add('dragover');
    });

    expenseImgDropzone.on('dragleave', function() {
        expenseImgDropzoneElement.classList.remove('dragover');
    });

    expenseImgDropzone.on('drop', function() {
        expenseImgDropzoneElement.classList.remove('dragover');
    });

    expenseImgDropzone.on("uploadprogress", function(file, progress, bytesSent) {
        file.previewElement.querySelector("[data-dz-uploadprogress]").style.width = progress + "%";
    });

    expenseImgDropzone.on("success", function(file, response) {
        console.log("Upload successful:", response);
        setTimeout(() => {
            expenseImgDropzone.removeFile(file);
        }, 500);
    });

    expenseImgDropzone.on("error", function(file, response, xhr) {
        console.error("Upload failed:", response);
        file.previewElement.querySelector(".dz-progress").classList.add('hidden');
        if (xhr) {
            let errorMessage = JSON.parse(xhr.responseText).message;
            file.previewElement.querySelector(".dz-file-error").textContent = errorMessage;
        } else {
            file.previewElement.querySelector(".dz-file-error").textContent = response;
        }

        file.previewElement.querySelector(".dz-file-error")
    });

    /**
     * Process the dropzone queue and upload to server
     */
    window.submitExpenseImages = function() {
        expenseImgDropzone.processQueue();
    }

    /**
     * Clear profile dropzone when modal is closed
     */
    window.clearExpenseUploader = function() {
        setTimeout(() => {
            expenseImgDropzone.removeAllFiles();
        }, 300);
    }
}

const groupDropzoneElement = document.querySelector("#group-img-form");

if (groupDropzoneElement) {
    const groupImgDropzoneElement = document.getElementById("group-img-form");

    var previewNode = document.querySelector("#dropzone-preview-template");
    previewNode.id = "";
    var previewTemplate = previewNode.parentNode.innerHTML;
    previewNode.parentNode.removeChild(previewNode);

    let groupImgDropzone = new Dropzone("#group-img-form", {
        autoProcessQueue: false,
        uploadMultiple: false,
        parallelUploads: 1,
        maxFiles: 1,
        maxFilesize: 5, // In MB
        previewsContainer: "#group-img-previews",
        previewTemplate: previewTemplate,
        thumbnailWidth: 200,
        thumbnailHeight: 200,
        acceptedFiles: ".jpeg,.jpg,.png",

        removedfile: file => {
            const previewElement = file.previewElement;
            if (previewElement && previewElement.parentNode) {
                previewElement.classList.remove('dz-animating-expand');
                previewElement.classList.add('dz-animating-collapse');
                setTimeout(() => {
                    previewElement.parentNode.removeChild(previewElement);
                }, 500); // Duration of the collapseFadeOut animation
            }
        },
    });

    groupImgDropzone.on("addedfile", file => {
        console.log(`File added: ${file.name}`);
        dropzoneAddPreviewElement(file);
    });

    groupImgDropzone.on('dragover', function() {
        groupImgDropzoneElement.classList.add('dragover');
    });

    groupImgDropzone.on('dragleave', function() {
        groupImgDropzoneElement.classList.remove('dragover');
    });

    groupImgDropzone.on('drop', function() {
        groupImgDropzoneElement.classList.remove('dragover');
    });

    groupImgDropzone.on("uploadprogress", function(file, progress, bytesSent) {
        file.previewElement.querySelector("[data-dz-uploadprogress]").style.width = progress + "%";
    });

    groupImgDropzone.on("success", function(file, response) {
        console.log("Upload successful:", response);
        setTimeout(() => {
            if (response.success && response.redirect) {
                window.location.href = response.redirect;
            }
        }, 500);
    });

    groupImgDropzone.on("error", function(file, response, xhr) {
        console.error("Upload failed:", response);
        file.previewElement.querySelector(".dz-progress").classList.add('hidden');
        if (xhr) {
            let errorMessage = JSON.parse(xhr.responseText).message;
            file.previewElement.querySelector(".dz-file-error").textContent = errorMessage;
        } else {
            file.previewElement.querySelector(".dz-file-error").textContent = response;
        }

        file.previewElement.querySelector(".dz-file-error")
    });

    /**
     * Process the dropzone queue and upload to server
     */
    window.submitGroupImage = function() {
        groupImgDropzone.processQueue();
    }

    /**
     * Clear profile dropzone when modal is closed
     */
    window.clearGroupUploader = function() {
        setTimeout(() => {
            groupImgDropzone.removeAllFiles();
        }, 300);
    }
}

/**
 * Customize file.previewElement before it is added to the dropzone previews container
 */
window.dropzoneAddPreviewElement = function(file) {
    const previewElement = file.previewElement;

    if (previewElement) {
        const thumbnailElement = previewElement.querySelector('.dz-thumbnail');
        const iconElement = previewElement.querySelector('.dz-filetype-icon');

        if (file.type.match(/image.*/)) {
            thumbnailElement.classList.remove('hidden');
            iconElement.classList.add('hidden');
        } else {
            thumbnailElement.classList.add('hidden');
            iconElement.classList.remove('hidden');

            // Set the appropriate icon based on file type
            const fileType = file.name.split('.').pop().toLowerCase();
            switch (fileType) {
                case 'pdf':
                    iconElement.querySelector('i').className = 'fa-solid fa-file-pdf';
                    break;
                case 'doc':
                case 'docx':
                    iconElement.querySelector('i').className = 'fa-solid fa-file-word';
                    break;
                case 'xls':
                case 'xlsx':
                    iconElement.querySelector('i').className = 'fa-solid fa-file-excel';
                    break;
                case 'ppt':
                case 'pptx':
                    iconElement.querySelector('i').className = 'fa-solid fa-file-powerpoint';
                    break;
                case 'csv':
                    iconElement.querySelector('i').className = 'fa-solid fa-file-csv';
                    break;
                default:
                    iconElement.querySelector('i').className = 'fa-solid fa-file-lines';
                    break;
            }
        }

        previewElement.classList.add('dz-animating-expand');
        setTimeout(() => {
            previewElement.classList.remove('dz-animating-expand');
        }, 500);
    }
}


// Reusable


/**
 * Resize textarea to fit content (limited by max-height)
 */
window.resizeTextarea = function(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = (textarea.scrollHeight + 2) + 'px';
}

/**
 * Open link in current tab
 */
window.openLink = function(link) {
    window.location.href = link;
}

window.showValidationWarning = function(validationWarning) {
    validationWarning.classList.remove('animate-out');
    validationWarning.classList.remove('hidden');
}

/**
 * Close the validation warning attached to the button
 */
window.closeValidationWarning = function(hideBtn) {
    let validationWarning = hideBtn.closest('.validation-warning');

    validationWarning.classList.add('animate-out');
    setTimeout(() => {
        validationWarning.classList.add('hidden');
    }, 300);
}

/**
 * Close the validation warning
 */
window.hideValidationWarning = function(validationWarning) {
    validationWarning.classList.add('animate-out');
    setTimeout(() => {
        validationWarning.classList.add('hidden');
    }, 300);
}

/**
 * Close all validation warnings
 */
window.hideAllValidationWarnings = function() {
    let validationWarnings = document.querySelectorAll('.validation-warning');
    validationWarnings.forEach(function(validationWarning) {
        validationWarning.classList.add('animate-out');
        setTimeout(() => {
            validationWarning.classList.add('hidden');
        }, 300);
    });
}


// Responsiveness


window.checkIfMobile = function() {
    if (window.innerWidth <= mobileWidth) {
        return true;
    } else {
        return false;
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


window.saveSidebarState = function(isCollapsed) {
    localStorage.setItem('sidebarCollapsed', isCollapsed);
}

window.loadSidebarState = function() {
    const isCollapsed = localStorage.getItem('sidebarCollapsed');
    return isCollapsed === 'true'; // Return true if 'sidebarCollapsed' is 'true', otherwise false
}

var sidebarCollapsed = loadSidebarState();

window.autoCloseSidebar = function() {
    sidebar.classList.remove("sidebar-expanded");
    navbar.style.marginLeft = "0";
    if (headerWrapper) headerWrapper.style.marginLeft = "0";
    mainContentWrapper.style.marginLeft = "0";
    sidebarButton.classList.remove("hidden");
    pinSidebarBtn.classList.add("hidden");
}

window.autoOpenSidebar = function() {
    if (!sidebarCollapsed) {
        sidebar.classList.add("sidebar-expanded");
        navbar.style.marginLeft = sidebarWidth;
        if (headerWrapper) headerWrapper.style.marginLeft = sidebarWidth;
        mainContentWrapper.style.marginLeft = sidebarWidth;
        sidebarButton.classList.add("hidden");
    }
}

sidebarButton.addEventListener("mouseover", function(event) {
    if (sidebarCollapsed || window.innerWidth < autoCloseSidebarWidth) {
        sidebar.classList.add("sidebar-expanded");
        sidebarButton.classList.add("hidden");
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
        sidebarButton.classList.remove("hidden");
    }
    pinSidebarBtn.classList.add("hidden");
});

window.toggleSidebar = function() {
    if (sidebarCollapsed) {
        sidebar.classList.add("sidebar-expanded");
        navbar.style.marginLeft = sidebarWidth;
        if (headerWrapper) headerWrapper.style.marginLeft = sidebarWidth;
        mainContentWrapper.style.marginLeft = sidebarWidth;
        pinSidebarTooltip.innerHTML = "Unpin Sidebar";
        sidebarButton.classList.add("hidden");
    } else {
        navbar.style.marginLeft = "0";
        if (headerWrapper) headerWrapper.style.marginLeft = "0";
        mainContentWrapper.style.marginLeft = "0";
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
    body.classList.add("overflow-y-hidden");
    mobileSearchInput.value = "";
    mobileSearchWrapper.classList.add("mobile-search-wrapper-active");
    mobileSearchResultsList.classList.remove("mobile-search-results-list-active")
    mobileSearchRecentExpenses.classList.remove("hidden");
    mobileSearchInput.focus();
}

window.closeMobileSearch = function() {
    body.classList.remove("overflow-y-hidden");
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





// Views





// Expenses

