import BrowserLogger from "js/utils/logger";

const logger = new BrowserLogger("ProductFormManager");

export default class ProductFormManager {
  constructor(validatorManager, errorManager) {
    this.validatorManager = validatorManager;
    this.errorManager = errorManager;
    this.forms = [];
  }

  bindFormEvents() {
    const forms = document.querySelectorAll('form[data-validate="true"]');
    logger.debug(`Binding product validators to ${forms.length} forms`);

    this.forms = Array.from(forms).map((form) => {
      const validator = this.validatorManager.createValidator(form);
      this._bindFormSubmit(form, validator);
      return { form, validator };
    });
  }

  _bindFormSubmit(form, validator) {
    form.addEventListener("submit", (event) => {
      this._handleFormSubmit(event, form, validator);
    });

    form._validator = validator;
  }

  async _handleFormSubmit(event, form, validator) {
    const formData = this._getFormData(form);
    validator.formData = formData;

    this._clearFormErrors(form);

    const isValid = validator.validateAll();
    const errors = validator.getErrors();

    if (!isValid) {
      event.preventDefault();
      this._displayFormErrors(form, errors);
    } else {
      this._disableSubmitButton(form);
    }
  }

  _getFormData(form) {
    const formData = new FormData(form);
    const result = {};

    logger.debug("=== CHECKING FOR variation_type_id IN FORMDATA ===");

    for (let [key, value] of formData.entries()) {
      const field = form.querySelector(`[name="${key}"]`);

      if (field && field.type === "file") {
        result[key] = field.files;
        continue;
      }

      if (value === "on") value = true;
      if (value === "off") value = false;

      // Log variation_type_id specifically
      if (key.includes("variation_type")) {
        logger.debug(`Found variation_type field: "${key}" = "${value}"`);
      }

      this._processField(key, value, result, field);
    }

    // Clean up variations
    this._cleanVariations(result);

    // Check final result
    const variationTypeKeys = Object.keys(result).filter((k) => k.includes("variation_type"));
    logger.debug("variation_type keys in final result:", variationTypeKeys);

    return result;
  }

  _processField(key, value, result, field) {
    const attributeMatch = key.match(
      /variations\[(\d+)\]\[attributes\]\[(\d+)\]\[(attribute_name|attribute_value)\]/,
    );
    const variationMatch = key.match(/variations\[(\d+)\]\[([^\]]+)\]/);

    if (attributeMatch) {
      this._processAttributeField(attributeMatch, value, result);
    } else if (variationMatch) {
      this._processVariationField(variationMatch, value, result);
    } else {
      result[key] = value;
    }
  }

  _processAttributeField(match, value, result) {
    const [, varIndex, attrIndex, attrField] = match;

    if (!result.variations) result.variations = [];
    if (!result.variations[varIndex]) result.variations[varIndex] = { attributes: [] };
    if (!result.variations[varIndex].attributes[attrIndex]) {
      result.variations[varIndex].attributes[attrIndex] = {};
    }

    result.variations[varIndex].attributes[attrIndex][attrField] = value;
    const flatFieldName = `variations[${varIndex}][attributes][${attrIndex}][${attrField}]`;
    result[flatFieldName] = value;
  }

  _processVariationField(match, value, result) {
    const [, varIndex, field] = match;

    if (!result.variations) result.variations = [];
    if (!result.variations[varIndex]) result.variations[varIndex] = { attributes: [] };

    // Convert numeric fields
    const numericFields = {
      variant_type: parseInt,
      stock_quantity: parseInt,
      price_modifier: parseFloat,
    };

    if (numericFields[field] && value !== "") {
      value = numericFields[field](value) || 0;
    }

    result.variations[varIndex][field] = value;

    const bracketFieldName = `variations[${varIndex}][${field}]`;
    result[bracketFieldName] = value;
  }

  _cleanVariations(result) {
    if (result.variations) {
      result.variations = result.variations.filter(
        (v) => v !== undefined && Object.keys(v).length > 0,
      );

      result.variations.forEach((variation) => {
        if (variation.attributes) {
          variation.attributes = variation.attributes.filter(
            (attr) => attr && (attr.attribute_name || attr.attribute_value),
          );
        } else {
          variation.attributes = [];
        }
      });
    }
  }

  bindRealTimeValidation() {
    logger.debug("Binding product real-time validation");

    document.addEventListener(
      "blur",
      (event) => {
        const target = event.target;
        if (target.matches("input, select, textarea") && target.form?._validator) {
          this._validateField(target, target.form._validator);
        }
      },
      true,
    );

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

    document.addEventListener(
      "change",
      (event) => {
        const target = event.target;
        if (target.form?._validator) {
          if (target.type === "file") {
            this._clearFieldError(target);
          }

          this._validateField(target, target.form._validator);
        }
      },
      true,
    );
  }

  _validateField(field, validator) {
    const fieldName = field.name;
    if (!fieldName) return;

    const form = field.form;
    const formData = this._getFormData(form);
    validator.formData = formData;

    this._clearFieldError(field);

    if (!validator.validateField(fieldName)) {
      const errors = validator.getErrors();
      this._displayFieldError(field, errors[fieldName]);
    }
  }
  _displayFormErrors(form, errors) {
    Object.entries(errors).forEach(([errorKey, error]) => {
      const errorObj = error && typeof error === "object" ? error : { message: error };
      const targetFieldName = errorObj.fieldPath || errorKey;

      const field = form.querySelector(`[name="${targetFieldName}"]`);
      if (field) {
        this._displayFieldError(field, errorObj);
      }
    });
  }

  _displayFieldError(field, error) {
    this.errorManager.displayError(field, error);
  }

  _clearFieldError(field) {
    this.errorManager.clearError(field);
  }

  _clearFormErrors(form) {
    this.errorManager.clearAllErrors(form);
  }

  _disableSubmitButton(form) {
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = "Submitting...";
    }
  }

  _diagnoseValidationIssue() {
    const form = document.querySelector('form[data-validate="true"]');
    if (!form) return;

    const validator = form._validator;
    if (!validator) return;

    console.group("🔍 VALIDATION DIAGNOSIS");

    // 1. Check what's in formData
    const formData = this._getFormData(form);
    logger.debug("Form Data (variation fields):");
    Object.keys(formData).forEach((key) => {
      if (key.includes("variation") || key.includes("sku")) {
        logger.debug(`  "${key}": "${formData[key]}"`);
      }
    });

    // 2. Manually test specific fields
    const testFields = [
      "variations[0][sku]",
      "variations[0][variation_type_id]",
      "variations[0][attributes][0][attribute_name]",
    ];

    testFields.forEach((fieldName) => {
      const field = form.querySelector(`[name="${fieldName}"]`);
      const value = formData[fieldName];
      const rules = validator.getFieldRules(fieldName);

      if (rules) {
        // Test required rule specifically
        if (rules.required) {
          const requiredValidator = this.validatorManager.createValidator(
            "required",
            { message: "%s is required.", classes: [] },
            rules.display || fieldName,
            value,
            rules.required,
            formData,
          );

          if (requiredValidator) {
            const error = requiredValidator.validate(formData);
            logger.debug(`  Required validator: ${error ? "FAILED" : "PASSED"}`, error?.message);
          }
        }
      }
    });

    console.groupEnd();
  }
}
