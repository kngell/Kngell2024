class FormButtonsManager {
  constructor(formContainer, buttonsGroup, sidebar) {
    this.form = formContainer;
    this.buttons = buttonsGroup;
    this.sidebar = sidebar;
    this.init();
  }

  init() {
    this.updatePosition();
    this.addEventListeners();
  }

  updatePosition() {
    if (window.innerWidth >= 768) {
      // md breakpoint
      const formRect = this.form.getBoundingClientRect();
      const sidebarWidth = this.sidebar.offsetWidth;

      this.buttons.style.position = "fixed";
      this.buttons.style.left = `${sidebarWidth}px`;
      this.buttons.style.width = `${formRect.width}px`;
      this.buttons.style.bottom = "0";
    } else {
      // Reset for mobile
      this.buttons.style.position = "";
      this.buttons.style.left = "";
      this.buttons.style.width = "";
    }
  }

  addEventListeners() {
    window.addEventListener("resize", () => this.updatePosition());
    // Optional: listen for sidebar collapse/expand
  }
}

// Usage when ready:
// const formManager = new FormButtonsManager(
//     document.querySelector('.product__body-frm'),
//     document.querySelector('.product__body-frm--buttons-group'),
//     document.querySelector('.sidebar') // your sidebar selector
// );
