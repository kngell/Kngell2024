import BaseValidator from "../BaseValidator";
import ValidatorFactory from "js/core/validation/factory/ValidatorFactory";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("ItemsValidator");

export default class ItemsValidator extends BaseValidator {
  constructor(errorParams, display, value, ruleValue = null, formData = {}) {
    super(errorParams, display, value, ruleValue);
    this.parentFieldName = errorParams.fieldName || display;
  }

  validate(formData = {}) {
    if (!Array.isArray(this.value)) return null;

    const itemsRule = this.ruleValue;
    if (!itemsRule || itemsRule.type !== "object" || !itemsRule.rules) {
      return null;
    }

    let firstError = null;

    this.value.forEach((item, index) => {
      const itemError = this.validateItem(item, index, itemsRule.rules, formData);
      if (itemError && !firstError) {
        firstError = itemError;
      }
    });

    return firstError;
  }
  validateItem(item, index, itemRules, formData) {
    for (const [field, fieldRules] of Object.entries(itemRules)) {
      const fieldValue = item[field];
      const fullFieldName = `${this.getParentFieldName()}[${index}][${field}]`;
      const display = fieldRules.display || this.formatDisplayName(field);

      if (fieldRules.array && fieldRules.items) {
        const nestedValidator = new ItemsValidator(
          { ...this.errorParams, fieldName: fullFieldName },
          display,
          fieldValue,
          fieldRules.items,
          formData
        );
        nestedValidator.setParentFieldName(fullFieldName);
        const nestedError = nestedValidator.validate(formData);
        if (nestedError) return nestedError;
      } else {
        // ✅ We pass fullFieldName into validateFieldRules
        const fieldError = this.validateFieldRules(
          fieldValue,
          fieldRules,
          display,
          formData,
          fullFieldName
        );
        if (fieldError) {
          return {
            ...fieldError,
            fieldPath: fullFieldName, // Critical for UI highlighting
            displayName: display
          };
        }
      }
    }
    return null;
  }

  // 1. Update the signature to accept 'fieldPath'
  validateFieldRules(value, rules, display, formData, fieldPath) {
    if (rules.required !== undefined) {
      const requiredValidator = ValidatorFactory.createValidator(
        "required",
        { ...this.errorParams, displayName: display, fieldPath: fieldPath }, // Use passed path
        display,
        value,
        rules.required,
        formData
      );

      if (requiredValidator) {
        const error = requiredValidator.validate(formData);
        if (error) return error;
      }
    }

    // Check other rules
    for (const [ruleName, ruleValue] of Object.entries(rules)) {
      logger.debug(`🔍 Validating field ${fieldPath} with rules:`, Object.keys(rules));
      if (
        ruleName === "display" ||
        ruleName === "array" ||
        ruleName === "items" ||
        ruleName === "required"
      )
        continue;

      logger.debug(`  → Creating ${ruleName} validator with ruleValue:`, ruleValue);

      const validator = ValidatorFactory.createValidator(
        ruleName,
        { ...this.errorParams, displayName: display, fieldPath: fieldPath },
        display,
        value,
        ruleName === "items" ? rules[ruleName] : ruleValue,
        formData
      );

      if (validator) {
        const error = validator.validate(formData);

        logger.debug(`  ❌ ${ruleName} validation failed:`, error);
        if (error) {
          logger.debug(`  ❌ ${ruleName} validation failed:`, error);
          return error;
        } else {
          logger.debug(`  ✅ ${ruleName} validation passed`);
        }
      } else {
        logger.debug(`  ⚠️ No validator found for rule: ${ruleName}`);
      }
    }

    return null;
  }

  formatDisplayName(fieldName) {
    return fieldName
      .replace(/[_\-]/g, " ")
      .replace(/\w\S*/g, (txt) => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
  }

  getParentFieldName() {
    return this.parentFieldName || "array";
  }

  setParentFieldName(parentFieldName) {
    this.parentFieldName = parentFieldName;
  }
}
