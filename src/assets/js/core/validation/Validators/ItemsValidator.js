// ItemsValidator.js - final clean version
import BaseValidator from "../BaseValidator.js";
import ValidatorFactory from "js/core/validation/factory/ValidatorFactory";

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
          formData,
        );
        const nestedError = nestedValidator.validate(formData);
        if (nestedError) return nestedError;
      } else {
        const fieldError = this.validateFieldRules(fieldValue, fieldRules, display, formData);
        if (fieldError) {
          return {
            ...fieldError,
            fieldPath: fullFieldName,
            displayName: display,
          };
        }
      }
    }
    return null;
  }

  validateFieldRules(value, rules, display, formData) {
    // Check required first
    if (rules.required !== undefined) {
      const requiredValidator = ValidatorFactory.createValidator(
        "required",
        { ...this.errorParams, displayName: display },
        display,
        value,
        rules.required,
        formData,
      );

      if (requiredValidator) {
        const error = requiredValidator.validate(formData);
        if (error) return error;
      }
    }

    // Check other rules
    for (const [ruleName, ruleValue] of Object.entries(rules)) {
      if (
        ruleName === "display" ||
        ruleName === "array" ||
        ruleName === "items" ||
        ruleName === "required"
      )
        continue;

      const validator = ValidatorFactory.createValidator(
        ruleName,
        { ...this.errorParams, displayName: display },
        display,
        value,
        ruleValue,
        formData,
      );

      if (validator) {
        const error = validator.validate(formData);
        if (error) return error;
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
