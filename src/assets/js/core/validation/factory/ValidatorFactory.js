import ValidatorRegistry from "js/core/validation/Registry/ValidatorRegistry";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("ValidatorFactory");

export default class ValidatorFactory {
  static createValidator(ruleName, errorParams, display, value, ruleValue, formData = {}) {
    logger.debug(`🏭 Factory creating validator for: ${ruleName}`, {
      ruleName,
      errorParams,
      display,
      value,
      ruleValue,
      formData: Object.keys(formData)
    });

    const ValidatorClass = ValidatorRegistry.getValidator(ruleName);

    if (!ValidatorClass) {
      logger.warn(`❌ No validator found for rule: ${ruleName}`);
      logger.debug("Available validators:", ValidatorRegistry.getAllValidatorNames());
      return null;
    }

    logger.debug(`✅ Found validator class: ${ValidatorClass.name}`);

    try {
      // ✅ Add required_checked to the list of context-aware validators
      const needsContext = [
        "required_if",
        "lte",
        "gte",
        "unique_in_array",
        "required_checked" // ⬅️ ADD THIS LINE
      ].includes(ruleName);

      logger.debug(`Needs context: ${needsContext} for ${ruleName}`);

      if (needsContext) {
        const validator = new ValidatorClass(errorParams, display, value, ruleValue, formData);
        logger.debug(`Created ${ruleName} validator with context:`, validator);
        return validator;
      }

      const validator = new ValidatorClass(errorParams, display, value, ruleValue);
      logger.debug(`Created ${ruleName} validator:`, validator);
      return validator;
    } catch (error) {
      logger.error(`❌ Failed to create validator for ${ruleName}:`, error);
      logger.error("Error details:", {
        ruleName,
        errorParams,
        display,
        value,
        ruleValue,
        ValidatorClass
      });
      return null;
    }
  }
}
