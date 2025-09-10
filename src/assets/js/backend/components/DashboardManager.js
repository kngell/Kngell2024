import MenuItem from "./MenuItem";
import DropdownMenuItem from "./DropdownMenuItem";

export default class DashboardManager {
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
