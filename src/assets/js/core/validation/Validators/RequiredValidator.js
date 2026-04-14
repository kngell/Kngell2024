import BaseValidator from "../BaseValidator";

export default class RequiredValidator extends BaseValidator {
  validate() {
    if (this.isEmpty(this.value)) {
      return this.errorMessage(this.errorParams.message, this.display);
    }
    return null;
  }
}
