export default class Helper {
  constructor(_variables) {
    this._variables = _variables;
  }

  operateNavigation(type, target) {
    let componentId =
      type === "dropdown" ? this._variables.target : this._variables.targetId;
    const targetId =
      type === "dropdown"
        ? target.querySelector(`[${componentId}]`).getAttribute(componentId)
        : target.getAttribute(componentId);

    const activeMenu = document.querySelector(`#${targetId}`);

    const nonTargeted = this._variables.components.map((item) => {
      const nonActiveId = item
        .querySelector(`[${componentId}]`)
        .getAttribute(componentId);
      const nonActive = document.querySelector(`#${nonActiveId}`);
      return nonActive;
    });
    const filterExceptActive = nonTargeted.filter(
      (target) => target !== activeMenu
    );
    filterExceptActive.forEach((item) =>
      item.classList.remove(this._variables.active)
    );
    if (activeMenu) {
      activeMenu.classList.toggle(this._variables.active);
    }
    return this;
  }
  closeMenu(speSelector) {
    window.addEventListener("mouseup", (e) => {
      const target =
        e.target.closest(`.${this._variables.menu}`) ||
        e.target.closest(speSelector);
      if (target) {
        return;
      }
      e.preventDefault();
      this._variables.components.forEach((comp) => {
        const menu = comp.querySelector(`.${this._variables.menu}`);
        if (menu.classList.contains(this._variables.active)) {
          menu.classList.remove(this._variables.active);
        }
      });
    });
  }
}
