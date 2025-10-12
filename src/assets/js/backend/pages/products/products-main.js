import { BrowserLogger } from "js/utils/BrowserLogger";
import Validator from "js/core/validation/Validator";
import ProductMediaUpload from "./components/ProductMediaUpload";
import ProductVariationManager from "./components/ProductVariationManager.js";

const logger = new BrowserLogger("ProductMain");

class ProductMain {
  constructor() {
    this._validatorInitialized = false;
    this._mainInitialized = false;
    this._init();
  }

  async _init() {
    if (this._mainInitialized) {
      logger.warn("ProductMain already initialized, skipping");
      return;
    }
    this._mainInitialized = true;

    logger.info("Initializing product page");

    try {
      await this._initializeProductComponents();
      await this._initProductValidator();
      this._bindProductRealTimeValidation();

      logger.success("Product page initialized successfully");
    } catch (error) {
      logger.error("Failed to initialize product page", error);
    }
  }

  async _initializeProductComponents() {
    logger.debug("Initializing product-specific components");

    // Product-specific components
    new ProductMediaUpload();
    new ProductVariationManager();

    // Initialize media upload for product images
    const mediaUploadContainers = document.querySelectorAll('[data-media-upload="true"]');
    logger.debug(`Found ${mediaUploadContainers.length} media upload containers`);

    mediaUploadContainers.forEach((container) => new MediaUpload(container));
  }

  async _initProductValidator() {
    if (this._validatorInitialized) {
      logger.warn("Product validator already initialized, skipping");
      return;
    }

    logger.debug("Starting product validator initialization");

    try {
      const rulesFile = this._getProductRulesFile();
      const baseUrl = this._getApiBaseUrl();
      const rulesUrl = `${baseUrl}/get-rules?rules=${rulesFile}`;

      const timestamp = Date.now();
      const finalUrl =
        process.env.NODE_ENV === "development" ? `${rulesUrl}&debug=1&t=${timestamp}` : rulesUrl;

      logger.debug(`Fetching product validation rules from: ${finalUrl}`);

      const response = await fetch(finalUrl);

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: Failed to load product rules`);
      }

      const data = await response.json();

      if (data.error) {
        throw new Error(data.error);
      }

      const validator = new Validator(data.rules, {}, data.settings);
      this._bindProductFormValidators(validator);
      this._validatorInitialized = true;

      logger.success(`Loaded product validation rules for: ${rulesFile}`);
    } catch (error) {
      logger.error("Failed to load product validation rules", error);
      this._showProductValidationWarning(error.message);
    }
  }

  _getProductRulesFile() {
    const form = document.querySelector('form[data-validate="true"]');

    // Product-specific rule detection
    if (form?.dataset.validationRules) {
      return form.dataset.validationRules;
    }

    // Default to product rules for product pages
    return "productRules";
  }

  _getApiBaseUrl() {
    return process.env.NODE_ENV === "development" ? "/form-validation-api" : "/api/validation-api";
  }

  _bindProductFormValidators(validator) {
    const forms = document.querySelectorAll('form[data-validate="true"]');
    logger.debug(`Binding product validators to ${forms.length} forms`);

    forms.forEach((form, index) => {
      const formValidator = new Validator(validator.rules, {}, validator.globalSettings);

      form.addEventListener("submit", (event) => {
        logger.debug(`Product form ${index + 1} submit event triggered`);
        this._handleProductFormSubmit(event, form, formValidator);
      });

      form._validator = formValidator;
    });
  }

  _handleProductFormSubmit(event, form, validator) {
    logger.debug("Handling product form submission");
    event.preventDefault();

    const formData = this._getProductFormData(form);
    validator.formData = formData;

    this._clearAllProductErrors(form);

    if (!validator.validateAll()) {
      const errors = validator.getErrors();
      logger.warn("Product form validation failed", { errors: Object.keys(errors) });
      this._displayProductFormErrors(form, errors);
    } else {
      logger.debug("Product form validation passed");
      this._submitProductForm(form);
    }
  }

  _validateProductField(field, validator) {
    const fieldName = field.name;
    if (!fieldName) return;

    const formData = this._getProductFormData(field.form);
    validator.formData = formData;

    // Clear previous error for this field
    this._clearProductFieldError(field);

    if (!validator.validateField(fieldName)) {
      const errors = validator.getErrors();
      logger.debug(`Field validation failed: ${fieldName}`, { error: errors[fieldName] });
      this._displayProductFieldError(field, errors[fieldName]);
    } else {
      logger.trace(`Field validation passed: ${fieldName}`);
    }
  }

  _getProductFormData(form) {
    const formData = {};
    new FormData(form).forEach((value, key) => {
      formData[key] = value;
    });
    return formData;
  }

  _bindProductRealTimeValidation() {
    logger.debug("Binding product real-time validation");

    document.addEventListener(
      "blur",
      (event) => {
        const target = event.target;
        if (target.matches("input, select, textarea") && target.form?._validator) {
          this._validateProductField(target, target.form._validator);
        }
      },
      true,
    );

    document.addEventListener(
      "input",
      (event) => {
        const target = event.target;
        if (target.form?._validator) {
          this._clearProductFieldError(target);
        }
      },
      true,
    );
  }
  _displayProductFormErrors(form, errors) {
    Object.entries(errors).forEach(([fieldName, error]) => {
      const field = form.querySelector(`[name="${fieldName}"]`);
      if (field) {
        this._displayProductFieldError(field, error);
      }
    });
  }
  _displayProductFieldError(field, error) {
    logger.debug("_displayProductFieldError START", {
      field: field.name,
      error: error.message,
      parentExists: !!field.closest(".input-box, .form-group, .field-container"),
    });

    logger.trace(`Displaying error for field: ${field.name}`, { message: error.message });

    // Add error class to the input field itself
    field.classList.add("is-invalid");
    logger.debug("Added is-invalid to field");

    // Find the parent container
    const parent = field.closest(".input-box, .form-group, .field-container");

    // Special container classes that need different handling
    const specialContainers = [
      ".input-box__container",
      ".input-box__container--currency-combo",
      ".input-box__container--currency",
    ];

    let specialContainer = null;
    let targetElement = field;

    // Check if parent contains any special container classes
    if (parent) {
      parent.classList.add("has-error");
      logger.debug("✅ Added has-error to parent:", parent.className);

      // Look for special containers within the parent
      for (const containerClass of specialContainers) {
        const container = parent.querySelector(containerClass);
        if (container) {
          specialContainer = container;
          targetElement = container;
          logger.debug("🔍 Found special container:", containerClass);
          break;
        }
      }
    }

    // Create new error message element
    const errorElement = document.createElement("div");
    errorElement.className = error.classes.join(" ");
    errorElement.textContent = error.message;
    // errorElement.style.color = "red"; // Make it visible
    // errorElement.style.fontWeight = "bold";

    // Insert error message in the appropriate location
    if (specialContainer) {
      specialContainer.parentNode.insertBefore(errorElement, specialContainer.nextSibling);
      logger.debug("Placed error AFTER special container");
    } else {
      field.parentNode.insertBefore(errorElement, field.nextSibling);
      logger.debug("Placed error AFTER field");
    }

    logger.debug("_displayProductFieldError COMPLETE - Error should be visible now");
    logger.debug("Current parent classes:", parent?.className);
  }
  _clearProductFieldError(field) {
    console.log("🟡 _clearProductFieldError called for:", field.name);

    // Remove error class from the input field
    field.classList.remove("is-invalid");

    // Find the parent container
    const parent = field.closest(".has-error");
    if (parent) {
      console.log("🔍 Found parent with has-error:", parent.className);
      parent.classList.remove("has-error");
      logger.trace("Removed has-error class from parent");

      // Special container classes that need cleanup
      const specialContainers = [
        ".input-box__container",
        ".input-box__container--currency-combo",
        ".input-box__container--currency",
      ];

      // Remove error messages that are siblings of special containers
      specialContainers.forEach((containerClass) => {
        const container = parent.querySelector(containerClass);
        if (container) {
          const nextSibling = container.nextElementSibling;
          if (nextSibling && nextSibling.matches(".input-box__hint-text")) {
            console.log("🗑️ Removing error from special container sibling");
            nextSibling.remove();
            logger.trace(`Removed error element after ${containerClass}`);
          }
        }
      });
    }

    // Remove error message that might be next to the field
    const errorElement = field.nextElementSibling;
    if (errorElement && errorElement.matches(".input-box__hint-text")) {
      console.log("🗑️ Removing error next to field");
      errorElement.remove();
      logger.trace("Removed error element next to field");
    }

    // Additional cleanup: remove any error messages from the parent container
    if (parent) {
      const allErrorElements = parent.querySelectorAll(".input-box__hint-text");
      allErrorElements.forEach((errorEl) => {
        console.log("🗑️ Removing error from parent container");
        errorEl.remove();
      });
      logger.trace("Removed all error elements from parent container");
    }

    console.log("🟢 _clearProductFieldError completed");
  }

  _clearAllProductErrors(form) {
    const errorElements = form.querySelectorAll(".has-error");
    errorElements.forEach((el) => el.classList.remove("has-error"));

    const hintElements = form.querySelectorAll(".input-box__hint-text");
    hintElements.forEach((el) => el.remove());

    const invalidInputs = form.querySelectorAll(".is-invalid");
    invalidInputs.forEach((input) => input.classList.remove("is-invalid"));
  }

  async _submitProductForm(form) {
    try {
      const formData = new FormData(form);

      // Product-specific submission logic
      const response = await fetch(form.action, {
        method: "POST",
        body: formData,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      if (response.ok) {
        const result = await response.json();
        logger.success("Product form submitted successfully", result);

        if (result.redirect) {
          window.location.href = result.redirect;
        }
      } else {
        throw new Error(`HTTP ${response.status}`);
      }
    } catch (error) {
      logger.error("Product form submission failed", error);
    }
  }

  _showProductValidationWarning(message) {
    const warningElement = document.createElement("div");
    warningElement.className = "validation-warning";
    warningElement.style.cssText = `
      background: #fff3cd;
      border: 1px solid #ffeaa7;
      color: #856404;
      padding: 12px;
      margin: 10px 0;
      border-radius: 4px;
      font-size: 14px;
    `;
    warningElement.innerHTML = `
      <strong>Note:</strong> Product validation is temporarily unavailable. 
      Your form will still be validated when submitted.
      <br><small>${message}</small>
    `;

    const form = document.querySelector("form[data-validate]");
    if (form) {
      form.parentNode.insertBefore(warningElement, form);
    }
  }
}

new ProductMain();

if (process.env.NODE_ENV === "development") {
  window.ProductApp = {
    instance: window.ProductApp?.instance, // Optional reference
    reinit: () => new ProductMain(), // Simple reinitialization
  };
}
