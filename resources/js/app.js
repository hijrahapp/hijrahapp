import './bootstrap';

// Start Alpine.js

// Metronic Core JavaScript functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize drawer functionality
    initDrawers();

    // Initialize menu functionality
    initMenus();

    // Initialize sticky headers
    initStickyHeaders();

    // Initialize modal functionality
    initModals();

    // Initialize dropdown functionality
    initDropdowns();

    // Rich text editors are initialized by their own components
});

// Drawer functionality
function initDrawers() {
    const drawers = document.querySelectorAll('[data-kt-drawer]');

    drawers.forEach(drawer => {
        const toggles = document.querySelectorAll(`[data-kt-drawer-toggle="#${drawer.id}"]`);

        toggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                drawer.classList.toggle('hidden');
                drawer.classList.toggle('block');
            });
        });
    });
}

// Menu functionality
function initMenus() {
    const menus = document.querySelectorAll('[data-kt-menu="true"]');

    menus.forEach(menu => {
        const items = menu.querySelectorAll('[data-kt-menu-item-toggle="dropdown"]');

        items.forEach(item => {
            const trigger = item.querySelector('[data-kt-menu-item-trigger="click"], [data-kt-menu-item-trigger="click|lg:hover"]');
            const dropdown = item.querySelector('.kt-menu-dropdown');

            if (trigger && dropdown) {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    dropdown.classList.toggle('hidden');
                });
            }
        });

        // Handle main container navigation
        const containerToggles = menu.querySelectorAll('[data-kt-container-toggle]');
        const containers = document.querySelectorAll('.kt-main-container');
        containerToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-kt-container-toggle');
                containers.forEach(container => {
                    if ('#' + container.id === targetId) {
                        container.classList.remove('hidden');
                    } else {
                        container.classList.add('hidden');
                    }
                });
            });
        });
    });
}

// Sticky header functionality
function initStickyHeaders() {
    const stickyElements = document.querySelectorAll('[data-kt-sticky="true"]');

    stickyElements.forEach(element => {
        const stickyClass = element.getAttribute('data-kt-sticky-class') || 'kt-sticky';
        const offset = parseInt(element.getAttribute('data-kt-sticky-offset')) || 0;

        window.addEventListener('scroll', function() {
            if (window.scrollY > offset) {
                element.classList.add(...stickyClass.split(' '));
            } else {
                element.classList.remove(...stickyClass.split(' '));
            }
        });
    });
}

// Modal functionality
function initModals() {
    const modalToggles = document.querySelectorAll('[data-kt-modal-toggle]');

    modalToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-kt-modal-toggle');
            const modal = document.querySelector(modalId);

            if (modal) {
                modal.classList.toggle('hidden');
                modal.classList.toggle('flex');
                // Rich text editors are initialized by their own components
            }
        });
    });
}

// Dropdown functionality
function initDropdowns() {
    // Initialize KTUI dropdowns if available
    if (window.KTDropdown) {
        window.KTDropdown.init();
    }

    // Handle dropdown menu item clicks to close dropdown
    document.addEventListener('click', function(e) {
        const menuLink = e.target.closest('.kt-dropdown-menu-link');
        if (menuLink && menuLink.hasAttribute('wire:click')) {
            // Find the parent dropdown and close it
            const dropdown = menuLink.closest('[data-kt-dropdown]');
            if (dropdown && window.KTDropdown) {
                const dropdownInstance = window.KTDropdown.getInstance(dropdown);
                if (dropdownInstance) {
                    // Close the dropdown after a short delay to allow Livewire to process the click
                    setTimeout(() => {
                        dropdownInstance.hide();
                    }, 100);
                }
            }
        }
    });
}

// No Quill/TinyMCE functionality

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    
    const modals = document.querySelectorAll('.kt-modal');

    modals.forEach(modal => {
        if (e.target === modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (window.Livewire) {
                window.Livewire.dispatch('reset-modal');
            }
        }
    });
});

// Livewire hooks
document.addEventListener('livewire:init', () => {
    // Re-initialize functionality after Livewire updates
    Livewire.hook('morph.updated', () => {
        initDrawers();
        initMenus();
        initStickyHeaders();
        initModals();
        initDropdowns();
        // Rich text editors are initialized by their own components
    });

    // Global toast listener for Livewire
    Livewire.on('show-toast', (data) => {
        console.log('Toast event received:', data);
        const { type, message } = data;
        const variant = mapToastVariant(type);
        try {
            if (window.KTToast && typeof window.KTToast.show === 'function') {
                console.log('Using KTToast.show');
                window.KTToast.show({
                    message: message,
                    // KTUI uses `variant` for color scheme
                    variant: variant,
                    // keep legacy `type` in case of version differences
                    type: variant,
                    appearance: 'solid',
                    position: 'top-end',
                    dismissible: true,
                    duration: 5000
                });
                return;
            }
            if (window.kt && window.kt.toast && typeof window.kt.toast.show === 'function') {
                console.log('Using kt.toast.show');
                window.kt.toast.show({
                    message: message,
                    // Some builds expect `type`; keep both for compatibility
                    variant: variant,
                    type: variant,
                    appearance: 'solid',
                    position: 'top-end',
                    dismissible: true,
                    duration: 5000
                });
                return;
            }
            console.log('Falling back to alert');
        } catch (e) {
            console.error('Toast error:', e);
        }
        alert(message);
    });

    // Global modal toggle listener
    Livewire.on('toggle-modal', (data) => {
        console.log('Toggle modal event received:', data);
        const { selector } = data;
        const modal = document.querySelector(selector);
        if (modal) {
            modal.classList.toggle('hidden');
            modal.classList.toggle('flex');
        }
    });

    // Global modal show listener using KTUI
    Livewire.on('show-modal', (data) => {
        console.log('Show modal event received:', data);
        const { selector } = data;
        const modal = document.querySelector(selector);
        if (modal && window.KTModal) {
            const modalInstance = window.KTModal.getInstance(modal);
            if (modalInstance) {
                modalInstance.show();
            } else {
                // Fallback to direct show if no instance
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }
        // Rich text editors are initialized by their own components
    });
});

// Export functions for use in other modules
window.MetronicCore = {
    initDrawers,
    initMenus,
    initStickyHeaders,
    initModals,
    initDropdowns
};

// Normalize toast type to KTUI `variant`
function mapToastVariant(rawType) {
    const t = (rawType || 'info').toString().toLowerCase();
    if (t === 'success') return 'success';
    if (t === 'warning' || t === 'warn') return 'warning';
    if (t === 'error' || t === 'danger' || t === 'destructive' || t === 'fail' || t === 'failed') return 'destructive';
    if (t === 'primary') return 'primary';
    if (t === 'secondary') return 'secondary';
    if (t === 'mono') return 'mono';
    return 'info';
}

// Rich text editor initialization is encapsulated within the Livewire component
