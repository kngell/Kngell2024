import BaseValidator from "../BaseValidator.js";

export default class PatternValidator extends BaseValidator {
  validate() {
    if (!this.isEmpty(this.value)) {
      const pattern = new RegExp(this.ruleValue);
      if (!pattern.test(this.value)) {
        return this.errorMessage(this.errorParams.message, this.display);
      }
    }
    return null;
  }
}
