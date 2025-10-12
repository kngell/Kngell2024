import BaseValidator from "../BaseValidator.js";

export default class RequiredIfValidator extends BaseValidator {
  validate(formData = {}) {
    const [otherField, expectedValue] = this.parseRuleValue(this.ruleValue);
    const otherValue = formData[otherField];

    if (expectedValue !== null) {
      const otherValueString = this.convertToString(otherValue);
      const expectedValueString = this.convertToString(expectedValue);

      if (otherValueString === expectedValueString && this.isEmpty(this.value)) {
        return this.errorMessage(this.errorParams.message, this.display, otherField);
      }
    } else {
      if (!this.isEmpty(otherValue) && this.isEmpty(this.value)) {
        return this.errorMessage(this.errorParams.message, this.display, otherField);
      }
    }

    return null;
  }

  parseRuleValue(ruleValue) {
    if (ruleValue.includes("=")) {
      const [field, value] = ruleValue.split("=");
      return [field.trim(), value.trim()];
    }
    return [ruleValue.trim(), null];
  }

  convertToString(value) {
    if (typeof value === "boolean") {
      return value ? "true" : "false";
    }
    if (value === null || value === undefined) {
      return "";
    }
    return String(value);
  }
}
