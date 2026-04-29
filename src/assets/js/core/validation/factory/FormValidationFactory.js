import ValidationService from "js/core/validation/Handlers/ValidationService";
import FormDataProcessor from "js/core/validation/Handlers/FormDataProcessor";
import ErrorDisplayService from "js/core/validation/Handlers/ErrorDisplayService";
import FormValidator from "js/core/validation/Handlers/FormValidator";
import AjaxHandler from "js/core/utils/AjaxHandler";
import RedirectProcessor from "js/core/processors/RedirectProcessor";
import NotificationProcessor from "js/core/processors/NotificationProcessor";

export class FormValidationFactory {
  /**
   * Create a FormValidator instance.
   *
   * @param {HTMLFormElement} form
   * @param {Object} options
   * @returns {FormValidator}
   */
  static createFormValidator(form, options = {}) {
    const validationService = ValidationService.getInstance();
    const errorService = new ErrorDisplayService();
    const dataProcessor = new FormDataProcessor();

    const rulesName = options.rulesName || form.dataset.validationRules || "defaultRules";

    const ajaxHandler = this.resolveAjaxHandler(form, options);

    const submissionMode = options.submissionMode || form.dataset.submissionMode || "ajax";

    const responseProcessors = this.buildResponseProcessors(options);

    return new FormValidator({
      form,
      validationService,
      errorService,
      dataProcessor,
      rulesName,
      enableRealTime: options.enableRealTime !== false,
      ajaxHandler,
      submissionMode,
      customSubmitHandler: options.customSubmitHandler,
      ajaxOptions: options.ajaxOptions,
      responseProcessors,
      notificationPublisher: options.notificationPublisher || null
    });
  }

  static resolveAjaxHandler(form, options) {
    if (options.ajaxHandler === true) {
      return new AjaxHandler();
    }

    if (options.ajaxHandler && typeof options.ajaxHandler.request === "function") {
      return options.ajaxHandler;
    }

    if (form.dataset.ajaxHandler === "true") {
      return new AjaxHandler();
    }

    return null;
  }

  /**
   * Build response processors from options.
   * No auto-detection — caller provides what they need.
   */
  static buildResponseProcessors(options) {
    const processors = [];

    // Custom processors first (highest priority)
    if (Array.isArray(options.responseProcessors)) {
      processors.push(...options.responseProcessors);
    }

    // Redirect processor (unless disabled)
    if (options.enableRedirectProcessor !== false) {
      processors.push(
        new RedirectProcessor({
          delays: options.redirectDelays
        })
      );
    }

    // Notification processor (unless disabled)
    if (options.enableNotificationProcessor !== false) {
      processors.push(
        new NotificationProcessor(
          options.notificationPublisher || null,
          options.notificationOptions || {}
        )
      );
    }

    return processors;
  }

  /**
   * Create a FormValidator with AJAX enabled.
   */
  static createAjaxFormValidator(form, options = {}) {
    return this.createFormValidator(form, {
      ...options,
      submissionMode: "ajax",
      ajaxHandler: options.ajaxHandler !== false
    });
  }

  /**
   * Create a FormValidator for modal forms.
   * Typically disables redirect processor since modal
   * handles navigation.
   */
  static createModalFormValidator(form, options = {}) {
    return this.createFormValidator(form, {
      ...options,
      submissionMode: "ajax",
      ajaxHandler: options.ajaxHandler !== false,
      enableRealTime: options.enableRealTime !== false,
      enableRedirectProcessor: false
    });
  }

  /**
   * Create validators for all forms matching selector.
   */
  static createFormValidators(
    selector = 'form[data-validate="true"]',
    options = {},
    formSpecificOptions = null
  ) {
    const forms = document.querySelectorAll(selector);

    return Array.from(forms).map((form) => {
      const formOptions = {
        ...options,
        ...(formSpecificOptions ? formSpecificOptions(form) : {})
      };
      return this.createFormValidator(form, formOptions);
    });
  }

  /**
   * Initialize all forms on the page.
   * Each form provides its own config via data attributes.
   * No type detection — forms are self-describing.
   */
  static initializeAllForms() {
    const forms = document.querySelectorAll('form[data-validate="true"]');

    const validators = [];
    const promises = [];

    forms.forEach((form) => {
      try {
        const validator = this.createFormValidator(form, {
          submissionMode: form.dataset.submissionMode,
          ajaxHandler: form.dataset.ajaxHandler === "true",
          enableRealTime: form.dataset.realTime !== "false"
        });

        validators.push(validator);
        promises.push(validator.initialize());
      } catch (error) {
        console.error(`Failed to initialize form: ${form.id || "anonymous"}`, error);
      }
    });

    window.__formValidators = validators;
    return Promise.allSettled(promises);
  }

  /**
   * Quick form — no real-time validation.
   */
  static createQuickFormValidator(form, options = {}) {
    return this.createFormValidator(form, {
      ...options,
      enableRealTime: false
    });
  }
}
