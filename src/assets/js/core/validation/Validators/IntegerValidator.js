import BaseValidator from "../BaseValidator";

export default class IntegerValidator extends BaseValidator {
  validate() {
    if (this.isEmpty(this.value)) {
      return null;
    }
    if (typeof this.value === "string" && this.value.trim() === "") {
      return this.errorMessage(this.errorParams.message, this.display);
    }

    const num = Number(this.value);
    if (!Number.isInteger(num)) {
      return this.errorMessage(this.errorParams.message, this.display);
    }

    return null;
  }
}
