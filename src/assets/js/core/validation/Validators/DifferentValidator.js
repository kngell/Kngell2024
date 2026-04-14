import BaseValidator from "../BaseValidator";

export default class DifferentValidator extends BaseValidator {
  validate() {
    const otherValue = this.resolveFieldValue(this.ruleValue);

    if (this.isEmpty(this.value) || this.isEmpty(otherValue)) return null;

    if (String(this.value) === String(otherValue)) {
      return this.errorMessage(this.errorParams.message, this.display, this.ruleValue);
    }
    return null;
  }
}
