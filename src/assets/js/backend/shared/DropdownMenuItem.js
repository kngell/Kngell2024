// DropdownMenuItem.js
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
        // e.preventDefault();

        // This is the key change. We use the History API.
        const newUrl = this.link.getAttribute("href");

        // Update the URL without reloading the page.
        history.pushState(null, "", newUrl);

        // Now, manage the active states with your existing logic.
        this.manager.setActiveDropdownItem(this);

        // We still need to load the content from the server
        // This part is missing in your current code.
        // You need to make an AJAX request to fetch the content for the new URL.
        // E.g., this.loadContent(newUrl);
      });
    }
  }

  activate() {
    this.element.classList.add("active");
    this.isActive = true;
  }

  deactivate() {
    this.element.classList.remove("active");
    this.isActive = false;
  }

  getText() {
    return this.link ? this.link.textContent : "Unknown";
  }
}
