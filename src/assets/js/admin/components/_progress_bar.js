export default class ProgressBar {
  constructor() {
    this._variables = {
      main: [...document.querySelectorAll(".admin-progress")],
      inner: "admin-progress__inner",
      target: "data-current-progress",
    };
  }

  update() {
    if (this._variables.main.length < 1) {
      return;
    }
    this._variables.main.forEach((el) => {
      const target = +el.getAttribute(this._variables.target);
      const inner = el.querySelector(`.${this._variables.inner}`);
      inner.style.width = `${target}%`;
    });
  }
}
