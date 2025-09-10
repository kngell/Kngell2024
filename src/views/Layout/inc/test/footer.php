 <script>
// Sidebar Class
class Sidebar {
    constructor(sidebarElement) {
        this.sidebar = sidebarElement;
        this.resizeButton = document.getElementById("resize");
        this.isCollapsed = false;

        this.init();
    }

    init() {
        this.resizeButton.addEventListener("click", () => this.toggle());
        window.addEventListener("resize", () => this.handleResize());
        this.handleResize();
    }

    toggle() {
        if (this.isCollapsed) {
            this.expand();
        } else {
            this.collapse();
        }
    }

    collapse() {
        this.sidebar.classList.add("collapsed");
        this.isCollapsed = true;
        this.resizeButton.classList.add("rotate");
    }

    expand() {
        this.sidebar.classList.remove("collapsed");
        this.isCollapsed = false;
        this.resizeButton.classList.remove("rotate");
    }

    handleResize() {
        if (window.innerWidth < 992) {
            // On mobile, ensure sidebar is expanded by default
            this.expand();
        }
    }
}

// MenuItem Class
class MenuItem {
    constructor(element, manager) {
        this.element = element;
        this.manager = manager;
        this.isActive = this.element.classList.contains('active');
        this.link = this.element.querySelector('.menu-group__list-item--link');
        this.dropdownButton = this.element.querySelector('.menu-group__list-item--dropdown-button');
        this.dropdownMenu = this.element.querySelector('.menu-group__list-item--dropdown-menu');

        this.init();
    }

    init() {
        if (this.link) {
            this.link.addEventListener('click', (e) => {
                e.preventDefault();
                this.manager.setActiveItem(this);
            });
        }

        if (this.dropdownButton) {
            this.dropdownButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleDropdown();
                this.manager.setActiveItem(this);
            });
        }
    }

    toggleDropdown() {
        if (this.dropdownMenu) {
            const isOpening = !this.dropdownMenu.classList.contains('show');

            // Close all other dropdowns
            this.manager.closeAllDropdowns();

            // Toggle this dropdown
            if (isOpening) {
                this.dropdownMenu.classList.add('show');
                const arrow = this.dropdownButton.querySelector('.icon.arrow-down');
                if (arrow) {
                    arrow.classList.add('rotated');
                }
            } else {
                this.dropdownMenu.classList.remove('show');
                const arrow = this.dropdownButton.querySelector('.icon.arrow-down');
                if (arrow) {
                    arrow.classList.remove('rotated');
                }
            }
        }
    }

    activate() {
        this.element.classList.add('active');
        this.isActive = true;
    }

    deactivate() {
        this.element.classList.remove('active');
        this.isActive = false;

        // Close dropdown if it's a dropdown parent
        if (this.dropdownButton && this.dropdownMenu) {
            this.dropdownMenu.classList.remove('show');
            const arrow = this.dropdownButton.querySelector('.icon.arrow-down');
            if (arrow) {
                arrow.classList.remove('rotated');
            }
        }
    }

    getType() {
        if (this.dropdownButton) {
            return 'dropdown-parent';
        }
        return 'top-level';
    }

    getText() {
        if (this.link) {
            const span = this.link.querySelector('span');
            return span ? span.textContent : 'Menu Item';
        }
        if (this.dropdownButton) {
            const span = this.dropdownButton.querySelector('span');
            return span ? span.textContent : 'Dropdown Menu';
        }
        return 'Unknown';
    }
}

// DashboardManager Class
class DashboardManager {
    constructor() {
        this.menuItems = [];
        this.dropdownItems = [];
        this.activeItem = null;
        this.activeDropdownItem = null;

        this.init();
    }

    init() {
        this.setup();
    }

    setup() {
        // Create sidebar instance
        const sidebarElement = document.querySelector('.dashboard__aside');
        this.sidebar = new Sidebar(sidebarElement);

        // Create menu item instances
        const menuItemElements = document.querySelectorAll('.menu-group__list-item');
        menuItemElements.forEach((itemElement) => {
            this.menuItems.push(new MenuItem(itemElement, this));
        });

        // Set initial active item
        const initialActive = this.menuItems.find((item) => item.isActive);
        if (initialActive) {
            this.setActiveItem(initialActive);
        }
    }

    setActiveItem(menuItem) {
        // Deactivate current active item
        if (this.activeItem && this.activeItem !== menuItem) {
            this.activeItem.deactivate();
        }

        // Activate the new item
        menuItem.activate();
        this.activeItem = menuItem;
    }

    closeAllDropdowns() {
        this.menuItems.forEach((menuItem) => {
            if (menuItem.dropdownMenu && menuItem.dropdownMenu.classList.contains('show')) {
                // Only close if it's not the active item's dropdown
                if (this.activeItem !== menuItem) {
                    menuItem.dropdownMenu.classList.remove('show');
                    if (menuItem.dropdownButton) {
                        const arrow = menuItem.dropdownButton.querySelector('.icon.arrow-down');
                        if (arrow) {
                            arrow.classList.remove('rotated');
                        }
                    }
                }
            }
        });
    }
}

// Initialize the dashboard
document.addEventListener('DOMContentLoaded', () => {
    new DashboardManager();

    // Mobile toggle functionality
    const mobileToggle = document.getElementById('mobile-toggle');
    const sidebar = document.querySelector('.dashboard__aside');

    mobileToggle.addEventListener('click', () => {
        sidebar.classList.toggle('visible');
    });

    // Handle window resize for mobile view
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 993) {
            sidebar.classList.remove('visible');
        }
    });
});
 </script>
 </body>

 </html>