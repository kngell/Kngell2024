import FormFormatter from "./FormFormatter";
export default class FormNumericManager {
  constructor(formElement) {
    this.form = formElement;

    const configEl = document.getElementById("form-numeric-config");
    const numericFields = configEl ? JSON.parse(configEl.value) : [];

    this.formatter = new FormFormatter(numericFields);

    if (this.form) {
      this._init();
    }
  }

  _init() {
    this._applyToContainer(this.form);

    // Handle form submission to unformat values
    this._setupFormSubmission();

    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (node.nodeType === 1) {
            if (node.tagName === "INPUT" && this.formatter.isNumeric(node.name)) {
              this._bindEvents(node);
              this._formatField(node);
            } else {
              this._applyToContainer(node);
            }
          }
        });
      });
    });

    observer.observe(this.form, { childList: true, subtree: true });
  }

  _setupFormSubmission() {
    // Find the parent form element
    const formElement =
      this.form.closest("form") ||
      document.querySelector(`#${this.form.getAttribute("id")}`) ||
      this.form;

    if (formElement && formElement.tagName === "FORM") {
      formElement.addEventListener("submit", (e) => {
        this._unformatAllFields();
      });
    }
  }

  _applyToContainer(container) {
    const inputs = container.querySelectorAll("input[type='number']");
    inputs.forEach((input) => {
      if (this.formatter.isNumeric(input.name)) {
        this._bindEvents(input);
        this._formatField(input);
      }
    });
  }

  _bindEvents(input) {
    const isQuantity = input.name.includes("quantity");

    // 1. Force numeric keypad for mobile
    input.inputMode = isQuantity ? "numeric" : "decimal";

    // 2. Clear formatting on focus
    input.addEventListener("focus", (e) => {
      const val = e.target.value;
      if (val) {
        e.target.value = this.formatter.getRawValue(val, isQuantity);
        // Select all text for easy editing
        setTimeout(() => e.target.select(), 10);
      }
    });

    // 3. Re-apply space formatting on blur
    input.addEventListener("blur", (e) => {
      this._formatField(e.target);
    });

    // 4. Also format on input (real-time) for better UX
    input.addEventListener("input", (e) => {
      if (document.activeElement !== e.target) {
        this._formatField(e.target);
      }
    });
  }

  _formatField(input) {
    const isQuantity = input.name.includes("quantity");
    const val = input.value;
    if (val !== "") {
      input.value = this.formatter.formatForDisplay(val, isQuantity);
    }
  }

  _unformatAllFields() {
    const inputs = this.form.querySelectorAll("input[type='number']");
    inputs.forEach((input) => {
      if (this.formatter.isNumeric(input.name)) {
        const isQuantity = input.name.includes("quantity");
        const rawValue = this.formatter.getRawValue(input.value, isQuantity);
        input.value = rawValue;
      }
    });
  }
}
