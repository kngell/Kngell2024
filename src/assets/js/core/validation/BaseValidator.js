import BrowserLogger from "js/utils/logger";
import { ValidationUtils } from "js/core/validation/utils/ValidationUtils";
const logger = new BrowserLogger("BaseValidator");

export default class BaseValidator {
  constructor(errorParams, display, value, ruleValue = null) {
    logger.debug("🔍 Validator created:", {
      display,
      value,
      ruleValue,
      type: typeof ruleValue,
    });
    this.errorParams = errorParams;
    this.display = display;
    this.value = value;
    this.ruleValue = ruleValue;
  }

  validate(formData = {}) {
    throw new Error("validate method must be implemented");
  }

  errorMessage(messageTemplate, ...params) {
    let formattedMessage = messageTemplate;
    params.forEach((param) => {
      formattedMessage = formattedMessage.replace(/%s/, param);
    });
    formattedMessage = formattedMessage.replace(/%s/g, this.display);
    return {
      message: formattedMessage,
      classes: this.errorParams.classes,
    };
  }

  isEmpty(value) {
    return ValidationUtils.isEmpty(value);
  }
}
