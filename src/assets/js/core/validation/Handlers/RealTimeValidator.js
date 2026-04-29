import BrowserLogger from "js/core/utils/logger";

const logger = new BrowserLogger("RealTimeValidator");

export default class RealTimeValidator {
  constructor(options) {
    this.form = options.form;
    this.validator = options.validator;
    this.dataProcessor = options.dataProcessor;
    this.errorService = options.errorService;

    this.debounceTimers = new Map();
    this.eventHandlers = null;
    this.isEnabled = false;
  }

  enable() {
    if (this.isEnabled) return;
    this.setupEventListeners();
    this.isEnabled = true;
  }

  disable() {
    if (!this.isEnabled) return;
    this.removeEventListeners();
    this.clearDebounceTimers();
    this.isEnabled = false;
  }

  setupEventListeners() {
    const handleBlur = (e) => {
      if (this.shouldValidate(e.target)) {
        // Don't validate toggleable inputs on blur —
        // they validate on change
        if (this.isToggleableInput(e.target)) return;
        this.validateField(e.target);
      }
    };

    const handleInput = (e) => {
      if (this.shouldValidate(e.target)) {
        // For text-like inputs: clear error on keystroke
        if (!this.isToggleableInput(e.target)) {
          this.errorService.clearError(e.target);
        }
      }
    };

    const handleChange = (e) => {
      if (!this.shouldValidate(e.target)) return;

      // ARCHITECTURAL FIX: Optimistically clear the visual error state instantly
      // when a user toggles a checkbox/radio or picks a select option.
      this.errorService.clearError(e.target);

      if (this.isToggleableInput(e.target)) {
        // Checkboxes & radios: validate immediately, no debounce
        this.validateField(e.target, 0);
      } else {
        // Selects, etc.: debounce
        this.validateField(e.target, 300);
      }
    };

    this.form.addEventListener("blur", handleBlur, true);
    this.form.addEventListener("input", handleInput, true);
    this.form.addEventListener("change", handleChange, true);

    this.eventHandlers = { handleBlur, handleInput, handleChange };
  }

  removeEventListeners() {
    if (!this.eventHandlers) return;

    this.form.removeEventListener("blur", this.eventHandlers.handleBlur, true);
    this.form.removeEventListener("input", this.eventHandlers.handleInput, true);
    this.form.removeEventListener("change", this.eventHandlers.handleChange, true);

    this.eventHandlers = null;
  }

  shouldValidate(field) {
    if (!field || !field.matches("input, select, textarea")) return false;

    // field.form may be null for fields with form="" attribute
    // pointing elsewhere, or for detached elements
    const belongsToForm = field.form === this.form || field.getAttribute("form") === this.form.id;

    return belongsToForm && field.name && !field.hasAttribute("data-skip-validation");
  }

  isToggleableInput(field) {
    const type = (field.type || "").toLowerCase();
    return type === "checkbox" || type === "radio";
  }

  validateField(field, debounceMs = 0) {
    const fieldName = field.name;
    const timerKey = `${this.form.id || "form"}-${fieldName}`;

    if (this.debounceTimers.has(timerKey)) {
      clearTimeout(this.debounceTimers.get(timerKey));
      this.debounceTimers.delete(timerKey);
    }

    if (debounceMs === 0) {
      this._executeValidation(field, fieldName, timerKey);
      return;
    }

    this.debounceTimers.set(
      timerKey,
      setTimeout(() => {
        this._executeValidation(field, fieldName, timerKey);
      }, debounceMs)
    );
  }

  _executeValidation(field, fieldName, timerKey) {
    try {
      const formData = this.dataProcessor.processFormData(this.form);
      this.validator.formData = formData;

      const isValid = this.validator.validateField(fieldName);

      if (!isValid) {
        const errors = this.validator.getErrors();
        if (errors[fieldName]) {
          this.errorService.displayError(field, errors[fieldName]);
        }
      } else {
        this.errorService.clearError(field);
      }
    } catch (error) {
      logger.error("Real-time validation failed:", error);
    } finally {
      this.debounceTimers.delete(timerKey);
    }
  }

  clearDebounceTimers() {
    this.debounceTimers.forEach((timer) => clearTimeout(timer));
    this.debounceTimers.clear();
  }

  destroy() {
    this.disable();
  }
}
