import BrowserLogger from "js/core/utils/BrowserLogger";
import { FormValidationFactory } from "js/core/validation/factory/FormValidationFactory";
import NotificationChannel from "js/components/FeedbackChannel/NotificationChannel";
import BaseHandler from "./BaseHandler";

export default class FormHandler extends BaseHandler {
  constructor(form, options = {}) {
    const mergedOptions = {
      processors: {
        enabled: true,
        notification: {
          enabled: true,
          config: {
            permanentErrors: true
          }
        },
        redirect: {
          enabled: true,
          config: {
            redirectOnInsert: true,
            redirectOnUpdate: false,
            redirectOnDelete: true,
            operationDelays: {
              insert: 1500,
              update: 0,
              delete: 1500
            }
          }
        },
        custom: []
      },
      enableRedirectProcessor: options.enableRedirectProcessor !== false,
      ...options
    };

    // ✅ Pass to BaseHandler
    super({
      ...mergedOptions,
      loggerName: `FormHandler:${form.getAttribute("id") || "unknown"}`
    });

    this.form = form;

    // FormHandler-specific options
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
      notificationConfig: {
        error: { permanent: true, duration: 8000, position: "top-right" },
        success: { permanent: false, duration: 3000, position: "top-right" },
        warning: { permanent: false, duration: 5000, position: "top-right" },
        info: { permanent: false, duration: 5000, position: "top-right" }
      },
      componentsManager: null,
      customDataProcessors: [],
      onInitialize: null,
      processors: mergedOptions.processors,
      enableRedirectProcessor: mergedOptions.enableRedirectProcessor,
      ...mergedOptions
    };

    // Feedback channel
    if (options.feedbackChannel) {
      this.feedbackChannel = options.feedbackChannel;
      this._ownsFeedbackChannel = false;
      this.logger.debug(
        `Using provided feedback channel: ${this.feedbackChannel.constructor.name}`
      );
    } else {
      this.logger.debug("No channel provided, creating default NotificationChannel");
      this.feedbackChannel = new NotificationChannel({
        containerId: options.notificationContainerId || "app-notifications",
        position: options.notificationPosition,
        config: this.options.notificationConfig
      });
      this._ownsFeedbackChannel = true;
    }

    // Core state
    this.validator = null;
    this.isInitialized = false;
    this.submitButton = null;
    this.componentsManager = options.componentsManager || null;

    // Bound handlers
    this._boundErrorHandler = null;
    this._boundSuccessHandler = null;
    this._boundValidationStartHandler = null;
    this._boundValidationEndHandler = null;
  }

  async initialize() {
    if (this.isInitialized) return this;

    try {
      this.logger.debug("Initializing form handler");

      // ✅ Build processors using BaseHandler method
      const responseProcessors = this.buildProcessors();

      // ✅ Store processors for later use
      this.processors = responseProcessors;

      this.logger.debug(`Built ${responseProcessors.length} processors`, {
        names: responseProcessors.map((p) => p.constructor.name)
      });

      // ✅ Filter out RedirectProcessor for FormValidator (it will be handled by BaseHandler)
      const validatorProcessors = responseProcessors.filter(
        (p) => p.constructor.name !== "RedirectProcessor"
      );

      this.validator = FormValidationFactory.createFormValidator(this.form, {
        rulesName: this.options.rulesName,
        enableRealTime: this.options.enableRealTime,
        submissionMode: this.options.submissionMode,
        ajaxHandler: this.options.ajaxHandler,
        ajaxOptions: this.options.ajaxOptions,
        // ✅ Pass only non-redirect processors to FormValidator
        responseProcessors: validatorProcessors,
        notificationPublisher: this.options.notificationPublisher,
        // ✅ Disable internal processor creation
        enableRedirectProcessor: false,
        enableNotificationProcessor: false
      });

      if (this.componentsManager) {
        this.validator.componentsManager = this.componentsManager;
      }

      this.enhanceDataProcessing();
      await this.validator.initialize();
      this.setupEventHandlers();
      this.findSubmitButton();

      this.isInitialized = true;
      this.logger.success("Form handler initialized successfully");

      if (this.options.onInitialize) {
        this.options.onInitialize(this);
      }

      return this;
    } catch (error) {
      this.logger.error("Failed to initialize form handler:", error);
      throw error;
    }
  }

  enhanceDataProcessing() {
    if (!this.validator?.dataProcessor) return;

    const originalProcessFormData = this.validator.dataProcessor.processFormData;

    this.validator.dataProcessor.processFormData = (form) => {
      let data = originalProcessFormData.call(this.validator.dataProcessor, form);

      if (this.componentsManager) {
        data = this.componentsManager.getFormDataForComponents(data);
      }

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

    this._boundErrorHandler = (e) => {
      const { result, context, error } = e.detail || {};
      this.logger.error("Form submission failed:", error || result);

      const operation = this._determineOperation(result, context);
      const status = error?.status ?? result?.status ?? null;

      const errorResult = result || {
        success: false,
        error: error?.message || result?.error || "An error occurred",
        status: status
      };

      const errorContext = {
        ...context,
        operation: operation,
        isError: true,
        originalError: error
      };

      // ✅ Process error through BaseHandler
      this.processResponse(errorResult, errorContext).catch((err) => {
        this.logger.debug("Error response processed by BaseHandler:", err);
      });

      if (this.options.onError) {
        const errorObj = {
          message: error?.message || result?.error || "An error occurred",
          original: error || result,
          result,
          context,
          status,
          isValidation: this._isValidationError(result, error)
        };
        this.options.onError(errorObj, this);
      }
    };

    this._boundSuccessHandler = (e) => {
      const { result, context } = e.detail || {};
      this.logger.debug("Form submission successful", { result, context });

      // ✅ Determine operation
      const operation = this._determineOperation(result, context);

      // ✅ Pass to processResponse - this triggers redirect processor
      this.processResponse(result, {
        ...context,
        operation: operation,
        isDeletion: operation === "delete" || operation === "destroy"
      })
        .then(() => {
          if (this.options.onSuccess) {
            this.options.onSuccess(result, context, this);
          }
        })
        .catch((err) => {
          this.logger.warn("Response processing failed:", err);
        });
    };

    this._boundValidationStartHandler = () => this.setSubmitButtonLoading(true);
    this._boundValidationEndHandler = () => this.setSubmitButtonLoading(false);

    this.validator.form.addEventListener("form:ajax-error", this._boundErrorHandler);
    this.validator.form.addEventListener("form:ajax-success", this._boundSuccessHandler);
    this.validator.form.addEventListener(
      "form:validation-start",
      this._boundValidationStartHandler
    );
    this.validator.form.addEventListener("form:validation-end", this._boundValidationEndHandler);
  }

  _isValidationError(result, error) {
    const status = error?.status || result?.status;
    if (status === 422) return true;
    if (result?.errors && typeof result.errors === "object") return true;
    if (result?.validationErrors) return true;
    return false;
  }

  findSubmitButton() {
    const internal = this.form.querySelector('button[type="submit"], input[type="submit"]');
    if (internal) {
      this.submitButton = internal;
      return internal;
    }

    const formId = this.form.getAttribute("id");
    if (formId) {
      const external = document.querySelector(
        `button[form="${CSS.escape(formId)}"][type="submit"]`
      );
      if (external) this.submitButton = external;
      return external;
    }

    return null;
  }

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

  enableRealTimeValidation() {
    if (this.validator?.realTimeValidator) {
      this.validator.realTimeValidator.enable();
      this.logger.debug("Real-time validation enabled");
    }
  }

  disableRealTimeValidation() {
    if (this.validator?.realTimeValidator) {
      this.validator.realTimeValidator.disable();
      this.logger.debug("Real-time validation disabled");
    }
  }

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

  validateField(fieldName) {
    if (!this.validator) return false;
    return this.validator.validator.validateField(fieldName);
  }

  async submit() {
    if (!this.validator) {
      throw new Error("FormHandler not initialized");
    }
    this.enableRealTimeValidation();
    return this.validator.handleSubmit(new Event("submit", { bubbles: true, cancelable: true }));
  }

  clearAllErrors() {
    if (this.validator?.errorService) {
      this.validator.errorService.clearAllErrors();
    }
  }

  clearFieldError(field) {
    if (this.validator?.errorService) {
      this.validator.errorService.clearError(field);
    }
  }

  getFormData() {
    if (this.validator?.dataProcessor) {
      return this.validator.dataProcessor.processFormData(this.form);
    }
    return new FormData(this.form);
  }

  setCustomData(key, value) {
    if (!this._customData) this._customData = {};
    this._customData[key] = value;
  }

  updateProcessorConfig(processorType, config) {
    if (this.validator?.responseProcessors) {
      const processor = this.validator.responseProcessors.find(
        (p) => p.constructor.name === `${processorType}Processor`
      );
      if (processor && processor.options) {
        Object.assign(processor.options, config);
        this.logger.debug(`Updated ${processorType}Processor config`, config);
      }
    }
  }

  getStatus() {
    return {
      isInitialized: this.isInitialized,
      hasValidator: !!this.validator,
      formId: this.form.getAttribute("id"),
      formAction: this.form.action,
      rulesName: this.options.rulesName,
      hasComponentsManager: !!this.componentsManager,
      enableRedirectProcessor: this.options.enableRedirectProcessor,
      processors: this.validator?.responseProcessors?.map((p) => p.constructor.name) || []
    };
  }

  destroy() {
    if (this.validator?.form) {
      const form = this.validator.form;
      if (this._boundErrorHandler)
        form.removeEventListener("form:ajax-error", this._boundErrorHandler);
      if (this._boundSuccessHandler)
        form.removeEventListener("form:ajax-success", this._boundSuccessHandler);
      if (this._boundValidationStartHandler)
        form.removeEventListener("form:validation-start", this._boundValidationStartHandler);
      if (this._boundValidationEndHandler)
        form.removeEventListener("form:validation-end", this._boundValidationEndHandler);
    }

    this._boundErrorHandler = null;
    this._boundSuccessHandler = null;
    this._boundValidationStartHandler = null;
    this._boundValidationEndHandler = null;

    if (this.validator) {
      this.validator.destroy();
      this.validator = null;
    }

    if (this._ownsFeedbackChannel) {
      this.feedbackChannel?.destroy?.();
    }
    this.feedbackChannel = null;

    this.isInitialized = false;
    this.submitButton = null;
    this.componentsManager = null;

    // Call parent destroy
    super.destroy();

    this.logger.debug("Form handler destroyed");
  }
}
