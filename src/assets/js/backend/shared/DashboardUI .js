export default class DashboardUI {
  constructor() {
    this.activeMenuElement = document.getElementById("current-active-menu");
    this.activeMenuTypeElement = document.getElementById("active-menu-type");
    this.currentUrlElement = document.getElementById("current-url");
    this.activeDropdownElement = document.getElementById("active-dropdown");
    this.dropdownClassesElement = document.getElementById("dropdown-classes");
    this.arrowClassesElement = document.getElementById("arrow-classes");
  }

  updateActiveDisplay(text, type) {
    if (this.activeMenuElement) {
      this.activeMenuElement.textContent = text;
    }

    if (this.activeMenuTypeElement) {
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
      this.activeMenuTypeElement.textContent = typeText;
    }
  }

  updateCurrentUrl(url) {
    if (this.currentUrlElement) {
      this.currentUrlElement.textContent = url;
    }
  }

  updateDropdownState(menuItem) {
    if (this.activeDropdownElement) {
      this.activeDropdownElement.textContent = menuItem ? menuItem.getText() : "None";
    }

    if (this.dropdownClassesElement && menuItem && menuItem.dropdownMenu) {
      this.dropdownClassesElement.textContent = menuItem.dropdownMenu.classList.toString();
    }

    if (this.arrowClassesElement && menuItem && menuItem.dropdownButton) {
      const arrow = menuItem.dropdownButton.querySelector(".icon.arrow-down");
      if (arrow) {
        this.arrowClassesElement.textContent = arrow.classList.toString();
      }
    }
  }
}
