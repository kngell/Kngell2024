import ValidatorRegistry from "js/core/validation/Registry/ValidatorRegistry";

export default class ValidatorFactory {
  static createValidator(ruleName, errorParams, display, value, ruleValue, formData = {}) {
    console.log(`🏭 Factory creating validator for: ${ruleName}`, {
      ruleName,
      errorParams,
      display,
      value,
      ruleValue,
      formData: Object.keys(formData),
    });

    const ValidatorClass = ValidatorRegistry.getValidator(ruleName);

    if (!ValidatorClass) {
      console.warn(`❌ No validator found for rule: ${ruleName}`);
      console.log("Available validators:", ValidatorRegistry.getAllValidatorNames());
      return null;
    }

    console.log(`✅ Found validator class: ${ValidatorClass.name}`);

    try {
      // ✅ Add required_checked to the list of context-aware validators
      const needsContext = [
        "required_if",
        "lte",
        "gte",
        "unique_in_array",
        "required_checked", // ⬅️ ADD THIS LINE
      ].includes(ruleName);

      console.log(`Needs context: ${needsContext} for ${ruleName}`);

      if (needsContext) {
        const validator = new ValidatorClass(errorParams, display, value, ruleValue, formData);
        console.log(`Created ${ruleName} validator with context:`, validator);
        return validator;
      }

      const validator = new ValidatorClass(errorParams, display, value, ruleValue);
      console.log(`Created ${ruleName} validator:`, validator);
      return validator;
    } catch (error) {
      console.error(`❌ Failed to create validator for ${ruleName}:`, error);
      console.error("Error details:", {
        ruleName,
        errorParams,
        display,
        value,
        ruleValue,
        ValidatorClass,
      });
      return null;
    }
  }
}
