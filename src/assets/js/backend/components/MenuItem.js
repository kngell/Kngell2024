export default class MenuItem {
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
