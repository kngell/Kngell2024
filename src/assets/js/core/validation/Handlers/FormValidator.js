import BrowserLogger from "js/core/utils/logger";
import Validator from "js/core/validation/Validator";
import RealTimeValidator from "./RealTimeValidator";
import { EmptyBlock, FileDialogListItemButtonView } from "ckeditor5";

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

    // NEW: Decouple response handling
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

      logger.success(`FormValidator initialized for form: ${this.form.id || "anonymous"}`);
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

      // Now handle async safely
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

      // Custom submit handler
      if (this.customSubmitHandler) {
        return await this.customSubmitHandler(this.form, this);
      }

      // Handle submission based on mode
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

    // Temporarily remove listener
    if (this.boundHandleSubmit) {
      this.form.removeEventListener("submit", this.boundHandleSubmit);
    }

    // Create temporary form
    const tempForm = document.createElement("form");
    tempForm.method = this.form.method || "POST";
    tempForm.action = this.form.action;
    tempForm.style.display = "none";

    // Copy inputs
    const inputs = this.form.querySelectorAll("input, select, textarea");
    inputs.forEach((input) => {
      tempForm.appendChild(input.cloneNode(true));
    });

    document.body.appendChild(tempForm);
    tempForm.submit();

    // Re-attach listener
    setTimeout(() => {
      if (this.boundHandleSubmit) {
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
  // processAjaxResult(result) {
  //   if (typeof result === "string") {
  //     try {
  //       result = JSON.parse(result);
  //     } catch (e) {
  //       result = { success: false, error: "Invalid server response format." };
  //     }
  //   }

  //   logger.debug("Processing Ajax result:", result);

  //   const context = {
  //     form: this.form,
  //     result,
  //     validator: this,
  //     shouldRedirect: !!result.redirect,
  //     redirectUrl: result.redirect || null,
  //     redirectDelay: 1500
  //   };

  //   if (result.success === false) {
  //     const errorMessage = result.error || result.message || "An unknown error occurred.";

  //     // Dispatch event only - NO NOTIFICATION
  //     this.form.dispatchEvent(
  //       new CustomEvent("form:ajax-error", {
  //         detail: { result, context, error: new Error(errorMessage) },
  //         bubbles: true
  //       })
  //     );

  //     throw new Error(errorMessage);
  //   }

  //   // Dispatch success event - NO NOTIFICATION
  //   this.form.dispatchEvent(
  //     new CustomEvent("form:ajax-success", {
  //       detail: { result, context },
  //       bubbles: true
  //     })
  //   );

  //   if (context.shouldRedirect && context.redirectUrl) {
  //     setTimeout(() => {
  //       window.location.href = context.redirectUrl;
  //     }, context.redirectDelay);
  //   }

  //   return result;
  // }
  processAjaxResult(result) {
    if (typeof result === "string") {
      try {
        result = JSON.parse(result);
      } catch (e) {
        result = { success: false, error: "Invalid server response format." };
      }
    }

    logger.debug("Processing Ajax result:", result);

    const context = {
      form: this.form,
      result,
      validator: this,
      shouldRedirect: !!result.redirect,
      redirectUrl: result.redirect || null,
      redirectDelay: 1500
    };

    if (result.success === false) {
      const errorMessage = result.error || result.message || "An unknown error occurred.";

      // Display field-level server errors on their respective inputs
      if (result.errors && typeof result.errors === "object") {
        this.displayServerValidationErrors(result.errors);
      }

      // Dispatch error event — FormHandler shows top-level notification
      this.form.dispatchEvent(
        new CustomEvent("form:ajax-error", {
          detail: { result, context, error: new Error(errorMessage) },
          bubbles: true
        })
      );

      throw new Error(errorMessage);
    }

    // Dispatch success event
    this.form.dispatchEvent(
      new CustomEvent("form:ajax-success", {
        detail: { result, context },
        bubbles: true
      })
    );

    if (context.shouldRedirect && context.redirectUrl) {
      setTimeout(() => {
        window.location.href = context.redirectUrl;
      }, context.redirectDelay);
    }

    return result;
  }

  /**
   * Display server-side validation errors on form fields.
   * Uses the same ErrorDisplayService as client-side validation
   * for consistent error presentation.
   *
   * Server format: { "fieldName": ["<small class='...'>Message</small>"] }
   */
  displayServerValidationErrors(errors) {
    this.errorService.clearAllErrors(this.form);

    Object.entries(errors).forEach(([fieldName, errorData]) => {
      if (!Array.isArray(errorData) || errorData.length === 0) {
        return;
      }

      const htmlString = errorData[0];
      if (typeof htmlString !== "string") {
        return;
      }

      // Extract plain text from server HTML
      const message = this.extractTextFromHtml(htmlString);
      if (!message) {
        return;
      }

      const field = this.findField(fieldName);

      if (field) {
        // Reuse the same errorService that RealTimeValidator uses
        this.errorService.displayError(field, {
          message,
          classes: ["input-box__hint-text", "invalid-feedback"]
        });
      } else {
        logger.warn(`Server error — field not found: "${fieldName}"`, message);
      }
    });

    this.errorService.scrollToFirstError(this.form);
  }

  /**
   * Extract plain text from server-rendered HTML error string.
   */
  extractTextFromHtml(html) {
    const template = document.createElement("template");
    template.innerHTML = html.trim();
    return template.content.textContent?.trim() || "";
  }

  getEventType(result, context) {
    if (result.success === false) {
      if (this.isInfoOrWarning(result)) {
        return "form:ajax-warning";
      }
      return "form:ajax-error";
    }
    return "form:ajax-success";
  }

  isInfoOrWarning(result) {
    const type = result.type || "";
    return type === "info" || type === "warning";
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
    Object.entries(errors).forEach(([fieldName, error]) => {
      const field = this.findField(fieldName);
      if (field) {
        this.errorService.displayError(field, error);
      } else {
        logger.warn(`Field not found: ${fieldName}`, error);
        this.publishNotification(`${fieldName}: ${error.message || error}`, "error");
      }
    });

    this.errorService.scrollToFirstError(this.form);
  }

  findField(fieldName) {
    let field = this.form.querySelector(`[name="${fieldName}"]`);

    if (!field) {
      const escapedName = fieldName.replace(/\[/g, "\\[").replace(/\]/g, "\\]");
      field = this.form.querySelector(`[name="${escapedName}"]`);
    }

    if (!field) {
      field = this.form.querySelector(`[data-field="${fieldName}"]`);
    }

    return field;
  }

  displayServerErrors(errors) {
    Object.entries(errors).forEach(([fieldName, messages]) => {
      if (Array.isArray(messages) && messages.length > 0) {
        const field = this.findField(fieldName);
        if (field) {
          this.errorService.displayError(field, { message: messages[0] });
        }
      }
    });

    this.errorService.scrollToFirstError(this.form);
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

  showError(message) {
    this.publishNotification(message, "error");
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

    // Validate first
    const formData = this.dataProcessor.processFormData(this.form);
    this.validator.formData = formData;

    if (!this.validator.validateAll()) {
      this.displayErrors(this.validator.getErrors());
      throw new Error("Form validation failed");
    }

    // Handle submission
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

  /**
   * Add response processor
   */
  addResponseProcessor(processor) {
    if (processor && !this.responseProcessors.includes(processor)) {
      this.responseProcessors.push(processor);
    }
    return this;
  }

  /**
   * Remove response processor
   */
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
