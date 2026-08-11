import BrowserLogger from "js/utils/logger";

const logger = new BrowserLogger("NumericFormattingService");

export default class NumericFormattingService {
  constructor() {
    this.numericManagers = new WeakMap();
  }

  /**
   * Initialize numeric formatting for all forms on the page
   */
  initializeForAllForms() {
    const forms = document.querySelectorAll('form[data-validate="true"]');
    forms.forEach((form) => this.initializeForForm(form));
  }

  /**
   * Initialize numeric formatting for a specific form
   */
  initializeForForm(form) {
    try {
      const formContainer = this._getFormContainer(form);

      if (!formContainer) {
        logger.warn(
          `No suitable container found for numeric formatting in form: ${form.getAttribute("id") || "unnamed-form"}`
        );
        return null;
      }

      // Import FormNumericManager dynamically to avoid circular dependencies
      import("../Utils/FormNumericManager")
        .then(({ default: FormNumericManager }) => {
          const numericManager = new FormNumericManager(formContainer);
          this.numericManagers.set(form, numericManager);
          logger.debug(
            `Numeric formatting initialized for form: ${form.getAttribute("id") || "unnamed-form"}`
          );
        })
        .catch((error) => {
          logger.error(`Failed to load FormNumericManager:`, error);
          this._initializeFallbackFormatting(form);
        });
    } catch (error) {
      logger.error(`Error initializing numeric formatting:`, error);
      this._initializeFallbackFormatting(form);
    }
  }

  /**
   * Get numeric manager for a specific form
   */
  getManagerForForm(form) {
    return this.numericManagers.get(form);
  }

  /**
   * Unformat all numeric fields before form submission
   */
  prepareFormForSubmission(form) {
    const numericManager = this.getManagerForForm(form);

    if (numericManager && typeof numericManager._unformatAllFields === "function") {
      numericManager._unformatAllFields();
      return true;
    }

    // Fallback if no manager exists
    return this._manualUnformatNumericFields(form);
  }

  /**
   * Reformat all numeric fields (e.g., after failed validation)
   */
  reformatFormFields(form) {
    const numericManager = this.getManagerForForm(form);

    if (numericManager && typeof numericManager._applyToContainer === "function") {
      numericManager._applyToContainer(form);
      return true;
    }

    return false;
  }

  /**
   * Format a specific field value
   */
  formatFieldValue(field, value) {
    const config = this._getNumericConfig();
    if (!config) return value;

    const fieldName = field.name.toLowerCase();
    const shouldFormat = config.some((pattern) => fieldName.includes(pattern.toLowerCase()));

    if (shouldFormat && value) {
      return this._formatNumberWithSpaces(value);
    }

    return value;
  }

  /**
   * Unformat a specific field value
   */
  unformatFieldValue(field, value) {
    return value ? value.toString().replace(/\s/g, "") : value;
  }

  // ========== PRIVATE METHODS ==========

  _getFormContainer(form) {
    return (
      form.querySelector(".product__body-frm") ||
      form.querySelector('[data-numeric-formatting="true"]') ||
      form
    );
  }

  _getNumericConfig() {
    const configEl = document.getElementById("form-numeric-config");
    if (!configEl) return null;

    try {
      return JSON.parse(configEl.value) || [];
    } catch (error) {
      logger.error("Error parsing numeric config:", error);
      return null;
    }
  }

  _initializeFallbackFormatting(form) {
    logger.info("Using fallback numeric formatting for form:", form.getAttribute("id"));

    form.querySelectorAll("input[type='number']").forEach((input) => {
      this._bindFallbackEvents(input);
    });
  }

  _bindFallbackEvents(input) {
    const config = this._getNumericConfig();
    if (!config) return;

    const fieldName = input.name.toLowerCase();
    const shouldFormat = config.some((pattern) => fieldName.includes(pattern.toLowerCase()));

    if (!shouldFormat) return;

    // Set input mode for mobile
    input.inputMode = fieldName.includes("quantity") ? "numeric" : "decimal";

    // Format on blur
    input.addEventListener("blur", (e) => {
      if (e.target.value) {
        e.target.value = this._formatNumberWithSpaces(e.target.value);
      }
    });

    // Unformat on focus
    input.addEventListener("focus", (e) => {
      if (e.target.value) {
        e.target.value = this.unformatFieldValue(e.target, e.target.value);
        setTimeout(() => e.target.select(), 10);
      }
    });
  }

  _manualUnformatNumericFields(form) {
    const config = this._getNumericConfig();
    if (!config) return false;

    let unformattedCount = 0;

    form.querySelectorAll("input[type='number']").forEach((input) => {
      const fieldName = input.name.toLowerCase();
      const shouldUnformat = config.some((pattern) => fieldName.includes(pattern.toLowerCase()));

      if (shouldUnformat && input.value) {
        input.value = this.unformatFieldValue(input, input.value);
        unformattedCount++;
      }
    });

    logger.debug(`Manually unformatted ${unformattedCount} fields`);
    return unformattedCount > 0;
  }

  _formatNumberWithSpaces(value) {
    const stringValue = value.toString().replace(/\s/g, "");
    const parts = stringValue.split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    return parts.join(".");
  }
}
