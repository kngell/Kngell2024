import RequiredValidator from "js/core/validation/Validators/RequiredValidator";
import MinValidator from "js/core/validation/Validators/MinValidator";
import MaxValidator from "js/core/validation/Validators/MaxValidator";
import PatternValidator from "js/core/validation/Validators/PatternValidator";
import NumericValidator from "js/core/validation/Validators/NumericValidator";
import RequiredIfValidator from "js/core/validation/Validators/RequiredIfValidator";
import MinValueValidator from "js/core/validation/Validators/MinValueValidator";
import MaxValueValidator from "js/core/validation/Validators/MaxValueValidator";
import LteValidator from "js/core/validation/Validators/LteValidator";
import GteValidator from "js/core/validation/Validators/GteValidator";

import { BrowserLogger } from "js/utils/BrowserLogger";
const logger = new BrowserLogger("Validator");

export default class ValidatorOld {
  constructor(rules, formData = {}, globalSettings = {}) {
    this.rules = rules.rules || rules;
    this.formData = formData;
    this.globalSettings = globalSettings;
    this.errors = {};
  }

  validateField(fieldName) {
    const fieldRules = this.rules[fieldName];
    const value = this.formData[fieldName];

    logger.debug("📋 ALL RULES FOR FIELD:", fieldName, fieldRules);
    logger.debug("📋 FIELD VALUE:", value);

    logger.info(`🔍 Validating field: ${fieldName}`, {
      value: value,
      allRules: fieldRules,
    });

    delete this.errors[fieldName];

    if (!fieldRules) {
      logger.debug(`No rules for field: ${fieldName}`);
      return true;
    }

    let isValid = true;

    // Process ALL rules except 'display'
    for (const [ruleName, ruleValue] of Object.entries(fieldRules)) {
      if (ruleName === "display") continue;

      logger.info(`🔧 Processing rule: ${ruleName} = ${ruleValue} for ${fieldName}`);

      const validator = this.createValidator(ruleName, fieldName, value, ruleValue);
      if (validator) {
        try {
          const error = validator.validate(this.formData);
          if (error) {
            logger.warn(`❌ Validation failed for ${fieldName}`, {
              rule: ruleName,
              error: error,
            });
            this.errors[fieldName] = error;
            isValid = false;
            break; // Stop on first error
          } else {
            logger.debug(`✅ Rule ${ruleName} passed for ${fieldName}`);
          }
        } catch (error) {
          logger.error(`Error in validator ${ruleName} for ${fieldName}:`, error);
          // Continue with other rules if one fails
        }
      }
    }

    return isValid;
  }

  createValidator(ruleName, fieldName, value, ruleValue) {
    logger.info(`🔧 Creating validator: ${ruleName} for ${fieldName}`, {
      ruleValue: ruleValue,
      value: value,
    });

    const display = this.rules[fieldName]?.display || this.formatDisplayName(fieldName);
    const messageTemplate = this.getMessageTemplate(ruleName);
    const classes = this.getErrorClasses();

    const errorParams = {
      message: messageTemplate,
      classes: classes,
    };

    // Handle different rule types with proper values
    switch (ruleName) {
      case "required":
        return new RequiredValidator(errorParams, display, value, ruleValue);
      case "min":
        return new MinValidator(errorParams, display, value, ruleValue);
      case "max":
        return new MaxValidator(errorParams, display, value, ruleValue);
      case "pattern":
        return new PatternValidator(errorParams, display, value, ruleValue);
      case "numeric":
        return new NumericValidator(errorParams, display, value, ruleValue);
      case "required_if":
        return new RequiredIfValidator(errorParams, display, value, ruleValue, this.formData);
      case "min_value":
        return new MinValueValidator(errorParams, display, value, ruleValue);
      case "max_value":
        return new MaxValueValidator(errorParams, display, value, ruleValue);
      case "lte":
        return new LteValidator(errorParams, display, value, ruleValue);
      case "gte":
        return new GteValidator(errorParams, display, value, ruleValue);
      default:
        console.warn(`Unknown validation rule: ${ruleName}`);
        return null;
    }
  }

  getMessageTemplate(ruleName) {
    return this.globalSettings.messages?.[ruleName] || this.getDefaultMessage(ruleName);
  }

  getErrorClasses() {
    return this.globalSettings.classes?.hint || ["input-box__hint-text", "invalid-feedback"];
  }

  getErrorParentClasses() {
    return this.globalSettings.classes?.error || ["has-error"];
  }

  getDefaultMessage(ruleName) {
    const defaultMessages = {
      required: "%s is required.",
      min: "%s must be at least %s characters.",
      max: "%s must be at most %s characters.",
      pattern: "%s format is invalid.",
      numeric: "%s must be a number.",
      min_value: "%s must be at least %s.",
      max_value: "%s must be at most %s.",
      lte: "%s must be less than or equal to %s.",
      gte: "%s must be greater than or equal to %s.",
      required_if: "%s is required when %s is present.",
      default: "%s is invalid.",
    };

    return defaultMessages[ruleName] || defaultMessages.default;
  }

  validateAll() {
    this.errors = {};
    let isValid = true;

    for (const fieldName in this.rules) {
      if (!this.validateField(fieldName)) {
        isValid = false;
      }
    }

    return isValid;
  }

  getErrors() {
    return this.errors;
  }

  clearErrors(fieldName = null) {
    if (fieldName) {
      delete this.errors[fieldName];
    } else {
      this.errors = {};
    }
  }

  formatDisplayName(fieldName) {
    return fieldName
      .replace(/[_\-]/g, " ")
      .replace(/\w\S*/g, (txt) => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
  }

  isEmpty(value) {
    if (value === null || value === undefined || value === "" || value === "[]") {
      return true;
    }
    if (Array.isArray(value) && value.length === 0) {
      return true;
    }
    if (typeof value === "string" && value.trim() === "") {
      return true;
    }
    return false;
  }
}
