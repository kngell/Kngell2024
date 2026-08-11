import BrowserLogger from "js/core/utils/BrowserLogger";
import Validator from "js/core/validation/Validator";
import RealTimeValidator from "./RealTimeValidator";

const logger = new BrowserLogger("FormValidator");

export default class FormValidator {
  constructor(options) {
    this.form = options.form;
    this.validationService = options.validationService;
    this.errorService = options.errorService;
    this.dataProcessor = options.dataProcessor;
    this.rulesName = options.rulesName;
    this.enableRealTime = options.enableRealTime;

    // AjaxHandler is optional
    this.ajaxHandler = options.ajaxHandler || null;
    this.submissionMode = options.submissionMode || "ajax";
    this.customSubmitHandler = options.customSubmitHandler || null;
    this.ajaxOptions = options.ajaxOptions || {};

    // Decoupled response handling
    this.responseProcessors = options.responseProcessors || [];
    this.notificationPublisher = options.notificationPublisher || null;

    // State
    this.validator = null;
    this.realTimeValidator = null;
    this.isSubmitting = false;
    this.boundHandleSubmit = null;
    this.originalFormSubmit = null;
  }

  async initialize() {
    try {
      logger.debug(`Initializing form validator for: ${this.rulesName}`);

      const { rules, settings } = await this.validationService.load(this.rulesName);
      this.validator = new Validator(rules, {}, settings);

      // Attach to form
      this.form._validator = this.validator;
      this.form._formValidator = this;

      if (this.enableRealTime) {
        this.setupRealTimeValidation();
      }

      this.setupFormSubmission();
      this.form.dataset.validatorInitialized = "true";

      logger.success(
        `FormValidator initialized for form: ${this.form.getAttribute("id") || "anonymous"}`
      );
    } catch (error) {
      logger.error("Failed to initialize FormValidator:", error);
      throw error;
    }
  }

  setupRealTimeValidation() {
    this.realTimeValidator = new RealTimeValidator({
      form: this.form,
      validator: this.validator,
      dataProcessor: this.dataProcessor,
      errorService: this.errorService
    });
    this.realTimeValidator.enable();
  }

  setupFormSubmission() {
    if (this.boundHandleSubmit) {
      this.form.removeEventListener("submit", this.boundHandleSubmit);
    }

    this.boundHandleSubmit = (e) => {
      // MUST be synchronous to prevent page reload
      e.preventDefault();
      e.stopPropagation();

      this.handleSubmit(e).catch((error) => {
        if (error.message === "Form validation failed") {
          logger.debug("Validation failed - errors already displayed to user");
        } else {
          logger.error("Form submission error:", error);
          this.form.dispatchEvent(
            new CustomEvent("form:ajax-error", {
              detail: { result: null, context: { form: this.form }, error },
              bubbles: true
            })
          );
        }
      });
    };

    if (this.form.onsubmit) {
      this.originalFormSubmit = this.form.onsubmit;
    }

    this.form.onsubmit = null;
    this.form.addEventListener("submit", this.boundHandleSubmit);
  }

  async handleSubmit(event) {
    if (this.isSubmitting) {
      logger.warn("Form is already submitting");
      return;
    }

    try {
      this.isSubmitting = true;
      this.errorService.clearAllErrors(this.form);

      // Validate
      const formData = this.dataProcessor.processFormData(this.form);
      this.validator.formData = formData;

      if (!this.validator.validateAll()) {
        const errors = this.validator.getErrors();
        this.displayErrors(errors);
        throw new Error("Form validation failed");
      }

      if (this.customSubmitHandler) {
        return await this.customSubmitHandler(this.form, this);
      }

      return await this.handleSubmissionBasedOnMode();
    } finally {
      this.isSubmitting = false;
    }
  }

  async handleSubmissionBasedOnMode() {
    switch (this.submissionMode) {
      case "direct":
        await this.submitDirect();
        break;
      case "ajax":
        await this.submitViaAjax();
        break;
      case "mixed":
        try {
          await this.submitViaAjax();
        } catch (error) {
          logger.warn("AJAX submission failed, falling back to direct:", error);
          await this.submitDirect();
        }
        break;
      default:
        throw new Error(`Unknown submission mode: ${this.submissionMode}`);
    }
  }

  async submitDirect() {
    logger.debug("Submitting form directly");

    if (this.boundHandleSubmit) {
      this.form.removeEventListener("submit", this.boundHandleSubmit);
    }

    const tempForm = document.createElement("form");
    tempForm.method = this.form.method || "POST";
    tempForm.action = this.form.action;
    tempForm.style.display = "none";

    const inputs = this.form.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => {
      tempForm.appendChild(input.cloneNode(true));
    });

    document.body.appendChild(tempForm);
    tempForm.submit();

    // Re-attach listener (only if validator was not destroyed in the meantime)
    setTimeout(() => {
      if (this.boundHandleSubmit && this.form && this.form.isConnected) {
        this.form.addEventListener("submit", this.boundHandleSubmit);
      }
    }, 100);

    return { success: true, mode: "direct" };
  }

  async submitViaAjax() {
    if (!this.ajaxHandler) {
      throw new Error("AJAX submission requires an AjaxHandler instance");
    }

    const hasFileUploads = this.hasFileUploads();
    const result = hasFileUploads
      ? await this.submitFileUploadForm()
      : await this.submitRegularForm();

    return this.processAjaxResult(result);
  }

  async submitRegularForm() {
    return await this.ajaxHandler.postForm(this.form.action, this.form, {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json",
        ...this.ajaxOptions.headers
      },
      timeout: this.ajaxOptions.timeout || 30000,
      ...this.ajaxOptions
    });
  }

  async submitFileUploadForm() {
    const formData = new FormData(this.form);
    return await this.ajaxHandler.post(this.form.action, formData, {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        Accept: "application/json",
        ...this.ajaxOptions.headers
      },
      timeout: this.ajaxOptions.timeout || 30000,
      ...this.ajaxOptions
    });
  }

  processAjaxResult(result) {
    if (typeof result === "string") {
      try {
        result = JSON.parse(result);
      } catch (e) {
        result = { success: false, error: "Invalid server response format." };
      }
    }

    logger.debug("Processing Ajax result:", result);

    // Build context — processors can mutate this
    const context = {
      form: this.form,
      result,
      validator: this,
      shouldRedirect: !!result.redirect,
      redirectUrl: result.redirect || null,
      redirectDelay: 1500,
      preventDefault: false,
      metadata: {}
    };

    // Run response processors (each may mutate context)
    for (const processor of this.responseProcessors) {
      try {
        if (typeof processor.canHandle === "function" && !processor.canHandle(context)) {
          continue;
        }
        if (typeof processor.handle === "function") {
          processor.handle(context);
        }
      } catch (err) {
        logger.error("Response processor threw:", err);
      }
    }

    // ─── Error path ───
    if (result.success === false) {
      const errorMessage = result.error || result.message || "An unknown error occurred.";

      if (result.errors && typeof result.errors === "object") {
        this.displayServerValidationErrors(result.errors);
      }

      // Build a richer Error object so consumers can read .status / .result
      const err = new Error(errorMessage);
      err.status = result.status ?? null;
      err.result = result;

      this.form.dispatchEvent(
        new CustomEvent("form:ajax-error", {
          detail: { result, context, error: err },
          bubbles: true
        })
      );

      throw err;
    }

    // ─── Success path ───
    this.form.dispatchEvent(
      new CustomEvent("form:ajax-success", {
        detail: { result, context },
        bubbles: true
      })
    );

    // if (context.shouldRedirect && context.redirectUrl && !context.preventDefault) {
    //   setTimeout(() => {
    //     // window.location.href = context.redirectUrl;
    //   }, context.redirectDelay);
    // } else if (context.preventDefault) {
    //   logger.debug("Redirect skipped: preventDefault set by processor");
    // }

    return result;
  }

  displayServerValidationErrors(errors) {
    this.errorService.clearAllErrors(this.form);

    Object.entries(errors).forEach(([fieldName, errorData]) => {
      if (!Array.isArray(errorData) || errorData.length === 0) return;

      const htmlString = errorData[0];
      if (typeof htmlString !== "string") return;

      const message = this.extractTextFromHtml(htmlString);
      if (!message) return;

      const field = this.findField(fieldName);

      if (field) {
        this.errorService.displayError(field, {
          message,
          classes: ["input-box__hint-text", "invalid-feedback"]
        });
      } else {
        logger.warn(`Server error — field not found: "${fieldName}"`, message);
      }
    });

    // Delay scroll for same reason
    setTimeout(() => {
      this.errorService.scrollToFirstError(this.form);
    }, 50);
  }

  extractTextFromHtml(html) {
    const template = document.createElement("template");
    template.innerHTML = html.trim();
    return template.content.textContent?.trim() || "";
  }

  // ============ VALIDATION METHODS ============

  validateField(field) {
    const formData = this.dataProcessor.processFormData(this.form);
    this.validator.formData = formData;

    const isValid = this.validator.validateField(field.name);

    if (!isValid) {
      const errors = this.validator.getErrors();
      if (errors[field.name]) {
        this.errorService.displayError(field, errors[field.name]);
      }
    } else {
      this.errorService.clearError(field);
    }

    return isValid;
  }

  getFieldError(fieldName) {
    const formData = this.dataProcessor.processFormData(this.form);
    this.validator.formData = formData;
    this.validator.validateField(fieldName);
    const errors = this.validator.getErrors();
    return errors[fieldName];
  }

  displayErrors(errors) {
    // First, display all errors
    Object.entries(errors).forEach(([fieldName, error]) => {
      const field = this.findField(fieldName);
      if (field) {
        this.errorService.displayError(field, error);
      } else {
        logger.warn(`Field not found: ${fieldName}`, error);
      }
    });

    // Then scroll to first error with a slight delay to ensure DOM is updated
    setTimeout(() => {
      this.errorService.scrollToFirstError(this.form);
    }, 50);
  }

  findField(fieldName) {
    let field = this.form.querySelector(`[name="${fieldName}"]`);

    if (!field) {
      const escapedName = fieldName.replace(/$$/g, "\\[").replace(/$$/g, "\\]");
      field = this.form.querySelector(`[name="${escapedName}"]`);
    }

    if (!field) {
      field = this.form.querySelector(`[data-field="${fieldName}"]`);
    }

    return field;
  }

  // ============ UI METHODS ============

  setSubmitButtonState(submitting) {
    const button = this.form.querySelector('[type="submit"]');
    if (!button) return;

    if (submitting) {
      if (!button.dataset.originalText) {
        button.dataset.originalText = button.textContent;
      }
      button.disabled = true;
      button.textContent = "Submitting...";
      button.classList.add("processing");
    } else {
      if (button.dataset.originalText) {
        button.textContent = button.dataset.originalText;
        delete button.dataset.originalText;
      }
      button.disabled = false;
      button.classList.remove("processing");
    }
  }

  // ============ PUBLIC API METHODS ============

  async validate() {
    const formData = this.dataProcessor.processFormData(this.form);
    this.validator.formData = formData;

    if (!this.validator.validateAll()) {
      this.displayErrors(this.validator.getErrors());
      return false;
    }

    return true;
  }

  async submit(options = {}) {
    const mergedOptions = {
      mode: this.submissionMode,
      ...options
    };

    const formData = this.dataProcessor.processFormData(this.form);
    this.validator.formData = formData;

    if (!this.validator.validateAll()) {
      this.displayErrors(this.validator.getErrors());
      throw new Error("Form validation failed");
    }

    if (mergedOptions.mode === "direct") {
      return await this.submitDirect();
    } else if (mergedOptions.mode === "ajax") {
      return await this.submitViaAjax();
    } else {
      return await this.handleSubmissionBasedOnMode();
    }
  }

  clearErrors() {
    this.errorService.clearAllErrors(this.form);
  }

  scrollToFirstError() {
    return this.errorService.scrollToFirstError(this.form);
  }

  hasFileUploads() {
    const fileInputs = this.form.querySelectorAll('input[type="file"]');
    return Array.from(fileInputs).some((input) => input.files.length > 0);
  }

  addResponseProcessor(processor) {
    if (processor && !this.responseProcessors.includes(processor)) {
      this.responseProcessors.push(processor);
    }
    return this;
  }

  removeResponseProcessor(processor) {
    const index = this.responseProcessors.indexOf(processor);
    if (index !== -1) {
      this.responseProcessors.splice(index, 1);
    }
    return this;
  }

  // ============ DESTROY ============

  destroy() {
    if (this.realTimeValidator) {
      this.realTimeValidator.destroy();
    }

    if (this.boundHandleSubmit) {
      this.form.removeEventListener("submit", this.boundHandleSubmit);
    }

    if (this.originalFormSubmit) {
      this.form.onsubmit = this.originalFormSubmit;
    }

    delete this.form._validator;
    delete this.form._formValidator;
    delete this.form.dataset.validatorInitialized;

    this.responseProcessors = [];

    logger.debug("FormValidator destroyed");
  }
}
