import BaseValidator from "../BaseValidator";

export default class EmailValidator extends BaseValidator {
  validate() {
    if (!this.isEmpty(this.value)) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(this.value.trim())) {
        return this.errorMessage(this.errorParams.message, this.display);
      }
    }
    return null;
  }
}
