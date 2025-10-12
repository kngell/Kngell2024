import BaseValidator from "../BaseValidator.js";

export default class MaxValueValidator extends BaseValidator {
  validate() {
    if (!this.isEmpty(this.value) && Number(this.value) > Number(this.ruleValue)) {
      return this.errorMessage(this.errorParams.message, this.display, this.ruleValue);
    }
    return null;
  }
}
