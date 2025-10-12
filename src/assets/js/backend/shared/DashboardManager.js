import MenuItem from "js/backend/shared/MenuItem.js";
import DropdownMenuItem from "js/backend/shared/DropdownMenuItem";

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

  // DashboardManager.js
  setup() {
    this.clearAllActiveStates();

    const allMenuItems = [];
    const menuItemElements = document.querySelectorAll(".menu-list__item");

    menuItemElements.forEach((itemElement) => {
      const menuItem = new MenuItem(itemElement, this);

      // ✅ Intercept click only if it’s NOT inside a form
      itemElement.addEventListener("click", (e) => {
        if (e.target.closest("form")) {
          return; // Skip menu logic if click is inside a form
        }
      });

      allMenuItems.push(menuItem);

      if (menuItem.dropdownMenu) {
        const dropdownItemElements = menuItem.dropdownMenu.querySelectorAll(".dropdown-list__item");

        dropdownItemElements.forEach((dropdownElement) => {
          const dropdownItem = new DropdownMenuItem(dropdownElement, this, menuItem);

          // Same protection for dropdown items
          dropdownElement.addEventListener("click", (e) => {
            if (e.target.closest("form")) {
              return;
            }
          });

          allMenuItems.push(dropdownItem);
        });
      }
    });

    this.menuItems = allMenuItems.filter((item) => !(item instanceof DropdownMenuItem));
    this.dropdownItems = allMenuItems.filter((item) => item instanceof DropdownMenuItem);

    let initialActiveItem = null;
    const currentUrl = window.location.pathname;

    if (currentUrl === "/admin" || currentUrl === "/admin/") {
      initialActiveItem = allMenuItems.find(
        (item) => item.link && item.link.pathname === "/admin/index",
      );
    }

    if (!initialActiveItem) {
      initialActiveItem = allMenuItems.find(
        (item) => item.link && item.link.pathname === currentUrl,
      );
    }

    if (!initialActiveItem && this.menuItems.length > 0) {
      initialActiveItem = this.menuItems[0];
    }

    if (initialActiveItem) {
      if (initialActiveItem instanceof DropdownMenuItem) {
        this.setActiveDropdownItem(initialActiveItem);
      } else if (initialActiveItem instanceof MenuItem) {
        this.setActiveItem(initialActiveItem);
      }
    }

    const logoLink = document.getElementById("logo-link");
    if (logoLink) {
      logoLink.addEventListener(
        "click",
        () => {
          this.clearAllActiveStates();
        },
        false,
      );
    }
  }

  clearAllActiveStates() {
    this.menuItems.forEach((item) => item.deactivate());
    this.dropdownItems.forEach((item) => item.deactivate());
    this.activeItem = null;
    this.activeDropdownItem = null;
  }

  setActiveItem(menuItem) {
    // Clear any previously active states.
    this.clearAllActiveStates();

    // Activate the new menu item and store it.
    this.activeItem = menuItem;
    menuItem.activate();

    // --- FIX FOR PROBLEM: ADDING 'opened' CLASS ---
    // If the activated item is a dropdown parent, add the 'opened' class
    // and trigger the dropdown's visual state change.
    if (menuItem.getType() === "dropdown-parent") {
      menuItem.element.classList.add("opened");
      menuItem.isOpened = true;

      // This is optional, but it ensures the arrow rotates
      const arrow = menuItem.dropdownButton.querySelector(".icon.arrow-down");
      if (arrow) arrow.classList.add("rotated");
    } else {
      // If it's not a dropdown parent, ensure all dropdowns are closed.
      // This handles the transition from a dropdown to a top-level item.
      this.closeAllDropdowns();
    }
    // --- END FIX ---

    // Update the display for the user.
    this.updateActiveDisplay(menuItem.getText(), menuItem.getType());
  }

  setActiveDropdownItem(dropdownItem) {
    this.clearAllActiveStates();
    this.activeDropdownItem = dropdownItem;
    dropdownItem.activate();

    // Activate the parent menu item as well
    if (dropdownItem.parent) {
      this.activeItem = dropdownItem.parent;
      this.activeItem.activate();
      this.activeItem.element.classList.add("opened"); // Ensure parent is opened
    }
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

  closeAllDropdowns() {
    this.menuItems.forEach((menuItem) => {
      if (menuItem.dropdownMenu) {
        menuItem.dropdownMenu.classList.remove("show");
        if (menuItem.dropdownButton) {
          const arrow = menuItem.dropdownButton.querySelector(".icon.arrow-down");
          if (arrow) arrow.classList.remove("rotated");
        }
        menuItem.element.classList.remove("opened");
        menuItem.isOpened = false;
      }
    });
  }
}
