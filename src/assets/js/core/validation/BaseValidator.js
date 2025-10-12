import { BrowserLogger } from "js/utils/BrowserLogger";
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
    // Start with the message template
    let formattedMessage = messageTemplate;

    // Replace %s placeholders with parameters
    params.forEach((param) => {
      formattedMessage = formattedMessage.replace(/%s/, param);
    });

    // If we still have %s placeholders but no more params, use default values
    formattedMessage = formattedMessage.replace(/%s/g, this.display);

    return {
      message: formattedMessage,
      classes: this.errorParams.classes,
    };
  }

  isEmpty(value) {
    if (value === null || value === undefined || value === "" || value === "[]") {
      return true;
    }
    if (Array.isArray(value) && value.length === 0) {
      return true;
    }
    if (typeof value === "string" && value.trim() === "") {
      return true;
    }
    return false;
  }
}
