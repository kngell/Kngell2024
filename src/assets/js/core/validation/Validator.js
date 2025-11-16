import BrowserLogger from "js/utils/logger";
import ValidatorFactory from "js/core/validation/factory/ValidatorFactory";

const logger = new BrowserLogger("Validator");

export default class Validator {
  constructor(rules, formData = {}, globalSettings = {}) {
    this.rules = rules?.rules || rules || {};
    this.formData = formData;
    this.globalSettings = globalSettings;
    this.errors = {};

    logger.debug("✅ Validator initialized", {
      ruleCount: Object.keys(this.rules).length,
    });
  }

  // validateField(fieldName) {
  //   const fieldRules = this.getFieldRules(fieldName);
  //   const value = this.formData[fieldName];

  //   delete this.errors[fieldName];

  //   if (!fieldRules) {
  //     return true;
  //   }

  //   let isValid = true;

  //   // Process ALL rules except 'display'
  //   for (const [ruleName, ruleValue] of Object.entries(fieldRules)) {
  //     if (ruleName === "display") continue;

  //     const validator = this.createValidator(ruleName, fieldName, value, ruleValue);
  //     if (validator) {
  //       try {
  //         const error = validator.validate(this.formData);
  //         if (error) {
  //           this.errors[fieldName] = error;
  //           isValid = false;
  //           break;
  //         }
  //       } catch (error) {
  //         logger.error("Validator error", error, { fieldName, ruleName });
  //       }
  //     }
  //   }

  //   return isValid;
  // }
  validateField(fieldName) {
    const fieldRules = this.getFieldRules(fieldName);
    const value = this.formData[fieldName];

    delete this.errors[fieldName];

    if (!fieldRules) {
      return true;
    }

    let isValid = true;

    logger.debug("🔍 Validating field:", {
      fieldName,
      fieldRules: Object.keys(fieldRules),
      hasMaxFiles: !!fieldRules.max_files,
      maxFilesValue: fieldRules.max_files,
      value,
      valueType: typeof value,
      isFileList: value instanceof FileList,
      fileCount: value?.length || 0,
    });

    // Process ALL rules except 'display'
    for (const [ruleName, ruleValue] of Object.entries(fieldRules)) {
      if (ruleName === "display") continue;

      logger.debug("🔄 Processing rule:", {
        fieldName,
        ruleName,
        ruleValue,
        value,
      });

      const validator = this.createValidator(ruleName, fieldName, value, ruleValue);
      if (validator) {
        try {
          const error = validator.validate(this.formData);
          if (error) {
            this.errors[fieldName] = error;
            isValid = false;
            logger.debug("❌ Validation failed:", {
              fieldName,
              ruleName,
              error,
            });
            break;
          } else {
            logger.debug("✅ Rule passed:", ruleName);
          }
        } catch (error) {
          logger.error("Validator error", error, { fieldName, ruleName });
        }
      } else {
        logger.debug("⚠️ No validator found for rule:", ruleName);
      }
    }

    return isValid;
  }
  getFieldDisplayName(fieldName) {
    // Try to find display name in nested rules structure
    const findDisplayInRules = (rules, pathParts) => {
      if (pathParts.length === 0) return null;

      const currentPart = pathParts[0];
      const remainingParts = pathParts.slice(1);

      // Skip numeric array indices
      if (!isNaN(currentPart)) {
        return findDisplayInRules(rules, remainingParts);
      }

      // Check if current part exists in rules
      if (rules && rules[currentPart]) {
        // If we have display name and no more parts, return it
        if (rules[currentPart].display && remainingParts.length === 0) {
          return rules[currentPart].display;
        }

        // If we have items rules, search deeper
        if (rules[currentPart].items && rules[currentPart].items.rules) {
          return findDisplayInRules(rules[currentPart].items.rules, remainingParts);
        }

        // If we have direct rules (for nested objects), search deeper
        if (rules[currentPart].rules) {
          return findDisplayInRules(rules[currentPart].rules, remainingParts);
        }

        // If current part has display but we have more parts, continue searching
        if (remainingParts.length > 0) {
          return findDisplayInRules(rules[currentPart], remainingParts);
        }
      }

      return null;
    };

    // Parse field name like "variations[0][attributes][0][attribute_name]"
    const pathParts = fieldName.split(/[\[\]]+/).filter((part) => part !== "");

    // Try to find display name in nested structure
    const displayName = findDisplayInRules(this.rules, pathParts);

    if (displayName) {
      return displayName;
    }

    // Fallback: try to get from field rules directly
    const fieldRules = this.getFieldRules(fieldName);
    if (fieldRules && fieldRules.display) {
      return fieldRules.display;
    }

    // Final fallback: format the field name
    return this.formatDisplayName(fieldName);
  }

  getFieldRules(fieldName) {
    logger.debug("🔍 getFieldRules called for:", fieldName);

    // Direct field rules (like "name", "sku")
    if (this.rules[fieldName]) {
      logger.debug("✅ Found direct rules for:", fieldName);
      return this.rules[fieldName];
    }

    // Parse deeply nested field names like: variations[0][attributes][0][attribute_name]
    const deeplyNestedMatch = this.parseDeeplyNestedFieldName(fieldName);
    if (deeplyNestedMatch) {
      const { arrayName, index, nestedArrayName, nestedIndex, field } = deeplyNestedMatch;

      logger.debug("🎯 Deeply nested field detected:", deeplyNestedMatch);

      // Check if we have array rules with items
      const arrayRules = this.rules[arrayName];
      if (arrayRules && arrayRules.items && arrayRules.items.rules) {
        // Check for nested array rules (attributes array within variations)
        const nestedArrayRules = arrayRules.items.rules[nestedArrayName];
        if (nestedArrayRules && nestedArrayRules.items && nestedArrayRules.items.rules) {
          const fieldRules = nestedArrayRules.items.rules[field];
          logger.debug("✅ Found deeply nested rules for:", {
            arrayName,
            nestedArrayName,
            field,
            fieldRules,
          });
          return fieldRules;
        }
      }
    }

    // Parse regular nested field names like: variations[0][name]
    const nestedMatch = this.parseNestedFieldName(fieldName);
    if (nestedMatch) {
      const { arrayName, index, field } = nestedMatch;

      // Check if we have array rules with items
      const arrayRules = this.rules[arrayName];
      if (arrayRules && arrayRules.items && arrayRules.items.rules) {
        const fieldRules = arrayRules.items.rules[field];
        logger.debug("✅ Found nested rules for:", { arrayName, field, fieldRules });
        return fieldRules;
      }
    }

    logger.debug("❌ No rules found for:", fieldName);
    return null;
  }
  parseDeeplyNestedFieldName(fieldName) {
    // Match patterns like: variations[0][attributes][0][attribute_name]
    const deeplyNestedRegex = /^(\w+)\[(\d+)\]\[(\w+)\]\[(\d+)\]\[(\w+)\]/;
    const match = fieldName.match(deeplyNestedRegex);

    if (match) {
      const result = {
        arrayName: match[1], // "variations"
        index: match[2], // "0"
        nestedArrayName: match[3], // "attributes"
        nestedIndex: match[4], // "0"
        field: match[5], // "attribute_name" or "attribute_value"
      };
      logger.debug("✅ Parsed as deeply nested field:", result);
      return result;
    }

    return null;
  }
  parseNestedFieldName(fieldName) {
    logger.debug("🔄 Parsing nested field:", fieldName);

    // Handle bracket notation: variations[0][name]
    const bracketRegex = /^(\w+)\[(\d+)\]\[(\w+)\]/;
    let match = fieldName.match(bracketRegex);

    if (match) {
      const result = {
        arrayName: match[1],
        index: match[2],
        field: match[3],
      };
      logger.debug("✅ Parsed as bracket notation:", result);
      return result;
    }

    // Handle dot notation: variations[0].name
    const dotRegex = /^(\w+)\[(\d+)\]\.(\w+)/;
    match = fieldName.match(dotRegex);

    if (match) {
      const result = {
        arrayName: match[1],
        index: match[2],
        field: match[3],
      };
      logger.debug("✅ Parsed as dot notation:", result);
      return result;
    }

    logger.debug("❌ Could not parse field name:", fieldName);
    return null;
  }

  createValidator(ruleName, fieldName, value, ruleValue) {
    const display = this.getFieldDisplayName(fieldName);
    const messageTemplate = this.getMessageTemplate(ruleName);
    const classes = this.getErrorClasses();
    const errorParams = {
      message: messageTemplate,
      classes: classes,
    };

    return ValidatorFactory.createValidator(
      ruleName,
      errorParams,
      display,
      value,
      ruleValue,
      this.formData,
    );
  }

  getMessageTemplate(ruleName) {
    return this.globalSettings.messages?.[ruleName] || this.getDefaultMessage(ruleName);
  }

  getErrorClasses() {
    return this.globalSettings.classes?.hint || ["input-box__hint-text", "invalid-feedback"];
  }

  getErrorParentClasses() {
    return this.globalSettings.classes?.error || ["has-error"];
  }

  getDefaultMessage(ruleName) {
    const defaultMessages = {
      required: "%s is required.",
      min: "%s must be at least %s characters.",
      max: "%s must be at most %s characters.",
      pattern: "%s format is invalid.",
      numeric: "%s must be a number.",
      min_value: "%s must be at least %s.",
      max_value: "%s must be at most %s.",
      lte: "%s must be less than or equal to %s.",
      gte: "%s must be greater than or equal to %s.",
      required_if: "%s is required when %s is present.",
      default: "%s is invalid.",
    };

    return defaultMessages[ruleName] || defaultMessages.default;
  }

  validateAll() {
    this.errors = {};
    let isValid = true;

    const allFieldNames = Object.keys(this.formData);

    // Validate each field
    allFieldNames.forEach((fieldName) => {
      if (!this.validateField(fieldName)) {
        isValid = false;
      }
    });

    logger.debug("Validation completed", {
      isValid,
      errorCount: Object.keys(this.errors).length,
    });

    return isValid;
  }

  getErrors() {
    return this.errors;
  }

  clearErrors(fieldName = null) {
    if (fieldName) {
      delete this.errors[fieldName];
    } else {
      this.errors = {};
    }
  }

  formatDisplayName(fieldName) {
    return fieldName
      .replace(/[_\-]/g, " ")
      .replace(/\w\S*/g, (txt) => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
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
