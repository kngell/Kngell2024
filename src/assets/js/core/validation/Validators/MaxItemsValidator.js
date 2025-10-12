import BaseValidator from "../BaseValidator.js";

export default class MaxItemsValidator extends BaseValidator {
  validate() {
    if (Array.isArray(this.value) && this.value.length > this.ruleValue) {
      return this.errorMessage(this.errorParams.message, this.display, this.ruleValue);
    }
    return null;
  }
}
