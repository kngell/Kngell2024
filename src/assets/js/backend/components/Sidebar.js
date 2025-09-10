export default class Sidebar {
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
  isSidebarCollapsed() {
    return this.isCollapsed;
  }
  getSidebar() {
    return this.sidebar;
  }
}
