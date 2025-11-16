import BaseValidator from "../BaseValidator.js";

export default class LteValidator extends BaseValidator {
  constructor(errorParams, display, value, ruleValue, formData) {
    super(errorParams, display, value, ruleValue);
    this.formData = formData;
  }

  validate() {
    const comparisonValue = this.formData[this.ruleValue];

    if (!this.isEmpty(this.value) && !this.isEmpty(comparisonValue)) {
      if (Number(this.value) > Number(comparisonValue)) {
        return this.errorMessage(this.errorParams.message, this.display, comparisonValue);
      }
    }
    return null;
  }
}
