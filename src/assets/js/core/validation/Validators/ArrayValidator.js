import BaseValidator from "../BaseValidator.js";

export default class ArrayValidator extends BaseValidator {
  validate() {
    if (this.ruleValue === true && !Array.isArray(this.value)) {
      return this.errorMessage(this.errorParams.message, this.display);
    }
    return null;
  }
}
