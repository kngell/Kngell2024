import BrowserLogger from "js/core/utils/BrowserLogger";

class ActionButton {
  constructor(formElement, options = {}) {
    if (!formElement) {
      return;
    }

    this.form = formElement;
    this.logger = new BrowserLogger("ActionButton");

    this.options = {
      handler: null,
      ...options
    };

    // Only direct child button of THIS form
    this.button = this.form.querySelector(":scope > button[type='submit']");

    if (!this.button) {
      this.button = this.form.querySelector(":scope > button");
    }

    if (!this.button) {
      this.logger.warn(`No direct submit button found in form: ${this.form.id || "anonymous"}`);
      return;
    }

    this._boundOnSubmit = this._onSubmit.bind(this);
    this._boundOnClick = this._onClick.bind(this);

    this.form.addEventListener("submit", this._boundOnSubmit);
    this.button.addEventListener("click", this._boundOnClick);

    this.logger.debug(`ActionButton bound to form: ${this.form.id || "anonymous"}`);
  }

  getData() {
    const data = {};

    this.form.querySelectorAll(':scope > input[type="hidden"]').forEach((input) => {
      if (input.name === "csrfToken" || input.name === "frm_name") return;
      data[input.name] = input.value;
    });

    return data;
  }

  getFormAction() {
    return this.form.getAttribute("action") || "";
  }

  getFormId() {
    return this.form.id || this.form.name || null;
  }

  setEnabled(enabled) {
    if (this.button) {
      this.button.disabled = !enabled;
      this.button.classList.toggle("btn--disabled", !enabled);
    }
  }

  setVisible(visible) {
    if (this.form) {
      this.form.style.display = visible ? "" : "none";
    }
  }

  _onSubmit(e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
  }

  _onClick(e) {
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();

    if (typeof this.options.handler === "function") {
      this.options.handler({
        data: this.getData(),
        formAction: this.getFormAction(),
        formId: this.getFormId(),
        button: this.button,
        form: this.form
      });
    }
  }

  destroy() {
    if (this.form) {
      this.form.removeEventListener("submit", this._boundOnSubmit);
    }
    if (this.button) {
      this.button.removeEventListener("click", this._boundOnClick);
    }

    this.form = null;
    this.button = null;
    this.options.handler = null;
  }
}

export default ActionButton;
