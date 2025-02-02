import Helper from "./_helper";
export default class Dropdown extends Helper {
  constructor() {
    super({
      main: "k-dropdown",
      menu: "k-dropdown__menu",
      target: "data-dropdown-target",
      active: "k-active",
      components: [...document.querySelectorAll(`.k-dropdown`)],
    });
  }
  handle() {
    document.addEventListener("click", (e) => {
      const target = e.target.closest(`.${this._variables.main}`);
      const targetedMenu = e.target.closest(`.${this._variables.menu}`);
      if (!target || targetedMenu) return;
      e.preventDefault();
      this.operateNavigation("dropdown", target).closeMenu(
        `[${this._variables.target}]`
      );
    });
  }
}
