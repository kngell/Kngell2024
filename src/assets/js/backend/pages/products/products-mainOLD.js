import BrowserLogger from "js/utils/logger";
import Validator from "js/core/validation/Validator";
import ProductVariationManager from "./components/ProductVariationManager.js";
import MediaUpload from "js/backend/shared/MediaUpload";

const logger = new BrowserLogger("ProductMain");

class ProductMainOLD {
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
    // TEST: Add file validation test
    setTimeout(() => this._testFileValidation(), 2000);
    try {
      const rulesFile = this._getProductRulesFile();
      const baseUrl = this._getApiBaseUrl();
      const rulesUrl = `${baseUrl}/get-rules?rules=${rulesFile}`;

      const finalUrl =
        process.env.NODE_ENV === "development" ? `${rulesUrl}&debug=1&t=${Date.now()}` : rulesUrl;

      logger.debug(`Fetching product validation rules from: ${finalUrl}`);

      const response = await fetch(finalUrl);

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: Failed to load product rules`);
      }

      // FIRST: Get the raw text to see what's actually being returned
      const rawText = await response.text();
      logger.debug("🔍 RAW RESPONSE FROM VALIDATION ENDPOINT:");
      logger.debug("Full response:", rawText);
      logger.debug("First 200 chars:", rawText.substring(0, 200));
      logger.debug("Content-Type header:", response.headers.get("content-type"));

      // Try to parse as JSON to see the exact error
      try {
        const data = JSON.parse(rawText);
        logger.debug("✅ Successfully parsed as JSON:", data);

        if (data.error) {
          throw new Error(data.error);
        }

        const validator = new Validator(data.rules, {}, data.settings);
        this._bindProductFormValidators(validator);
        this._validatorInitialized = true;

        logger.success(`Loaded product validation rules for: ${rulesFile}`);

        // Test max_files rule specifically
        setTimeout(() => this._testMaxFilesRule(), 1000);
      } catch (parseError) {
        logger.error("❌ JSON Parse Error:", parseError);
        logger.debug("Raw response that failed to parse:", rawText);
        throw new Error(`Invalid JSON response from server: ${parseError.message}`);
      }
    } catch (error) {
      logger.error("Failed to load product validation rules", error);
      this._showProductValidationWarning(error.message);
    }
  }
  _testMaxFilesRule() {
    logger.debug("🧪 TEST: Max Files Rule Detection");

    const form = document.querySelector('form[data-validate="true"]');
    const fileInput = document.querySelector("#product-frm_main-image");
    const validator = form?._validator;

    if (!validator) {
      logger.error("❌ No validator found");
      return;
    }

    // Check if max_files rule is properly loaded
    const mainImageRules = validator.getFieldRules("main_image");
    logger.debug("🔍 Main Image Rules:", {
      allRules: mainImageRules,
      maxFilesRule: mainImageRules?.max_files,
      maxUploadFileType: typeof mainImageRules?.max_files,
    });

    // Test with multiple files
    const file1 = new File(["content1"], "test1.png", { type: "image/png" });
    const file2 = new File(["content2"], "test2.jpg", { type: "image/jpeg" });

    const dt = new DataTransfer();
    dt.items.add(file1);
    dt.items.add(file2);
    fileInput.files = dt.files;

    // Update form data
    const formData = this._getProductFormData(form);
    validator.formData = formData;

    logger.debug("📁 Test files set:", {
      fileCount: fileInput.files.length,
      fileNames: Array.from(fileInput.files).map((f) => f.name),
    });

    // Validate the field
    const isValid = validator.validateField("main_image");
    const errors = validator.getErrors();

    logger.debug("🧪 TEST RESULTS:", {
      isValid,
      errors,
      mainImageError: errors.main_image,
    });

    if (!isValid && errors.main_image) {
      logger.success("✅ TEST PASSED: Max files rule is working!");
      logger.debug("Error details:", errors.main_image);
    } else {
      logger.error("❌ TEST FAILED: Max files rule did not trigger");
    }
  }

  _testFileValidation() {
    logger.debug("🧪 Testing file validation setup...");

    const form = document.querySelector('form[data-validate="true"]');
    const fileInput = document.querySelector("#product-frm_main-image");
    const validator = form?._validator;

    if (!form || !fileInput || !validator) {
      logger.error("❌ Missing components for file validation test");
      return;
    }

    logger.debug("🔍 File validation test setup:", {
      form: !!form,
      fileInput: !!fileInput,
      validator: !!validator,
      hasMainImageRules: !!validator.rules.main_image,
      mainImageRules: validator.rules.main_image,
    });

    // Test max_files rule specifically
    setTimeout(() => {
      this._testMaxFilesRule();
    }, 1000);
  }

  _testMaxFilesRule() {
    logger.debug("🧪 TEST: Max Files Rule Detection");

    const form = document.querySelector('form[data-validate="true"]');
    const fileInput = document.querySelector("#product-frm_main-image");
    const validator = form?._validator;

    if (!validator) {
      logger.error("❌ No validator found");
      return;
    }

    // Check if max_files rule is properly loaded
    const mainImageRules = validator.getFieldRules("main_image");
    logger.debug("🔍 Main Image Rules:", {
      allRules: mainImageRules,
      maxFilesRule: mainImageRules?.max_files,
      maxUploadFileType: typeof mainImageRules?.max_files,
    });

    // Test with multiple files
    const file1 = new File(["content1"], "test1.png", { type: "image/png" });
    const file2 = new File(["content2"], "test2.jpg", { type: "image/jpeg" });

    const dt = new DataTransfer();
    dt.items.add(file1);
    dt.items.add(file2);
    fileInput.files = dt.files;

    // Update form data
    const formData = this._getProductFormData(form);
    validator.formData = formData;

    logger.debug("📁 Test files set:", {
      fileCount: fileInput.files.length,
      fileNames: Array.from(fileInput.files).map((f) => f.name),
    });

    // Validate the field
    const isValid = validator.validateField("main_image");
    const errors = validator.getErrors();

    logger.debug("🧪 TEST RESULTS:", {
      isValid,
      errors,
      mainImageError: errors.main_image,
    });

    if (!isValid && errors.main_image) {
      logger.success("✅ TEST PASSED: Max files rule is working!");
      logger.debug("Error details:", errors.main_image);
    } else {
      logger.error("❌ TEST FAILED: Max files rule did not trigger");
    }
  }
  _getProductRulesFile() {
    const form = document.querySelector('form[data-validate="true"]');
    return form?.dataset.validationRules || "productRules";
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
    logger.debug("🔄 Handling product form submission");

    const formData = this._getProductFormData(form);
    validator.formData = formData;
    this._clearAllProductErrors(form);

    const isValid = validator.validateAll();
    const errors = validator.getErrors();

    if (!isValid) {
      // PREVENT submission only when invalid
      event.preventDefault();
      logger.warn("Product form validation failed - preventing submission");
      this._displayProductFormErrors(form, errors);
    } else {
      // ALLOW normal form submission when valid
      logger.debug("✅ Product form validation passed - allowing normal submission");
      // The form will submit normally with files included

      // Optional: Disable submit button to prevent double clicks
      const submitButton = form.querySelector('button[type="submit"]');
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = "Submitting...";
      }
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
      this._displayProductFieldError(field, errors[fieldName]);
    }
  }
  _getProductFormData(form) {
    const formData = new FormData(form);
    const result = {};

    logger.debug("🔧 Processing form data from FormData");

    // Process all form fields
    for (let [key, value] of formData.entries()) {
      // Handle file inputs - store the FileList object
      const field = form.querySelector(`[name="${key}"]`);
      if (field && field.type === "file") {
        result[key] = field.files; // Store FileList instead of individual files
        continue;
      }

      // Handle checkbox values
      if (value === "on") value = true;
      if (value === "off") value = false;

      // Handle variation fields
      const attributeMatch = key.match(
        /variations\[(\d+)\]\[attributes\]\[(\d+)\]\[(attribute_name|attribute_value)\]/,
      );
      const variationMatch = key.match(/variations\[(\d+)\]\[([^\]]+)\]/);

      if (attributeMatch) {
        const [, varIndex, attrIndex, attrField] = attributeMatch;

        if (!result.variations) result.variations = [];
        if (!result.variations[varIndex]) result.variations[varIndex] = { attributes: [] };
        if (!result.variations[varIndex].attributes[attrIndex]) {
          result.variations[varIndex].attributes[attrIndex] = {};
        }

        result.variations[varIndex].attributes[attrIndex][attrField] = value;

        // Create flat field name for validator
        const flatFieldName = `variations[${varIndex}][attributes][${attrIndex}][${attrField}]`;
        result[flatFieldName] = value;
      } else if (variationMatch) {
        const [, varIndex, field] = variationMatch;

        if (!result.variations) result.variations = [];
        if (!result.variations[varIndex]) result.variations[varIndex] = { attributes: [] };

        // Convert numeric fields
        if (field === "variant_type" && value !== "") {
          value = parseInt(value, 10) || 0;
        }
        if (field === "stock_quantity" && value !== "") {
          value = parseInt(value, 10) || 0;
        }
        if (field === "price_modifier" && value !== "") {
          value = parseFloat(value) || 0;
        }

        result.variations[varIndex][field] = value;

        // CRITICAL: Create BOTH flat field names for validator compatibility
        const bracketFieldName = `variations[${varIndex}][${field}]`;
        const dotFieldName = `variations[${varIndex}].${field}`;

        result[bracketFieldName] = value;
        result[dotFieldName] = value;
      } else {
        // Handle regular fields
        result[key] = value;
      }
    }

    // Clean up variations
    if (result.variations) {
      result.variations = result.variations.filter(
        (v) => v !== undefined && Object.keys(v).length > 0,
      );

      result.variations.forEach((variation, index) => {
        // Ensure attributes array is clean
        if (variation.attributes) {
          variation.attributes = variation.attributes.filter(
            (attr) => attr && (attr.attribute_name || attr.attribute_value),
          );
        } else {
          variation.attributes = [];
        }
      });
    }

    // DEBUG: Log variant_type specifically
    if (result.variations && result.variations[0]) {
      logger.debug("🔍 VARIANT_TYPE DEBUG:", {
        nested: result.variations[0].variant_type,
        bracket: result["variations[0][variant_type]"],
        dot: result["variations[0].variant_type"],
        allVariantFields: Object.keys(result).filter((key) => key.includes("variant_type")),
      });
    }
    // DEBUG: Log file inputs specifically
    Object.keys(result).forEach((key) => {
      const field = form.querySelector(`[name="${key}"]`);
      if (field && field.type === "file") {
        logger.debug("📁 File input in formData:", {
          field: key,
          files: result[key],
          fileCount: result[key]?.length || 0,
        });
      }
    });
    return result;
  }

  _bindProductRealTimeValidation() {
    logger.debug("Binding product real-time validation");

    // Existing blur validation for text inputs
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

    // Existing input event for clearing errors
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

    // NEW: Add file input change validation
    document.addEventListener(
      "change",
      (event) => {
        const target = event.target;
        if (target.type === "file" && target.form?._validator) {
          logger.debug("📁 File input change detected, validating...", {
            field: target.name,
            files: target.files,
          });
          this._validateProductField(target, target.form._validator);
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

  // _displayProductFieldError(field, error) {
  //   // Add error class to the input field itself
  //   field.classList.add("is-invalid");

  //   // Find the parent input-box container
  //   const inputBoxContainer = field.closest(".input-box");

  //   if (!inputBoxContainer) {
  //     logger.warn("No .input-box container found for field", { field: field.name });
  //     return;
  //   }

  //   // Add error class to the container
  //   inputBoxContainer.classList.add("has-error");

  //   // Create new error message element
  //   const errorElement = document.createElement("div");
  //   errorElement.className = error.classes.join(" ");
  //   errorElement.textContent = error.message;

  //   // Remove any existing error message first
  //   const existingError = inputBoxContainer.querySelector(".input-box__hint-text");
  //   if (existingError) {
  //     existingError.remove();
  //   }

  //   // Insert error message as the LAST child of the input-box container
  //   inputBoxContainer.appendChild(errorElement);

  //   logger.debug("Error message placed at bottom of input-box container");
  // }

  _displayProductFieldError(field, error) {
    // Add error class to the input field itself
    field.classList.add("is-invalid");

    // Find the parent input-box container
    const inputBoxContainer = field.closest(".input-box");

    if (!inputBoxContainer) {
      logger.warn("No .input-box container found for field", { field: field.name });
      return;
    }

    // Add error class to the container
    inputBoxContainer.classList.add("has-error");

    // Create new error message element
    const errorElement = document.createElement("div");
    errorElement.className = error.classes.join(" ");
    errorElement.textContent = error.message;

    // Remove any existing error message first
    const existingError = inputBoxContainer.querySelector(".input-box__hint-text");
    if (existingError) {
      existingError.remove();
    }

    // Insert error message as the LAST child of the input-box container
    inputBoxContainer.appendChild(errorElement);

    logger.debug("Error message placed at bottom of input-box container");

    // ✅ KEEP THE FILES IN PREVIEW - users need to see what they selected
    // Don't clear the preview - let users see the 6 files they selected
    // The form submission will be prevented by the validation system
  }

  // Add this new method
  _clearFilePreviewOnError(field) {
    logger.debug("🧹 Clearing file preview due to max_files validation error", {
      field: field.name,
      fileCount: field.files?.length,
    });

    // Find the media upload container for this field
    const mediaUploadContainer = field.closest('[data-media-upload="true"]');
    if (!mediaUploadContainer) {
      logger.warn("No media upload container found for field", field.name);
      return;
    }

    // Find the MediaUpload instance
    const mediaUploadInstance = mediaUploadContainer._mediaUploadInstance;
    if (mediaUploadInstance) {
      // Clear the preview
      mediaUploadInstance.clearPreview();

      // Also clear the file input (remove all files)
      const dt = new DataTransfer();
      field.files = dt.files;

      logger.debug("✅ File preview and input cleared after validation error");
    } else {
      logger.warn("No MediaUpload instance found for container");
    }
  }
  _clearProductFieldError(field) {
    // Remove error class from the input field
    field.classList.remove("is-invalid");

    // Find the parent input-box container
    const inputBoxContainer = field.closest(".input-box");

    if (inputBoxContainer) {
      // Remove error class from container
      inputBoxContainer.classList.remove("has-error");

      // Remove error message from container
      const errorElement = inputBoxContainer.querySelector(".input-box__hint-text");
      if (errorElement) {
        errorElement.remove();
      }
    }
  }

  _clearAllProductErrors(form) {
    // Remove error classes from all input-box containers
    const errorContainers = form.querySelectorAll(".has-error");
    errorContainers.forEach((container) => container.classList.remove("has-error"));

    // Remove all error messages from input-box containers
    const hintElements = form.querySelectorAll(".input-box__hint-text");
    hintElements.forEach((el) => el.remove());

    // Remove invalid class from all inputs
    const invalidInputs = form.querySelectorAll(".is-invalid");
    invalidInputs.forEach((input) => input.classList.remove("is-invalid"));
  }

  async _submitProductForm(form) {
    try {
      const formData = new FormData(form);

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
    instance: window.ProductApp?.instance,
    reinit: () => new ProductMain(),
  };
}
