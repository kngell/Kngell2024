// MenuItem.js
export default class MenuItem {
  constructor(element, manager) {
    this.element = element;
    this.manager = manager;
    this.link = this.element.querySelector(".menu-list__item--link");
    this.dropdownButton = this.element.querySelector(".menu-list__item--dropdown-button");
    this.dropdownMenu = this.element.querySelector(".menu-list__item--dropdown-menu");
    this.isActive = this.element.classList.contains("active");
    this.isOpened = this.element.classList.contains("opened");

    this.init();
  }

  init() {
    // Only add a click listener to the link if it's NOT a dropdown parent.
    if (this.link && !this.dropdownButton) {
      this.link.addEventListener("click", (e) => {
        // Do NOT preventDefault() here. The browser should navigate.
        this.manager.setActiveItem(this);
      });
    }

    // Add a click listener for the dropdown button. This is where we need to prevent default.
    if (this.dropdownButton && this.dropdownMenu) {
      this.dropdownButton.addEventListener("click", (e) => {
        e.preventDefault(); // Prevent page reload
        this.toggleDropdown();
      });
    }
  }

  toggleDropdown() {
    if (this.isOpened) {
      this.element.classList.remove("opened");
      this.isOpened = false;
    } else {
      this.manager.closeAllDropdowns(this);
      this.element.classList.add("opened");
      this.isOpened = true;
    }
    this.manager.setActiveItem(this);
  }

  activate() {
    this.element.classList.add("active");
    this.isActive = true;
  }

  deactivate() {
    this.element.classList.remove("active", "opened");
    this.isActive = false;
    this.isOpened = false;
  }

  getType() {
    return this.dropdownMenu ? "dropdown-parent" : "link";
  }

  getText() {
    const textElement = this.element.querySelector("span");
    return textElement ? textElement.textContent : "";
  }
}
