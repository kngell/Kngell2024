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
  }

  enable() {
    this.setupEventListeners();
  }

  disable() {
    this.removeEventListeners();
    this.clearDebounceTimers();
  }

  setupEventListeners() {
    const handleBlur = (e) => {
      if (this.shouldValidate(e.target)) {
        this.validateField(e.target);
      }
    };

    const handleInput = (e) => {
      if (this.shouldValidate(e.target)) {
        this.errorService.clearError(e.target);
      }
    };

    const handleChange = (e) => {
      if (this.shouldValidate(e.target)) {
        this.validateField(e.target, 300); // Debounce for change events
      }
    };

    // Use event delegation on the form
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
    return (
      field.matches("input, select, textarea") &&
      field.form === this.form &&
      field.name &&
      !field.hasAttribute("data-skip-validation")
    );
  }

  validateField(field, debounceMs = 0) {
    const fieldName = field.name;
    const timerKey = `${this.form.id || "form"}-${fieldName}`;

    // Clear existing timer
    if (this.debounceTimers.has(timerKey)) {
      clearTimeout(this.debounceTimers.get(timerKey));
    }

    // Set new timer
    this.debounceTimers.set(
      timerKey,
      setTimeout(() => {
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
      }, debounceMs)
    );
  }

  clearDebounceTimers() {
    this.debounceTimers.forEach((timer) => clearTimeout(timer));
    this.debounceTimers.clear();
  }

  destroy() {
    this.disable();
  }
}
