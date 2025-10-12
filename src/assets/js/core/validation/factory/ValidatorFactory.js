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
import ArrayValidator from "js/core/validation/Validators/ArrayValidator";
import MaxItemsValidator from "js/core/validation/Validators/MaxItemsValidator";
import ItemsValidator from "js/core/validation/Validators/ItemsValidator";

export default class ValidatorFactory {
  static createValidator(ruleName, errorParams, display, value, ruleValue, formData = {}) {
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
        return new RequiredIfValidator(errorParams, display, value, ruleValue, formData);
      case "min_value":
        return new MinValueValidator(errorParams, display, value, ruleValue);
      case "max_value":
        return new MaxValueValidator(errorParams, display, value, ruleValue);
      case "lte":
        return new LteValidator(errorParams, display, value, ruleValue);
      case "gte":
        return new GteValidator(errorParams, display, value, ruleValue);
      case "array":
        return new ArrayValidator(errorParams, display, value, ruleValue);
      case "max_items":
        return new MaxItemsValidator(errorParams, display, value, ruleValue);
      case "items":
        return new ItemsValidator(errorParams, display, value, ruleValue);
      default:
        return null;
    }
  }
}
