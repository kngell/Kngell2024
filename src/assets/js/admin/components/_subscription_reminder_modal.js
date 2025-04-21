export default class SubscriptionReminderModal {
  constructor() {
    this._variables = {
      target: "data-modal-target",
      active: "k-active",
    };
    this._init();
  }
  _init() {
    this.open();
    this.close();
  }

  open() {
    document.addEventListener("click", (e) => {
      const target = e.target.closest(`[${this._variables.target}]`);
      if (!target) return;
      e.preventDefault();
      const targetId = target.getAttribute(this._variables.target);
      const modal = document.getElementById(targetId);
      if (!modal) return;
      modal.classList.add(this._variables.active);
      document.body.style.overflow = "hidden";
    });
  }
  close() {
    window.addEventListener("mouseup", (e) => {
      const target =
        e.target.closest(`[${this._variables.target}]`) ||
        e.target.closest(`.admin-subscription-plan`);
      if (target) {
        return;
      }
      const modal = e.target.closest(".admin-modal");
      if (
        !modal ||
        (modal && !modal.classList.contains(this._variables.active))
      ) {
        return;
      }
      modal.classList.remove(this._variables.active);
      document.body.removeAttribute("style");
    });
  }
}
