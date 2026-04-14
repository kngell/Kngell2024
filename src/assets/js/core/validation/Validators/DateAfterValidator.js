import BaseValidator from "../BaseValidator";

export default class DateAfterValidator extends BaseValidator {
  validate() {
    if (this.isEmpty(this.value)) return null;

    const otherDateValue = this.resolveFieldValue(this.ruleValue);
    if (this.isEmpty(otherDateValue)) return null;

    const dateCurrent = new Date(this.value);
    const dateOther = new Date(otherDateValue);

    if (dateCurrent <= dateOther) {
      // message: "%s must be after %s"
      return this.errorMessage(this.errorParams.message, this.display, this.ruleValue);
    }

    return null;
  }
}
