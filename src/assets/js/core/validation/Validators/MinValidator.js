import BaseValidator from "../BaseValidator.js";

export default class MinValidator extends BaseValidator {
  validate() {
    if (!this.isEmpty(this.value) && this.value.length < this.ruleValue) {
      return this.errorMessage(this.errorParams.message, this.display, this.ruleValue);
    }
    return null;
  }
}
