import DashboardManager from "js/backend/components/DashboardManager";
import ProductListCheckboxManager from "js/backend/components/ProducListCheckboxManager";
import MediaUpload from "js/backend/components/MediaUpload";
import Validator from "js/components/validation/Validator";

// Import custom logger
import { BrowserLogger } from "js/utils/BrowserLogger";
const logger = new BrowserLogger("Main");

class MainOld {
  constructor() {
    logger.debug("🔄 Main constructor called");
    logger.debug("📄 Document readyState:", document.readyState);
    logger.debug("🔍 Forms on page:", document.querySelectorAll("form").length);
    this._validatorInitialized = false;
    this._mainInitialized = false;
    this._init();
  }

  async _init() {
    if (this._mainInitialized) {
      logger.warn("Main already initialized, skipping");
      return;
    }
    this._mainInitialized = true;

    logger.info("Initializing application");

    try {
      await this._initializeComponents();
      await this._initValidator();
      this._bindRealTimeValidation();

      logger.success("Application initialized successfully");
    } catch (error) {
      logger.error("Failed to initialize application", error);
    }
  }

  async _initializeComponents() {
    logger.debug("Initializing components");

    new DashboardManager();
    new ProductListCheckboxManager();

    const mediaUploadContainers = document.querySelectorAll('[data-media-upload="true"]');
    logger.debug(`Found ${mediaUploadContainers.length} media upload containers`);

    mediaUploadContainers.forEach((container) => new MediaUpload(container));
  }

  async _initValidator() {
    if (this._validatorInitialized) {
      logger.error("🚨 Validator already initialized - this should not happen!");
      logger.warn("Validator already initialized, skipping");
      return;
    }

    logger.debug("Starting validator initialization");

    try {
      const rulesFile = this._getCurrentRulesFile();
      logger.debug(`Rules file determined: ${rulesFile}`);

      const baseUrl = this._getApiBaseUrl();
      const rulesUrl = `${baseUrl}/get-rules?rules=${rulesFile}`;

      const timestamp = Date.now();
      const finalUrl =
        process.env.NODE_ENV === "development" ? `${rulesUrl}&debug=1&t=${timestamp}` : rulesUrl;

      logger.debug(`Fetching validation rules from: ${finalUrl}`);

      const startTime = performance.now();
      const response = await fetch(finalUrl);
      const endTime = performance.now();

      logger.debug(`Request took: ${(endTime - startTime).toFixed(2)}ms`);

      if (!response.ok) {
        this._handleApiError(response, rulesFile);
        return;
      }

      const data = await response.json();
      logger.debug("📋 FULL VALIDATION RULES STRUCTURE:", data.rules);

      // Log each field's rules in detail
      Object.entries(data.rules).forEach(([fieldName, rules]) => {
        logger.debug(`📋 Field: ${fieldName}`, rules);
      });

      if (data.error) {
        throw new Error(data.error);
      }

      // ✅ FIX: Wait for the validator to be properly created
      const validator = new Validator(data.rules, {}, data.settings);

      // ✅ FIX: Add small delay to ensure DOM is ready
      await new Promise((resolve) => setTimeout(resolve, 0));

      this._bindFormValidators(validator);

      this._validatorInitialized = true;

      logger.success(`Loaded validation rules for: ${rulesFile}`, {
        environment: data._environment,
        ruleCount: Object.keys(data.rules).length,
        fields: Object.keys(data.rules),
      });
    } catch (error) {
      logger.error("Failed to load validation rules", error);
      this._showValidationWarning(error.message);
    }
  }

  _getApiBaseUrl() {
    const baseUrl =
      process.env.NODE_ENV === "development" ? "/form-validation-api" : "/api/validation-api";

    logger.trace(`API base URL: ${baseUrl}`);
    return baseUrl;
  }

  _handleApiError(response, rulesFile) {
    logger.debug(`API error response: ${response.status}`, {
      status: response.status,
      rulesFile: rulesFile,
      url: response.url,
    });

    switch (response.status) {
      case 400:
        throw new Error("Invalid validation rules request");
      case 403:
        throw new Error("Operation not allowed in current environment");
      case 404:
        throw new Error(`Validation rules not found for: ${rulesFile}`);
      case 500:
        throw new Error("Server error loading validation rules");
      default:
        throw new Error(`HTTP ${response.status}: Failed to load validation rules`);
    }
  }

  _showValidationWarning(message) {
    logger.warn(`Showing validation warning: ${message}`);

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
      <strong>Note:</strong> Enhanced validation is temporarily unavailable. 
      Your form will still be validated when submitted.
      <br><small>${message}</small>
    `;

    const form = document.querySelector("form[data-validate]");
    if (form) {
      form.parentNode.insertBefore(warningElement, form);

      setTimeout(() => {
        if (warningElement.parentNode) {
          warningElement.parentNode.removeChild(warningElement);
          logger.debug("Validation warning auto-removed");
        }
      }, 10000);
    }
  }

  _getCurrentRulesFile() {
    // Method 1: From form data attribute
    const form = document.querySelector("form[data-validate]");
    if (form?.dataset.validationRules) {
      const rules = form.dataset.validationRules;
      logger.trace(`Rules from form data attribute: ${rules}`);
      return rules;
    }

    // Method 2: From URL path
    const path = window.location.pathname;
    if (path.includes("/product")) {
      logger.trace("Rules from URL path: productRules");
      return "productRules";
    }
    if (path.includes("/post")) {
      logger.trace("Rules from URL path: postRules");
      return "postRules";
    }
    if (path.includes("/user")) {
      logger.trace("Rules from URL path: userRules");
      return "userRules";
    }
    if (path.includes("/admin")) {
      logger.trace("Rules from URL path: adminRules");
      return "adminRules";
    }

    // Method 3: From page-specific meta tag
    const metaTag = document.querySelector('meta[name="validation-rules"]');
    if (metaTag) {
      const rules = metaTag.getAttribute("content");
      logger.trace(`Rules from meta tag: ${rules}`);
      return rules;
    }

    logger.warn("No specific rules found, using default");
    return "defaultRules";
  }

  _bindFormValidators(validator) {
    try {
      const forms = document.querySelectorAll('form[data-validate="true"]');
      logger.debug(`Binding validators to ${forms.length} forms`);

      forms.forEach((form, index) => {
        // Create a validator instance for each form
        const formValidator = new Validator(validator.rules, {}, validator.globalSettings);

        // Bind submit event
        form.addEventListener("submit", (event) => {
          logger.debug(`Form ${index + 1} submit event triggered`);
          this._handleFormSubmit(event, form, formValidator);
        });

        // Store validator reference on form for real-time validation
        form._validator = formValidator;
        logger.trace(`Validator bound to form ${index + 1}`);
      });
    } catch (error) {
      logger.error("Error in _bindFormValidators:", error);
      throw error; // Re-throw to be caught by the main error handler
    }
  }

  _bindRealTimeValidation() {
    logger.debug("Binding real-time validation events");

    // Real-time validation on input blur
    document.addEventListener(
      "blur",
      (event) => {
        const target = event.target;
        if (target.matches("input, select, textarea") && target.form?._validator) {
          logger.trace(`Real-time validation for field: ${target.name}`);
          this._validateField(target, target.form._validator);
        }
      },
      true,
    );

    // Clear errors on input
    document.addEventListener(
      "input",
      (event) => {
        const target = event.target;
        if (target.form?._validator) {
          this._clearFieldError(target);
        }
      },
      true,
    );
  }

  _handleFormSubmit(event, form, validator) {
    logger.debug("Handling form submission");

    // Always prevent default to stop page reload
    event.preventDefault();
    event.stopPropagation();

    const formData = this._getFormData(form);
    validator.formData = formData;

    // Clear all previous errors
    this._clearAllErrors(form);

    if (!validator.validateAll()) {
      const errors = validator.getErrors();
      logger.warn("Form validation failed", { errors: Object.keys(errors) });
      this._displayFormErrors(form, errors);

      // Add visual feedback
      form.classList.add("validation-failed");
      setTimeout(() => form.classList.remove("validation-failed"), 1000);
    } else {
      logger.debug("Form validation passed - submitting form");

      // If validation passes, submit the form programmatically
      if (form.method === "post") {
        this._submitForm(form);
      } else {
        form.submit(); // Fallback
      }
    }
  }

  async _submitForm(form) {
    try {
      const formData = new FormData(form);

      // Show loading state
      const submitButton = form.querySelector('button[type="submit"]');
      const originalText = submitButton.textContent;
      submitButton.textContent = "Submitting...";
      submitButton.disabled = true;

      const response = await fetch(form.action, {
        method: "POST",
        body: formData,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      if (response.ok) {
        const result = await response.json();
        logger.success("Form submitted successfully", result);
        // Handle success (redirect, show message, etc.)
        if (result.redirect) {
          window.location.href = result.redirect;
        }
      } else {
        throw new Error(`HTTP ${response.status}`);
      }
    } catch (error) {
      logger.error("Form submission failed", error);
      // Re-enable button
      const submitButton = form.querySelector('button[type="submit"]');
      submitButton.textContent = originalText;
      submitButton.disabled = false;
    }
  }

  _validateField(field, validator) {
    const fieldName = field.name;
    if (!fieldName) return;

    const formData = this._getFormData(field.form);
    validator.formData = formData;

    // Clear previous error for this field
    this._clearFieldError(field);

    if (!validator.validateField(fieldName)) {
      const errors = validator.getErrors();
      logger.debug(`Field validation failed: ${fieldName}`, { error: errors[fieldName] });
      this._displayFieldError(field, errors[fieldName]);
    } else {
      logger.trace(`Field validation passed: ${fieldName}`);
    }
  }

  _getFormData(form) {
    const formData = {};
    const formDataObj = new FormData(form);

    logger.debug("📋 RAW FORM DATA:");
    for (let [key, value] of formDataObj.entries()) {
      logger.debug(`  ${key}: ${value}`);
      formData[key] = value;
    }

    logger.debug("Form data extracted", {
      fields: Object.keys(formData),
      values: formData,
    });

    return formData;
  }

  _displayFormErrors(form, errors) {
    logger.debug(`Displaying form errors for ${Object.keys(errors).length} fields`);

    Object.entries(errors).forEach(([fieldName, error]) => {
      const field = form.querySelector(`[name="${fieldName}"]`);
      if (field) {
        this._displayFieldError(field, error);
      }
    });
  }

  _displayFieldError(field, error) {
    logger.debug("_displayFieldError START", {
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

    logger.debug("_displayFieldError COMPLETE - Error should be visible now");
    logger.debug("Current parent classes:", parent?.className);
  }

  _clearFieldError(field) {
    console.log("🟡 _clearFieldError called for:", field.name);

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

    console.log("🟢 _clearFieldError completed");
  }

  _clearAllErrors(form) {
    logger.debug("_clearAllErrors called");

    // Remove all is-invalid classes from inputs
    const invalidInputs = form.querySelectorAll(".is-invalid");
    invalidInputs.forEach((input) => {
      input.classList.remove("is-invalid");
    });

    // Remove all has-error classes from parents
    const errorParents = form.querySelectorAll(".has-error");
    errorParents.forEach((parent) => {
      parent.classList.remove("has-error");
    });

    // Remove all error message elements
    const errorElements = form.querySelectorAll(".input-box__hint-text");
    errorElements.forEach((element) => {
      element.remove();
    });

    logger.debug(
      `Cleared ${invalidInputs.length} invalid inputs, ${errorParents.length} error parents, ${errorElements.length} error elements`,
    );
  }
}

// Single initialization with proper safeguards
let mainInstance = null;
let initializationStarted = false;

function initializeApplication() {
  if (mainInstance) {
    logger.warn("Application instance already exists");
    return;
  }

  if (initializationStarted) {
    logger.warn("Application initialization already in progress");
    return;
  }

  initializationStarted = true;
  logger.info("Starting application initialization");

  mainInstance = new Main();
}

// Handle DOM ready state
if (document.readyState === "loading") {
  logger.debug("Document still loading, adding DOMContentLoaded listener");
  document.addEventListener("DOMContentLoaded", () => {
    logger.debug("DOMContentLoaded fired");
    initializeApplication();
  });
} else {
  logger.debug("Document already ready, initializing immediately");
  initializeApplication();
}

// Export for debugging
if (process.env.NODE_ENV === "development") {
  window.MainApp = {
    getInstance: () => mainInstance,
    reinitialize: () => {
      if (mainInstance) {
        mainInstance._validatorInitialized = false;
        mainInstance._mainInitialized = false; // Add this
        mainInstance._init(); // Re-run full initialization
      }
    },
    getState: () => ({
      // Add state inspection
      validatorInitialized: mainInstance?._validatorInitialized,
      mainInitialized: mainInstance?._mainInitialized,
    }),
  };
}
