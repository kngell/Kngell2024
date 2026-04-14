import BrowserLogger from "js/core/utils/BrowserLogger";
import { FormValidationFactory } from "js/core/validation/factory/FormValidationFactory";
import { getNotificationManager } from "js/components/Managers/NotificationManager";

export default class FormHandler {
  constructor(form, options = {}) {
    this.form = form;
    this.options = {
      rulesName: form.dataset.validationRules || "defaultRules",
      enableRealTime: true,
      submissionMode: "ajax",
      ajaxHandler: true,
      ajaxOptions: {
        timeout: 30000,
        json: true,
        headers: {}
      },
      responseProcessors: [],
      enableRedirectProcessor: true,
      redirectDelays: {
        success: 1500,
        error: 1000,
        warning: 2500,
        info: 2000,
        danger: 1000
      },
      enableNotificationProcessor: true,
      componentsManager: null,
      customDataProcessors: [],
      onSuccess: null,
      onError: null,
      onInitialize: null,

      // NEW: Notification configuration
      notificationConfig: {
        error: {
          permanent: true, // Make errors permanent by default
          duration: 8000, // Fallback if not permanent
          position: "top-right"
        },
        success: {
          permanent: false,
          duration: 3000,
          position: "top-right"
        },
        warning: {
          permanent: false,
          duration: 5000,
          position: "top-right"
        },
        info: {
          permanent: false,
          duration: 5000,
          position: "top-right"
        },
        ...options.notificationConfig
      },
      ...options
    };

    this.logger = new BrowserLogger(`FormHandler:${form.id || "unknown"}`);
    this.validator = null;
    this.isInitialized = false;
    this.submitButton = null;
    this.componentsManager = options.componentsManager || null;

    // Initialize notification manager with form-specific config
    this.notificationManager = getNotificationManager({
      containerId: options.notificationContainerId || "app-notifications",
      position: options.notificationPosition || this.options.notificationConfig.error.position,
      ...options.notificationConfig
    });
  }

  /**
   * Initialize the form handler with validation
   */
  async initialize() {
    if (this.isInitialized) return this;

    try {
      this.logger.debug("Initializing form handler");

      // Create validator with all options
      this.validator = FormValidationFactory.createFormValidator(this.form, {
        rulesName: this.options.rulesName,
        enableRealTime: this.options.enableRealTime,
        submissionMode: this.options.submissionMode,
        ajaxHandler: this.options.ajaxHandler,
        ajaxOptions: this.options.ajaxOptions,
        responseProcessors: this.options.responseProcessors,
        notificationPublisher: this.options.notificationPublisher,
        enableRedirectProcessor: this.options.enableRedirectProcessor,
        redirectDelays: this.options.redirectDelays,
        notificationOptions: this.options.notificationOptions,
        enableNotificationProcessor: this.options.enableNotificationProcessor
      });

      // Attach components manager if provided
      if (this.componentsManager) {
        this.validator.componentsManager = this.componentsManager;
      }

      // Enhance data processing
      this.enhanceDataProcessing();

      // Initialize validator
      await this.validator.initialize();

      // Setup custom event handlers
      this.setupEventHandlers();

      // Find and store submit button
      this.findSubmitButton();

      this.isInitialized = true;
      this.logger.success("Form handler initialized successfully");

      // Call onInitialize callback
      if (this.options.onInitialize) {
        this.options.onInitialize(this);
      }

      return this;
    } catch (error) {
      this.logger.error("Failed to initialize form handler:", error);
      throw error;
    }
  }

  /**
   * Enhance data processing with custom processors
   */
  enhanceDataProcessing() {
    if (!this.validator?.dataProcessor) return;

    const originalProcessFormData = this.validator.dataProcessor.processFormData;

    this.validator.dataProcessor.processFormData = (form) => {
      // Get base data
      let data = originalProcessFormData.call(this.validator.dataProcessor, form);

      // Apply components manager enhancement
      if (this.componentsManager) {
        data = this.componentsManager.getFormDataForComponents(data);
      }

      // Apply custom data processors
      if (this.options.customDataProcessors.length > 0) {
        this.options.customDataProcessors.forEach((processor) => {
          if (typeof processor === "function") {
            data = processor(data, form, this);
          }
        });
      }

      return data;
    };
  }

  setupEventHandlers() {
    if (!this.validator?.form) return;

    // Add flag to prevent duplicate processing
    let isProcessing = false;

    // Error handler with configurable notifications
    this.validator.form.addEventListener("form:ajax-error", (e) => {
      if (isProcessing) return;
      isProcessing = true;
      setTimeout(() => {
        isProcessing = false;
      }, 500);

      const { result, context, error } = e.detail;

      this.logger.error("Form submission failed:", error || result);

      // Extract meaningful error message
      let errorMessage = "An error occurred while saving. Please try again.";
      if (result && result.error) {
        errorMessage = result.error;
      } else if (result && result.message) {
        errorMessage = result.message;
      } else if (error && error.message) {
        errorMessage = error.message;
      }

      // SHOW ERROR NOTIFICATION with form-specific config
      const errorConfig = this.options.notificationConfig.error;
      this.notificationManager.error(errorMessage, {
        permanent: errorConfig.permanent,
        duration: errorConfig.duration,
        position: errorConfig.position
      });

      // Create error object for callback
      const errorObj = {
        message: errorMessage,
        original: error || result,
        result: result,
        context: context
      };

      // Call onError callback
      if (this.options.onError) {
        this.options.onError(errorObj, this);
      }
    });

    // Success handler with configurable notifications
    this.validator.form.addEventListener("form:ajax-success", (e) => {
      const { result, context } = e.detail;
      this.logger.debug("Form submission successful", { result, context });

      // SHOW SUCCESS NOTIFICATION with form-specific config
      if (result.success !== false) {
        const successMessage = result.message || "Saved successfully.";
        const successConfig = this.options.notificationConfig.success;
        this.notificationManager.success(successMessage, {
          permanent: successConfig.permanent,
          duration: successConfig.duration,
          position: successConfig.position
        });
      }

      // Call onSuccess callback
      if (this.options.onSuccess) {
        this.options.onSuccess(result, context, this);
      }
    });

    // Validation events
    this.validator.form.addEventListener("form:validation-start", (e) => {
      this.logger.debug("Validation started");
      this.setSubmitButtonLoading(true);
    });

    this.validator.form.addEventListener("form:validation-end", (e) => {
      this.logger.debug("Validation ended");
      this.setSubmitButtonLoading(false);
    });
  }
  /**
   * Find and store the submit button
   */
  findSubmitButton() {
    this.submitButton = this.form.querySelector(
      'button[type="submit"], input[type="submit"], .btn-primary, [data-submit="true"]'
    );
  }

  /**
   * Set submit button loading state
   */
  setSubmitButtonLoading(isLoading) {
    if (!this.submitButton) return;

    if (isLoading) {
      this.submitButton.classList.add("loading");
      this.submitButton.setAttribute("disabled", "");
      if (this.submitButton.tagName === "BUTTON" || this.submitButton.tagName === "INPUT") {
        this.submitButton.dataset.originalText =
          this.submitButton.textContent || this.submitButton.value;
        this.submitButton.textContent = "Submitting...";
      }
    } else {
      this.submitButton.classList.remove("loading");
      this.submitButton.removeAttribute("disabled");
      if (this.submitButton.dataset.originalText) {
        this.submitButton.textContent = this.submitButton.dataset.originalText;
        delete this.submitButton.dataset.originalText;
      }
    }
  }

  /**
   * Enable real-time validation
   */
  enableRealTimeValidation() {
    if (this.validator?.realTimeValidator) {
      this.validator.realTimeValidator.enable();
      this.logger.debug("Real-time validation enabled");
    }
  }

  /**
   * Disable real-time validation
   */
  disableRealTimeValidation() {
    if (this.validator?.realTimeValidator) {
      this.validator.realTimeValidator.disable();
      this.logger.debug("Real-time validation disabled");
    }
  }

  /**
   * Validate the entire form
   */
  async validateAll() {
    if (!this.validator) return false;

    try {
      const formData = this.validator.dataProcessor.processFormData(this.form);
      this.validator.validator.formData = formData;
      return await this.validator.validator.validateAll();
    } catch (error) {
      this.logger.error("Validation failed:", error);
      return false;
    }
  }

  /**
   * Validate a specific field
   */
  validateField(fieldName) {
    if (!this.validator) return false;
    return this.validator.validator.validateField(fieldName);
  }

  /**
   * Submit the form programmatically
   */
  async submit() {
    if (!this.validator) return;

    // Enable real-time validation for submission
    this.enableRealTimeValidation();

    // Trigger form submission
    const submitEvent = new Event("submit", {
      bubbles: true,
      cancelable: true
    });
    this.form.dispatchEvent(submitEvent);
  }

  /**
   * Clear all errors
   */
  clearAllErrors() {
    if (this.validator?.errorService) {
      this.validator.errorService.clearAllErrors();
    }
  }

  /**
   * Clear error for a specific field
   */
  clearFieldError(field) {
    if (this.validator?.errorService) {
      this.validator.errorService.clearError(field);
    }
  }

  /**
   * Get form data
   */
  getFormData() {
    if (this.validator?.dataProcessor) {
      return this.validator.dataProcessor.processFormData(this.form);
    }
    return new FormData(this.form);
  }

  /**
   * Set custom data for submission
   */
  setCustomData(key, value) {
    if (!this._customData) {
      this._customData = {};
    }
    this._customData[key] = value;
  }

  /**
   * Destroy the form handler
   */
  destroy() {
    if (this.validator) {
      this.validator.destroy();
      this.validator = null;
    }

    this.isInitialized = false;
    this.submitButton = null;
    this.componentsManager = null;
    this.logger.debug("Form handler destroyed");
  }

  /**
   * Get validation status
   */
  getStatus() {
    return {
      isInitialized: this.isInitialized,
      hasValidator: !!this.validator,
      formId: this.form.id,
      formAction: this.form.action,
      rulesName: this.options.rulesName,
      hasComponentsManager: !!this.componentsManager
    };
  }
}
