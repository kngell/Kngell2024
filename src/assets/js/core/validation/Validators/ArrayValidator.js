import BaseValidator from "../BaseValidator.js";

export default class ArrayValidator extends BaseValidator {
  validate() {
    // If the field name contains array brackets, it's already structured as an array
    // so we should skip the array validation for individual fields
    if (typeof this.value === "string" && this.value.includes("[") && this.value.includes("]")) {
      return null; // Skip validation for array-structured field names
    }

    if (this.ruleValue === true && !Array.isArray(this.value)) {
      return this.errorMessage(this.errorParams.message, this.display);
    }
    return null;
  }
}
