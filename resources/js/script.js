/* ================================================= */
/* === MAIN EXECUTION BLOCK (DOMContentLoaded) === */
/* ================================================= */

document.addEventListener('DOMContentLoaded', function() {
    const isVueAdmin = document.getElementById('app-container')?.dataset?.vueAdmin === 'true';
    
    /* -------------------------------------- */
    /* --- General Site Functionality --- */
    /* -------------------------------------- */
    
    // Call navigation/scroll handlers if they exist
    if (typeof handlePublicNavigation === 'function') handlePublicNavigation();
    if (typeof handleSmoothScroll === 'function') handleSmoothScroll();

    /* -------------------------------------- */
    /* --- Module-Specific Integrations --- */
    /* -------------------------------------- */

    // Admin Dashboard
    if (!isVueAdmin && typeof handleAdminSidebarToggle === 'function') handleAdminSidebarToggle();
    
    // Public Pages
    if (typeof handleLoginFormValidation === 'function') handleLoginFormValidation();
    if (typeof handlePublicAdoptionPage === 'function') handlePublicAdoptionPage(); 
    if (typeof handleHomePageFeatures === 'function') handleHomePageFeatures(); 

    // Notifications toggle (admin header)
    const notifToggle = document.getElementById('notification-toggle');
    const notifPanel = document.getElementById('notification-panel');
    const notifCount = document.getElementById('notification-count');
    const notifList = document.getElementById('notification-list');
    if (!isVueAdmin && notifToggle && notifPanel) {
        notifToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            notifPanel.classList.toggle('hidden');
        });
        document.addEventListener('click', (e) => {
            if (!notifPanel.classList.contains('hidden') && !notifPanel.contains(e.target) && !notifToggle.contains(e.target)) {
                notifPanel.classList.add('hidden');
            }
        });
        if (notifList && notifCount) {
            const readKeys = JSON.parse(sessionStorage.getItem('adminNotifRead') || '[]');
            notifList.querySelectorAll('.notification-item').forEach(item => {
                const key = item.dataset.key;
                if (key && readKeys.includes(key)) {
                    item.remove();
                }
            });
            const remaining = notifList.querySelectorAll('.notification-item').length;
            if (remaining > 0) {
                notifCount.textContent = remaining;
            } else {
                notifCount.remove();
                notifList.innerHTML = '<p class="text-gray-600">No new notifications.</p>';
            }

            notifList.addEventListener('click', (e) => {
                const item = e.target.closest('.notification-item');
                if (!item) return;
                e.preventDefault();
                const href = item.getAttribute('href');
                const key = item.dataset.key;
                const current = parseInt(notifCount.textContent || '0', 10);
                if (current > 0) {
                    const next = current - 1;
                    if (next > 0) {
                        notifCount.textContent = next;
                    } else {
                        notifCount.remove();
                    }
                }
                item.remove();
                if (key) {
                    const updated = Array.from(new Set([...(readKeys || []), key]));
                    sessionStorage.setItem('adminNotifRead', JSON.stringify(updated));
                }
                if (!notifList.querySelector('.notification-item')) {
                    notifList.innerHTML = '<p class="text-gray-600">No new notifications.</p>';
                }
                if (href) {
                    setTimeout(() => window.location.href = href, 50);
                }
            });
        }
    }

    // Profile dropdown toggle (admin header)
    const profileToggle = document.getElementById('profile-toggle');
    const profilePanel = document.getElementById('profile-panel');
    if (!isVueAdmin && profileToggle && profilePanel) {
        const setProfileState = (isOpen) => {
            profilePanel.classList.toggle('hidden', !isOpen);
            profileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        };
        profileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = profilePanel.classList.contains('hidden');
            setProfileState(isOpen);
        });
        document.addEventListener('click', (e) => {
            if (!profilePanel.classList.contains('hidden') && !profilePanel.contains(e.target) && !profileToggle.contains(e.target)) {
                setProfileState(false);
            }
        });
    }

    // Sidebar toggle (mobile)
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    if (!isVueAdmin && sidebar && mainContent && sidebarToggle) {
        const closeSidebar = () => {
            sidebar.classList.remove('open', 'translate-x-0');
            sidebar.classList.add('-translate-x-full');
            mainContent.classList.remove('open');
        };

        const openSidebar = () => {
            sidebar.classList.add('open', 'translate-x-0');
            sidebar.classList.remove('-translate-x-full');
            mainContent.classList.add('open');
        };

        // Start closed on load for all screen sizes
        closeSidebar();

        sidebarToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            if (sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                closeSidebar();
            }
        });
    }

    /* -------------------------------------- */
    /* --- Modal Management (Universal) --- */
    /* -------------------------------------- */

    const modal = document.getElementById('animal-modal');
    const body = document.body;

    if (modal) {
        const closeModal = () => {
            modal.style.display = 'none';
            body.classList.remove('modal-open');
        };

        const openModal = () => {
            modal.style.display = 'block';
            body.classList.add('modal-open');
        };

        modal.addEventListener('click', (event) => {
            // Check if click is on the close button, close class, or the modal backdrop itself
            if (event.target.classList.contains('close') || 
                event.target.classList.contains('close-modal-btn') || 
                event.target === modal) {
                closeModal();
            }
        });

        // Expose modal functions globally for use in click events on animal cards
        window.closeModal = closeModal;
        window.openModal = openModal;
    }
});


/* ================================================= */
/* === ADMIN DASHBOARD FUNCTIONALITY === */
/* ================================================= */

/**
 * Handles the logic for the Admin Dashboard sidebar toggle,
 * including applying the smooth transition classes and saving state via localStorage.
 */
function handleAdminSidebarToggle() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    const toggleButton = document.getElementById('sidebar-toggle'); 

    if (!toggleButton || !sidebar || !mainContent) return;

    const toggleSidebar = () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('shifted');

        // Save state to local storage
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    };

    toggleButton.addEventListener('click', toggleSidebar);

    // Load the saved state on page load
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true') {
         sidebar.classList.add('collapsed');
         mainContent.classList.add('shifted');
    }
}


/* ================================================= */
/* === PUBLIC PAGE / GLOBAL HELPER FUNCTIONS === */
/* ================================================= */

/**
 * Helper function to map integer age to filter categories.
 * @param {number} age - The animal's age in years.
 * @returns {string} The age category tag.
 */
function getAgeCategory(age) {
    if (age < 1) return 'baby';
    if (age >= 1 && age < 3) return 'young';
    if (age >= 3 && age < 8) return 'adult';
    return 'senior';
}

/**
 * Renders a single animal card HTML.
 * NOTE: Changed onclick to use data-id and event delegation in Adoption Page logic.
 * @param {object} animal - The animal data object.
 * @returns {string} HTML string for the animal card.
 */
function renderAnimalCard(animal) {
    return `
        <div class="animal-card clickable-card" data-id="${animal.id}">
            <img src="/storage/${animal.image_path}" alt="${animal.name}">
            <div class="animal-info">
                <h3>${animal.name}</h3>
                <p>Age: ${animal.age} years | ${animal.species}</p>
            </div>
        </div>
    `;
}

/**
 * Helper function to display content in the modal and open it.
 * @param {string} content - The HTML content to display in the modal.
 */
function showModal(content) {
    const modalBody = document.getElementById('modal-body');
    if (modalBody) {
        modalBody.innerHTML = content;
        window.openModal();
    }
}

/**
 * Validates the main adoption form fields (used inside the modal).
 * @returns {boolean} True if all validations pass, false otherwise.
 */
function validateAdoptionForm() {
    // NOTE: Ensure these IDs exist in the showAdoptionForm template
    const fullName = document.getElementById('full_name')?.value.trim();
    const email = document.getElementById('email')?.value.trim();
    const phone = document.getElementById('phone')?.value.trim();
    const message = document.getElementById('message')?.value.trim();

    let isValid = true;
    let errorMessage = '';

    // Validate full name
    if (fullName && fullName.length < 2) {
        isValid = false;
        errorMessage += 'Full name must be at least 2 characters long.\n';
    }

    // Validate email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email && !emailRegex.test(email)) {
        isValid = false;
        errorMessage += 'Please enter a valid email address.\n';
    }

    // Validate phone (Philippine format)
    // Removed strict regex from JS validation since HTML pattern attribute handles it
    const phoneRegex = /^(\+63\s?|0)9\d{2}\s?\d{3}\s?\d{4}$/; 
    if (phone && !phoneRegex.test(phone)) {
        isValid = false;
        errorMessage += 'Please enter a valid Philippine phone number (e.g., +63 917 123 4567 or 0917 123 4567).\n';
    }

    // Validate message
    if (message && message.length < 10) {
        isValid = false;
        errorMessage += 'Please provide a message with at least 10 characters.\n';
    }

    if (!isValid) {
        alert('Please correct the following errors:\n\n' + errorMessage);
    }

    return isValid;
}

/**
 * Function to handle card click events, fetching and displaying animal details in the modal.
 * @param {number} animalId - The ID of the animal to display.
 */
window.showAnimalDetail = function(animalId) {
    // NOTE: Assumes global data is available: window.featuredPetsData or window.petsData
    const dataSet = window.petsData || window.featuredPetsData || [];
    const animal = dataSet.find(a => a.id == animalId);
    if (!animal) return;

    let authButtonHtml = '';
    // NOTE: window.isAuthenticated and window.loginRoute must be defined in the main Blade file
    if (window.isAuthenticated) {
        const baseRoute = window.adoptRoute || '/adopt';
        authButtonHtml = `<a href="${baseRoute}/${animal.id}" class="btn btn-primary">Interested in Adopting Me?</a>`;
    } else {
      authButtonHtml = `
            <div class="login-prompt-clean">
                <p>To adopt this animal, please log in first.</p>
                <a href="${window.loginRoute || '/login'}" class="btn btn-primary btn-login-adopt">Login to Adopt</a>
            </div>`;
    }

    const content = `
        <div class="animal-detail">
            <button class="close-modal-btn close" title="Close">&times;</button>
            <div class="animal-detail-header">
                <img src="/storage/${animal.image_path}" alt="${animal.name}">
                <div class="animal-detail-info">
                    <h2>${animal.name}</h2>
                    <div class="animal-tags">
                        <span class="tag tag-species">${animal.species}</span>
                        <span class="tag tag-age">${getAgeCategory(animal.age)}</span>
                        <span class="tag tag-size">${animal.size}</span>
                        <span class="tag tag-gender">${animal.gender}</span>
                    </div>
                    <p class="description">${animal.description || 'No description available. Adopt me to find out more!'}</p>
                </div>
            </div>
            <div class="animal-detail-actions">
                ${authButtonHtml}
            </div>
        </div>
    `;
    showModal(content);
};

window.showAdoptionForm = function(animalId) {
    const baseRoute = window.adoptRoute || '/adopt';
    window.location.href = `${baseRoute}/${animalId}`;
};

/* ================================================= */
/* === LOGIN FORM VALIDATION LOGIC === */
/* ================================================= */

function handleLoginFormValidation() {
    const loginForm = document.querySelector('.clean-login-form');
    
    if (!loginForm) return;

    loginForm.addEventListener('submit', function (event) {
        let isValid = true;
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        if (emailInput && !emailInput.value.trim()) {
            displayError(emailInput, 'Email address is required.');
            isValid = false;
        } else {
            clearError(emailInput);
        }

        if (passwordInput && passwordInput.value.length < 6) {
            displayError(passwordInput, 'Password must be at least 6 characters.');
            isValid = false;
        } else {
            clearError(passwordInput);
        }
        
        if (!isValid) {
            event.preventDefault();
        }
    });

    // --- Helper Functions for UI Feedback (Local to this module) ---
    
    function displayError(inputElement, message) {
        if (!inputElement) return;

        let errorDiv = inputElement.nextElementSibling;
        if (!errorDiv || !errorDiv.classList.contains('input-error-message')) {
            errorDiv = document.createElement('div');
            errorDiv.classList.add('input-error-message');
            inputElement.parentNode.insertBefore(errorDiv, inputElement.nextSibling);
        }

        errorDiv.textContent = message;
        inputElement.classList.add('input-error'); // Use CSS class for styling
    }

    function clearError(inputElement) {
        if (!inputElement) return;

        inputElement.classList.remove('input-error'); // Remove CSS class
        let errorDiv = inputElement.nextElementSibling;
        if (errorDiv && errorDiv.classList.contains('input-error-message')) {
            errorDiv.remove();
        }
    }
}

/* ================================================= */
/* === PUBLIC ADOPTION PAGE LOGIC === */
/* ================================================= */

function handlePublicAdoptionPage() {
    const gallery = document.getElementById('adoption-gallery');
    const filterControls = document.querySelectorAll('.filter-container select');
    const sortControl = document.getElementById('sort-by');
    const allAnimals = window.petsData; // Assumes petsData is globally available

    if (!gallery || !allAnimals) return; 

    /**
     * Filters the animals based on current selections.
     * @param {Array<object>} data - The full list of animal objects.
     * @returns {Array<object>} The filtered list.
     */
    const filterAnimals = (data) => {
        const filters = {
            species: document.getElementById('filter-species')?.value,
            age: document.getElementById('filter-age')?.value,
            size: document.getElementById('filter-size')?.value,
            gender: document.getElementById('filter-gender')?.value,
        };

        return data.filter(animal => {
            let passes = true;

            // Filter by Species
            const speciesValue = animal.species ? animal.species.toString().toLowerCase() : '';
            const filterSpecies = filters.species ? filters.species.toString().toLowerCase() : '';
            if (filterSpecies && filterSpecies !== 'all') {
                if (filterSpecies === 'dog') {
                    if (!speciesValue.includes('dog')) passes = false;
                } else if (filterSpecies === 'cat') {
                    if (!speciesValue.includes('cat')) passes = false;
                } else if (speciesValue !== filterSpecies) {
                    passes = false;
                }
            }

            // Filter by Age Category (using the helper function)
            if (filters.age && filters.age !== 'all') {
                const ageCategory = getAgeCategory(animal.age);
                if (ageCategory !== filters.age) {
                    passes = false;
                }
            }

            // Filter by Size
            const sizeValue = animal.size ? animal.size.toString().toLowerCase() : '';
            const filterSize = filters.size ? filters.size.toString().toLowerCase() : '';
            if (filterSize && filterSize !== 'all' && sizeValue !== filterSize) {
                passes = false;
            }

            // Filter by Gender
            const genderValue = animal.gender ? animal.gender.toString().toLowerCase() : '';
            const filterGender = filters.gender ? filters.gender.toString().toLowerCase() : '';
            if (filterGender && filterGender !== 'all' && genderValue !== filterGender) {
                passes = false;
            }

            return passes;
        });
    };

    /**
     * Sorts the animals based on the selected criteria.
     * @param {Array<object>} data - The list of animal objects to sort.
     * @returns {Array<object>} The sorted list.
     */
    const sortAnimals = (data) => {
        const sortBy = sortControl ? sortControl.value : 'name_asc';
        
        return data.sort((a, b) => {
            switch (sortBy) {
                case 'name_asc':
                    return a.name.localeCompare(b.name);
                case 'name_desc':
                    return b.name.localeCompare(a.name);
                case 'age_asc':
                    return a.age - b.age;
                case 'age_desc':
                    return b.age - a.age;
                default:
                    return a.name.localeCompare(b.name);
            }
        });
    };

    /**
     * Executes the full filtering and rendering process.
     */
    const updateGallery = () => {
        let filteredAnimals = filterAnimals(allAnimals);
        let sortedAnimals = sortAnimals(filteredAnimals);

        gallery.innerHTML = ''; 
        if (sortedAnimals.length === 0) {
            gallery.innerHTML = '<div class="no-results">No animals match your current filters. Please adjust your search criteria.</div>';
        } else {
            sortedAnimals.forEach(animal => {
                gallery.innerHTML += renderAnimalCard(animal);
            });
        }
    };

    // Attach event listeners to all filter and sort controls
    [...filterControls, sortControl].forEach(control => {
        if (control) control.addEventListener('change', updateGallery);
    });

    // Use event delegation on the gallery for card clicks (more efficient)
    gallery.addEventListener('click', (event) => {
        // Look for the closest element with the class 'clickable-card'
        const card = event.target.closest('.clickable-card');
        if (card) {
            const animalId = card.getAttribute('data-id');
            if (animalId) {
                // Ensure animalId is parsed as number if needed, though showAnimalDetail handles string/number comparison
                window.showAnimalDetail(animalId); 
            }
        }
    });

    // Initial load of the gallery
    updateGallery();
}


/* ================================================= */
/* === PUBLIC NAVIGATION & UTILITY FUNCTIONS === */
/* ================================================= */

function handlePublicNavigation() {
    const hamburger = document.querySelector('.hamburger');
    const primaryNav = document.querySelector('.primary-nav');
    const utilityNav = document.querySelector('.nav-utility');

    if (hamburger && primaryNav && utilityNav) {
        hamburger.addEventListener('click', () => {
         
            const isMenuOpen = primaryNav.style.display === 'flex' || primaryNav.style.display === '';
            primaryNav.style.display = isMenuOpen ? 'none' : 'flex';
            utilityNav.style.display = isMenuOpen ? 'none' : 'flex';
            
            hamburger.classList.toggle('is-active'); 
        });
        
        window.addEventListener('resize', () => {
            if (window.innerWidth > 992) {
                primaryNav.style.display = 'flex';
                utilityNav.style.display = 'flex';
            } else if (!hamburger.classList.contains('is-active')) {
                primaryNav.style.display = 'none';
                utilityNav.style.display = 'none';
            }
        });
    }
}

        function handleSmoothScroll() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();

                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });
        }

        function handleHomePageFeatures() {

            const featuredCards = document.querySelectorAll('.clickable-card');
            featuredCards.forEach(card => {
                card.addEventListener('click', () => {
                    const animalId = card.getAttribute('data-id');
                    if (animalId) {
                        window.showAnimalDetail(animalId);
                    }
                });
            });
        }
