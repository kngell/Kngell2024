import BaseValidator from "../BaseValidator";

export default class RequiredCheckedValidator extends BaseValidator {
  validate() {
    if (this.ruleValue === true || this.ruleValue === "true") {
      const isChecked = this.isCheckboxChecked();

      if (!isChecked) {
        return this.errorMessage(this.errorParams.message, this.display);
      }
    }

    return null;
  }

  isCheckboxChecked() {
    // Handle various checkbox value representations
    if (this.value === undefined || this.value === null) {
      return false;
    }

    // Boolean values
    if (typeof this.value === "boolean") {
      return this.value === true;
    }

    // String values
    if (typeof this.value === "string") {
      const normalizedValue = this.value.toLowerCase().trim();
      return (
        normalizedValue === "true" ||
        normalizedValue === "yes" ||
        normalizedValue === "on" ||
        normalizedValue === "1" ||
        normalizedValue === "checked"
      );
    }

    // Number values
    if (typeof this.value === "number") {
      return this.value === 1;
    }

    // Default case
    return !!this.value;
  }
}
