import BaseValidator from "../BaseValidator.js";

export default class PatternValidator extends BaseValidator {
  validate() {
    if (!this.isEmpty(this.value)) {
      // Convert boolean to string for pattern matching
      const valueToTest = typeof this.value === "boolean" ? this.value.toString() : this.value;

      const pattern = new RegExp(this.ruleValue);
      if (!pattern.test(valueToTest)) {
        return this.errorMessage(this.errorParams.message, this.display);
      }
    }
    return null;
  }
}
