import ValidationService from "js/core/validation/Handlers/ValidationService";
import FormDataProcessor from "js/core/validation/Handlers/FormDataProcessor";
import ErrorDisplayService from "js/core/validation/Handlers/ErrorDisplayService";
import FormValidator from "js/core/validation/Handlers/FormValidator";
import AjaxHandler from "js/core/utils/AjaxHandler";

// Import default processors
import RedirectProcessor from "js/core/processors/RedirectProcessor";
import NotificationProcessor from "js/core/processors/NotificationProcessor";

export class FormValidationFactory {
  /**
   * Create a FormValidator instance with enhanced AJAX capabilities
   * @param {HTMLFormElement} form - The form element
   * @param {Object} options - Configuration options
   * @param {string} options.rulesName - Name of validation rules
   * @param {boolean} options.enableRealTime - Enable real-time validation
   * @param {string} options.submissionMode - 'ajax', 'direct', or 'mixed'
   * @param {AjaxHandler|boolean} options.ajaxHandler - Optional AjaxHandler instance or true to auto-create
   * @param {Function} options.customSubmitHandler - Optional custom submission handler
   * @param {Object} options.ajaxOptions - Default AJAX options
   * @param {Array} options.responseProcessors - Array of response processors
   * @param {Object} options.notificationPublisher - Notification publisher instance
   * @returns {FormValidator}
   */
  static createFormValidator(form, options = {}) {
    const validationService = ValidationService.getInstance();
    const errorService = new ErrorDisplayService();
    const dataProcessor = new FormDataProcessor();

    const rulesName = options.rulesName || form.dataset.validationRules || "productRules";

    // Determine AJAX handler
    let ajaxHandler = null;
    if (options.ajaxHandler === true) {
      ajaxHandler = new AjaxHandler();
    } else if (options.ajaxHandler && typeof options.ajaxHandler.request === "function") {
      ajaxHandler = options.ajaxHandler;
    } else if (form.dataset.ajaxHandler === "true") {
      ajaxHandler = new AjaxHandler();
    }

    // Determine submission mode
    const submissionMode = options.submissionMode || form.dataset.submissionMode || "ajax";

    // Build response processors
    const responseProcessors = this.buildResponseProcessors(options, form);

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
      notificationPublisher: options.notificationPublisher || null,
    });
  }

  static buildResponseProcessors(options, form) {
    const processors = [];

    // Add custom processors from options
    if (options.responseProcessors && Array.isArray(options.responseProcessors)) {
      processors.push(...options.responseProcessors);
    }

    // Add default processors based on form type
    const isProductDeletion = form.action && form.action.includes("/product/delete/");

    if (isProductDeletion) {
      // Lazy load product deletion processor only when needed
      import("js/core/processors/ProductDeletionProcessor")
        .then((module) => {
          const ProductDeletionProcessor = module.default;
          const processor = new ProductDeletionProcessor();
          processors.push(processor);
        })
        .catch((err) => console.warn("Failed to load ProductDeletionProcessor:", err));
    }

    // Add default redirect processor if not explicitly disabled
    if (options.enableRedirectProcessor !== false) {
      const redirectProcessor = new RedirectProcessor({
        delays: options.redirectDelays,
      });
      processors.push(redirectProcessor);
    }

    // Add notification processor if publisher provided or use fallback
    if (options.notificationPublisher || options.enableNotificationProcessor !== false) {
      const notificationProcessor = new NotificationProcessor(
        options.notificationPublisher || null,
        options.notificationOptions || {},
      );
      processors.push(notificationProcessor);
    }

    return processors;
  }

  /**
   * Create a FormValidator with AJAX submission enabled
   */
  static createAjaxFormValidator(form, options = {}) {
    return this.createFormValidator(form, {
      ...options,
      submissionMode: "ajax",
      ajaxHandler: options.ajaxHandler !== false,
    });
  }

  /**
   * Create a FormValidator for product deletion
   */
  static createProductDeletionValidator(form, options = {}) {
    const validator = this.createFormValidator(form, {
      ...options,
      submissionMode: "ajax",
      ajaxHandler: options.ajaxHandler !== false,
      enableRedirectProcessor: false, // Disable automatic redirects
    });

    // Add product deletion processor dynamically
    import("js/core/processors/ProductDeletionProcessor").then((module) => {
      const ProductDeletionProcessor = module.default;
      const processor = new ProductDeletionProcessor();
      validator.addResponseProcessor(processor);
    });

    return validator;
  }

  /**
   * Create a FormValidator for modal forms
   */
  static createModalFormValidator(form, options = {}) {
    return this.createFormValidator(form, {
      ...options,
      submissionMode: "ajax",
      ajaxHandler: options.ajaxHandler !== false,
      enableRealTime: options.enableRealTime !== false,
    });
  }

  /**
   * Create multiple validators for all forms matching selector
   */
  static createFormValidators(
    selector = 'form[data-validate="true"]',
    options = {},
    formSpecificOptions = null,
  ) {
    const forms = document.querySelectorAll(selector);
    return Array.from(forms).map((form) => {
      const formOptions = {
        ...options,
        ...(formSpecificOptions ? formSpecificOptions(form) : {}),
      };
      return this.createFormValidator(form, formOptions);
    });
  }

  /**
   * Initialize all forms on the page based on their data attributes
   */
  static initializeAllForms() {
    const validators = [];
    const promises = [];

    const explicitForms = document.querySelectorAll('form[data-validate="true"]');
    explicitForms.forEach((form) => {
      try {
        // Detect form type from action or data attribute
        const formType = form.dataset.formType || this.detectFormType(form);

        let validator;
        if (formType === "product-deletion") {
          validator = this.createProductDeletionValidator(form, {
            submissionMode: form.dataset.submissionMode,
            ajaxHandler: form.dataset.ajaxHandler === "true",
            enableRealTime: form.dataset.realTime !== "false",
          });
        } else {
          validator = this.createFormValidator(form, {
            submissionMode: form.dataset.submissionMode,
            ajaxHandler: form.dataset.ajaxHandler === "true",
            enableRealTime: form.dataset.realTime !== "false",
          });
        }

        validators.push(validator);
        promises.push(validator.initialize());
      } catch (error) {
        console.error(
          `Failed to initialize form validator for ${form.id || "anonymous form"}:`,
          error,
        );
      }
    });

    window.__formValidators = validators;
    return Promise.allSettled(promises);
  }

  /**
   * Detect form type from action URL
   */
  static detectFormType(form) {
    if (!form.action) return "unknown";

    if (form.action.includes("/product/delete/")) {
      return "product-deletion";
    }
    if (form.action.includes("/product/create")) {
      return "product-create";
    }
    if (form.action.includes("/product/edit")) {
      return "product-edit";
    }
    return "unknown";
  }

  /**
   * Create a FormValidator with specific validation rules
   */
  static createFormValidatorWithRules(form, rulesName, options = {}) {
    return this.createFormValidator(form, {
      ...options,
      rulesName,
    });
  }

  /**
   * Create a FormValidator for quick form submissions (no real-time validation)
   */
  static createQuickFormValidator(form, options = {}) {
    return this.createFormValidator(form, {
      ...options,
      enableRealTime: false,
    });
  }
}
