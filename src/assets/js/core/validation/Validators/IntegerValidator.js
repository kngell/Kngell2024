import BaseValidator from "../BaseValidator.js";

export default class IntegerValidator extends BaseValidator {
  validate() {
    if (this.isEmpty(this.value)) {
      return null;
    }

    const num = Number(this.value);
    const isInteger = Number.isInteger(num);

    if (!isInteger) {
      return this.errorMessage("%s must be an integer.");
    }

    return null;
  }
}
