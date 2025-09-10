export default class DropdownMenuItem {
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
