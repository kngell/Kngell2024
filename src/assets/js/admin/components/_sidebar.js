import Helper from "./_helper.js";
export default class Sidebar extends Helper {
  constructor() {
    super({
      main: "k-header",
      menu: "k-header__nav-items",
      target: "k-header__mobile--trigger",
      targetId: "data-menu-target",
      active: "k-active",
      components: [...document.querySelectorAll(".k-header")],
    });
  }
  handle() {
    document.addEventListener("click", (e) => {
      const targetBtn = e.target.closest(`.${this._variables.target}`);
      if (!targetBtn) {
        return;
      }
      e.preventDefault();
      this.operateNavigation("sidebar", targetBtn).closeMenu(
        `.${this._variables.target}`
      );
    });
  }
}
