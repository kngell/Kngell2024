import BrowserLogger from "js/core/utils/logger";
import { ValidationUtils } from "js/core/utils/ValidationUtils";
const logger = new BrowserLogger("BaseValidator");

export default class BaseValidator {
  constructor(errorParams, display, value, ruleValue = null, formData = {}) {
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
    this.formData = formData; // Store formData for all children
  }

  validate(formData = {}) {
    throw new Error("validate method must be implemented");
  }

  resolveFieldValue(targetField) {
    if (!this.formData) return null;

    const path = this.errorParams.fieldPath;
    if (path && path.includes("[")) {
      const keys = path.match(/[^[\]]+/g);
      keys.pop();

      let current = this.formData;
      for (const key of keys) {
        if (current && current[key] !== undefined) {
          current = current[key];
        } else {
          current = null;
          break;
        }
      }

      if (current && current[targetField] !== undefined) {
        return current[targetField];
      }
    }
    return this.formData[targetField];
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
