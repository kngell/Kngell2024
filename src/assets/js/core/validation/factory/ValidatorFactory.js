import ValidatorRegistry from "js/core/validation/Registry/ValidatorRegistry.js";

export default class ValidatorFactory {
  static createValidator(ruleName, errorParams, display, value, ruleValue, formData = {}) {
    const ValidatorClass = ValidatorRegistry.getValidator(ruleName);

    if (!ValidatorClass) {
      console.warn(`No validator found for rule: ${ruleName}`);
      return null;
    }

    try {
      if (ruleName === "required_if" || ruleName === "lte" || ruleName === "gte") {
        return new ValidatorClass(errorParams, display, value, ruleValue, formData);
      }

      return new ValidatorClass(errorParams, display, value, ruleValue);
    } catch (error) {
      console.error(`Failed to create validator for ${ruleName}:`, error);
      return null;
    }
  }
}
