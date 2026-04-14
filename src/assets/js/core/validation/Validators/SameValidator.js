import BaseValidator from "../BaseValidator";

export default class SameValidator extends BaseValidator {
  validate() {
    // 1. Resolve the value using the BaseValidator intelligence
    const otherValue = this.resolveFieldValue(this.ruleValue);

    // 2. Compare (null/undefined check handled by convertToString if desired)
    if (String(this.value) !== String(otherValue)) {
      return this.errorMessage(this.errorParams.message, this.display, this.ruleValue);
    }

    return null;
  }
}
