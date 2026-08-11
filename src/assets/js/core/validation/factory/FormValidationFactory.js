import ValidationService from "js/core/validation/Handlers/ValidationService";
import FormDataProcessor from "js/core/validation/Handlers/FormDataProcessor";
import ErrorDisplayService from "js/core/validation/Handlers/ErrorDisplayService";
import FormValidator from "js/core/validation/Handlers/FormValidator";
import AjaxHandler from "js/core/utils/AjaxHandler";

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

    // ✅ Only custom processors - no RedirectProcessor or NotificationProcessor
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
   * Build response processors.
   * ✅ ONLY custom processors - FormValidator does NOT handle redirects or notifications
   * RedirectProcessor is handled by BaseHandler
   * NotificationProcessor/MessageHandler handle flash messages
   */
  static buildResponseProcessors(options) {
    const processors = [];

    // ✅ Custom processors only (entity events, etc.)
    if (Array.isArray(options.responseProcessors)) {
      processors.push(...options.responseProcessors);
    }

    // ❌ REMOVED: RedirectProcessor - handled by BaseHandler
    // ❌ REMOVED: NotificationProcessor - handled by MessageHandler

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
   */
  static createModalFormValidator(form, options = {}) {
    return this.createFormValidator(form, {
      ...options,
      submissionMode: "ajax",
      ajaxHandler: options.ajaxHandler !== false,
      enableRealTime: options.enableRealTime !== false
    });
  }

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
        console.error(
          `Failed to initialize form: ${form.getAttribute("id") || "anonymous"}`,
          error
        );
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
