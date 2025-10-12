import MenuItem from "./MenuItem";
import DropdownMenuItem from "./DropdownMenuItem";
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
    // Create menu item instances
    const menuItemElements = document.querySelectorAll(".menu-list__item");
    menuItemElements.forEach((itemElement) => {
      this.menuItems.push(new MenuItem(itemElement, this));
    });

    // Create dropdown menu item instances
    const dropdownItemElements = document.querySelectorAll(".dropdown-list__item");
    dropdownItemElements.forEach((itemElement) => {
      // Find the parent dropdown
      const dropdownMenu = itemElement.closest(".menu-list__item--dropdown-menu");
      const parentElement = dropdownMenu ? dropdownMenu.closest(".menu-list__item") : null;
      const parent = this.menuItems.find((item) => item.element === parentElement);

      this.dropdownItems.push(new DropdownMenuItem(itemElement, this, parent));
    });

    // Set initial active item
    let initialActive = this.menuItems.find((item) => item.isActive);

    // If no active item found, set first one as active
    if (!initialActive && this.menuItems.length > 0) {
      initialActive = this.menuItems[0];
      initialActive.activate();
      this.activeItem = initialActive;
    } else if (initialActive) {
      this.activeItem = initialActive;
    }

    // Set initial active dropdown item if exists
    const initialActiveDropdown = this.dropdownItems.find((item) => item.isActive);

    // If no active dropdown but we have an active dropdown parent, activate first dropdown item
    if (!initialActiveDropdown && initialActive && initialActive.getType() === "dropdown-parent") {
      const firstDropdownItem = this.dropdownItems.find((item) => {
        const dropdownMenu = item.element.closest(".menu-list__item--dropdown-menu");
        const parentElement = dropdownMenu ? dropdownMenu.closest(".menu-list__item") : null;
        return parentElement === initialActive.element;
      });

      if (firstDropdownItem) {
        firstDropdownItem.activate();
        this.activeDropdownItem = firstDropdownItem;
      }
    } else if (initialActiveDropdown) {
      this.activeDropdownItem = initialActiveDropdown;
    }
  }

  setActiveItem(menuItem) {
    // Deactivate current active item
    if (this.activeItem && this.activeItem !== menuItem) {
      this.activeItem.deactivate();
    }

    // Deactivate any active dropdown item
    if (this.activeDropdownItem) {
      this.activeDropdownItem.deactivate();
      this.activeDropdownItem = null;
    }

    // Close all dropdowns except if this is a dropdown parent
    if (menuItem.getType() === "dropdown-parent") {
      this.closeAllDropdowns(menuItem);
    } else {
      this.closeAllDropdowns();
    }

    // Activate the new item
    menuItem.activate();
    this.activeItem = menuItem;

    // Update display
    this.updateActiveDisplay(menuItem.getText(), menuItem.getType());
  }

  setActiveDropdownItem(dropdownItem) {
    // Deactivate current active dropdown item
    if (this.activeDropdownItem) {
      this.activeDropdownItem.deactivate();
    }

    // Activate the new dropdown item
    dropdownItem.activate();
    this.activeDropdownItem = dropdownItem;

    // Update display
    this.updateActiveDisplay(dropdownItem.getText(), "dropdown-item");
  }

  updateActiveDisplay(text, type) {
    const displayElement = document.getElementById("current-active-menu");
    const typeElement = document.getElementById("active-menu-type");

    if (displayElement) {
      displayElement.textContent = text;
    }

    if (typeElement) {
      let typeText = "Type: ";
      switch (type) {
        case "top-level":
          typeText += "Top-level menu item";
          break;
        case "dropdown-parent":
          typeText += "Dropdown parent";
          break;
        case "dropdown-item":
          typeText += "Dropdown item";
          break;
        default:
          typeText += type;
      }
      typeElement.textContent = typeText;
    }
  }

  closeAllDropdowns(exceptItem = null) {
    this.menuItems.forEach((menuItem) => {
      // Only process items with dropdowns that aren't the exception
      if (menuItem.dropdownMenu && menuItem !== exceptItem) {
        // Don't close dropdowns of active items with opened class
        if (!(menuItem.isActive && menuItem.isOpened)) {
          menuItem.dropdownMenu.classList.remove("show");
          if (menuItem.dropdownButton) {
            const arrow = menuItem.dropdownButton.querySelector(".icon.arrow-down");
            if (arrow) arrow.classList.remove("rotated");
          }
          // Only remove opened class if the item is not active
          if (!menuItem.isActive) {
            menuItem.element.classList.remove("opened");
            menuItem.isOpened = false;
          }
        }
      }
    });
  }
}

class MenuItem {
  constructor(element, manager) {
    this.element = element;
    this.manager = manager;

    this.isActive = this.element.classList.contains("active");
    this.isOpened = this.element.classList.contains("opened");

    // Detect link and dropdown elements
    this.link = this.element.querySelector(".menu-list__item--link") || null;
    this.dropdownButton = this.element.querySelector(".menu-list__item--dropdown-button") || null;

    // Safely find dropdown menu only if it exists
    this.dropdownMenu = this.element.querySelector(".menu-list__item--dropdown-menu") || null;

    this.init();
  }

  init() {
    // Click on top-level link
    if (this.link) {
      this.link.addEventListener("click", (e) => {
        e.preventDefault();
        this.manager.setActiveItem(this);
      });
    }

    // Click on dropdown button
    if (this.dropdownButton && this.dropdownMenu) {
      this.dropdownButton.addEventListener("click", (e) => {
        e.preventDefault();
        this.toggleDropdown();
        this.manager.setActiveItem(this);
      });
    }
  }

  toggleDropdown() {
    if (!this.dropdownMenu) return;

    const isOpening = !this.dropdownMenu.classList.contains("show");

    // Close all other dropdowns first
    this.manager.closeAllDropdowns(isOpening ? this : null);

    // Toggle this dropdown
    if (isOpening) {
      this.dropdownMenu.classList.add("show");

      const arrow = this.dropdownButton?.querySelector(".icon.arrow-down");
      if (arrow) arrow.classList.add("rotated");

      // Add opened class to parent
      this.element.classList.add("opened");
      this.isOpened = true;
    } else {
      this.dropdownMenu.classList.remove("show");

      const arrow = this.dropdownButton?.querySelector(".icon.arrow-down");
      if (arrow) arrow.classList.remove("rotated");

      // Remove opened class from parent
      this.element.classList.remove("opened");
      this.isOpened = false;
    }
  }

  activate() {
    this.element.classList.add("active");
    this.isActive = true;
  }

  deactivate() {
    this.element.classList.remove("active");
    this.isActive = false;

    // Close dropdown and remove opened class
    if (this.dropdownButton && this.dropdownMenu) {
      this.dropdownMenu.classList.remove("show");

      const arrow = this.dropdownButton.querySelector(".icon.arrow-down");
      if (arrow) arrow.classList.remove("rotated");

      this.element.classList.remove("opened");
      this.isOpened = false;
    }
  }

  getType() {
    return this.dropdownButton ? "dropdown-parent" : "top-level";
  }

  getText() {
    const span = this.link?.querySelector("span") || this.dropdownButton?.querySelector("span");
    return span ? span.textContent : "Unknown";
  }
}

class DropdownMenuItem {
  constructor(element, manager, parent) {
    this.element = element;
    this.manager = manager;
    this.parent = parent;
    this.isActive = this.element.classList.contains("active");
    this.link = this.element.querySelector(".dropdown-list__item--link");

    this.init();
  }

  init() {
    if (this.link) {
      this.link.addEventListener("click", (e) => {
        e.preventDefault();
        this.manager.setActiveDropdownItem(this);

        // Navigate after a small delay to allow UI updates
        setTimeout(() => {
          window.location.href = this.link.href;
        }, 100);
      });
    }
  }

  activate() {
    this.element.classList.add("active");
    this.isActive = true;

    // Also activate the parent dropdown
    if (this.parent) {
      this.parent.activate();

      // Open the dropdown if it's closed and add opened class
      const dropdownMenu = this.parent.element.querySelector(".menu-list__item--dropdown-menu");
      if (dropdownMenu && !dropdownMenu.classList.contains("show")) {
        dropdownMenu.classList.add("show");
        this.parent.element.classList.add("opened");
        this.parent.isOpened = true;

        // Rotate the arrow
        const arrow = this.parent.dropdownButton.querySelector(".icon.arrow-down");
        if (arrow) {
          arrow.classList.add("rotated");
        }
      }
    }
  }

  deactivate() {
    this.element.classList.remove("active");
    this.isActive = false;
  }

  getText() {
    if (this.link) {
      return this.link.textContent;
    }
    return "Unknown";
  }
}
