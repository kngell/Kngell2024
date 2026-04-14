import BaseValidator from "../BaseValidator";

export default class RequiredIfValidator extends BaseValidator {
  validate(formData = {}) {
    const [targetFieldName, expectedValue] = this.parseRuleValue(this.ruleValue);

    const actualValue = this.resolveFieldValue(targetFieldName);

    if (expectedValue === null) {
      if (!this.isEmpty(actualValue) && this.isEmpty(this.value)) {
        return this.errorMessage(this.errorParams.message, this.display, targetFieldName);
      }
    } else {
      const actualStr = this.convertToString(actualValue);
      const expectedStr = this.convertToString(expectedValue);

      if (actualStr === expectedStr && this.isEmpty(this.value)) {
        return this.errorMessage(this.errorParams.message, this.display, targetFieldName);
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
    if (value === true || value === "1" || value === "true" || value === "on") return "true";
    if (
      value === false ||
      value === "0" ||
      value === "false" ||
      value === null ||
      value === undefined
    )
      return "false";
    return String(value).trim();
  }
}
