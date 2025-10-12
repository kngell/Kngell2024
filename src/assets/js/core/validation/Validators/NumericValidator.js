import BaseValidator from "../BaseValidator.js";

export default class NumericValidator extends BaseValidator {
  validate() {
    if (!this.isEmpty(this.value) && isNaN(Number(this.value))) {
      return this.errorMessage(this.errorParams.message, this.display);
    }
    return null;
  }
}
