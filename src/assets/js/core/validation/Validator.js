import BrowserLogger from "js/core/utils/logger";
import ValidatorFactory from "js/core/validation/factory/ValidatorFactory";
import ValidatorCacheManager from "js/core/utils/CacheManager";

const logger = new BrowserLogger("Validator");

export default class Validator {
  constructor(rules, formData = {}, globalSettings = {}) {
    this.rules = rules?.rules || rules || {};
    this.formData = formData;
    this.globalSettings = globalSettings;
    this.errors = {};

    // Use the cache manager
    this.cacheManager = new ValidatorCacheManager({
      enableStats: true, // Enable stats for debugging
      maxSize: 500, // Prevent memory leaks
      ttl: 5 * 60 * 1000, // 5 minute TTL
    });

    logger.debug("✅ Validator initialized", {
      ruleCount: Object.keys(this.rules).length,
    });
  }
  validateField(fieldName) {
    const fieldRules = this.getFieldRules(fieldName);
    const value = this.formData[fieldName];

    if (!fieldRules) {
      return true;
    }

    let isValid = true;

    for (const [ruleName, ruleValue] of Object.entries(fieldRules)) {
      if (ruleName === "display") continue;

      // Generate cache key using the cache manager
      const cacheKey = this.cacheManager.generateKey(fieldName, ruleName, ruleValue);

      // Get from cache
      let validator = this.cacheManager.get(cacheKey);

      if (!validator) {
        validator = this.createValidator(ruleName, fieldName, value, ruleValue);
        if (validator) {
          // Store in cache
          this.cacheManager.set(cacheKey, validator);
          logger.debug(`🔄 Created and cached validator for: ${fieldName}:${ruleName}`);
        }
      } else {
        // Update existing validator with current value and formData
        validator.value = value;
        validator.formData = this.formData;
        logger.debug(`✅ Using cached validator for: ${fieldName}:${ruleName}`);
      }

      if (validator) {
        try {
          const error = validator.validate(this.formData);
          if (error) {
            const errorKey = error.fieldPath || fieldName;
            delete this.errors[errorKey];
            this.errors[errorKey] = error;
            isValid = false;
            break;
          }
        } catch (error) {
          logger.error("Validator error", error, { fieldName, ruleName });
        }
      }
    }

    return isValid;
  }

  getFieldDisplayName(fieldName) {
    return this.findDisplayInRules(fieldName) || this.formatDisplayName(fieldName);
  }

  getFieldRules(fieldName) {
    logger.debug("🔍 getFieldRules called for:", fieldName);

    // Direct field rules
    if (this.rules[fieldName]) {
      logger.debug("✅ Found direct rules for:", fieldName);
      return this.rules[fieldName];
    }

    // Try deeply nested first
    const deeplyNestedRules = this.getDeeplyNestedRules(fieldName);
    if (deeplyNestedRules) return deeplyNestedRules;

    // Try regular nested
    const nestedRules = this.getNestedRules(fieldName);
    if (nestedRules) return nestedRules;

    logger.debug("❌ No rules found for:", fieldName);
    return null;
  }

  // Helper methods for rule extraction
  getDeeplyNestedRules(fieldName) {
    const match = this.parseDeeplyNestedFieldName(fieldName);
    if (!match) return null;

    const { arrayName, nestedArrayName, field } = match;
    const arrayRules = this.rules[arrayName];

    if (arrayRules?.items?.rules?.[nestedArrayName]?.items?.rules?.[field]) {
      logger.debug("✅ Found deeply nested rules:", { arrayName, nestedArrayName, field });
      return arrayRules.items.rules[nestedArrayName].items.rules[field];
    }

    return null;
  }

  getNestedRules(fieldName) {
    const match = this.parseNestedFieldName(fieldName);
    if (!match) return null;

    const { arrayName, field } = match;
    const arrayRules = this.rules[arrayName];

    if (arrayRules?.items?.rules?.[field]) {
      logger.debug("✅ Found nested rules:", { arrayName, field });
      return arrayRules.items.rules[field];
    }

    return null;
  }

  findDisplayInRules(fieldName) {
    const pathParts = fieldName.split(/[\[\]]+/).filter((part) => part !== "");
    return this.traverseRulesForDisplay(this.rules, pathParts);
  }

  traverseRulesForDisplay(rules, pathParts) {
    if (pathParts.length === 0 || !rules) return null;

    const currentPart = pathParts[0];
    const remainingParts = pathParts.slice(1);

    // Skip numeric array indices
    if (!isNaN(currentPart)) {
      return this.traverseRulesForDisplay(rules, remainingParts);
    }

    const currentRules = rules[currentPart];
    if (!currentRules) return null;

    // Found display name at this level
    if (currentRules.display && remainingParts.length === 0) {
      return currentRules.display;
    }

    // Check items rules for arrays
    if (currentRules.items?.rules) {
      return this.traverseRulesForDisplay(currentRules.items.rules, remainingParts);
    }

    // Check nested rules
    if (currentRules.rules) {
      return this.traverseRulesForDisplay(currentRules.rules, remainingParts);
    }

    // Continue deeper if more parts exist
    if (remainingParts.length > 0) {
      return this.traverseRulesForDisplay(currentRules, remainingParts);
    }

    return null;
  }

  createValidator(ruleName, fieldName, value, ruleValue) {
    const display = this.getFieldDisplayName(fieldName);
    const messageTemplate = this.getMessageTemplate(ruleName);
    const classes = this.getErrorClasses();

    const errorParams = {
      message: messageTemplate,
      classes,
      fieldName: fieldName,
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

  validateAll() {
    this.errors = {};
    let isValid = true;

    // Get unique field names (avoid duplicates)
    const uniqueFieldNames = new Set();

    Object.keys(this.formData).forEach((fieldName) => {
      // Skip dot notation if bracket notation exists
      if (fieldName.includes(".")) {
        const bracketVersion = fieldName.replace(/\.(\w+)/g, "[$1]");
        if (this.formData[bracketVersion] !== undefined) {
          return; // Skip dot notation, use bracket instead
        }
      }
      uniqueFieldNames.add(fieldName);
    });

    // Validate only unique field names
    Array.from(uniqueFieldNames).forEach((fieldName) => {
      if (!this.validateField(fieldName)) {
        isValid = false;
      }
    });

    logger.debug("Validation completed", {
      isValid,
      errorCount: Object.keys(this.errors).length,
      errorKeys: Object.keys(this.errors),
    });

    return isValid;
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
      array: "%s must be an array.",
      max_items: "%s cannot have more than %s items.",
      max_files: "%s cannot have more than %s files.",
      file_size: "%s file size exceeds the limit.",
      upload_limit: "%s exceeds upload limit.",
      post_limit: "%s exceeds post limit.",
      mimes: "%s file type is not allowed.",
      required: "%s is required.",
      integer: "%s must be a whole number.", // 👈 ADD THIS
      numeric: "%s must be a number.",
      default: "%s is invalid.",
    };

    return defaultMessages[ruleName] || defaultMessages.default;
  }

  // Utility methods
  parseDeeplyNestedFieldName(fieldName) {
    const deeplyNestedRegex = /^(\w+)\[(\d+)\]\[(\w+)\]\[(\d+)\]\[(\w+)\]/;
    const match = fieldName.match(deeplyNestedRegex);
    return match
      ? {
          arrayName: match[1],
          index: match[2],
          nestedArrayName: match[3],
          nestedIndex: match[4],
          field: match[5],
        }
      : null;
  }

  parseNestedFieldName(fieldName) {
    // Handle bracket notation: variations[0][name]
    // const bracketRegex = /^(\w+)\[(\d+)\]\[(\w+)\]/;
    // let match = fieldName.match(bracketRegex);
    const bracketRegex = /^(\w+)\[(\d+)\]\[(\w+)\]$/;
    let match = fieldName.match(bracketRegex);

    if (match) {
      return {
        arrayName: match[1],
        index: match[2],
        field: match[3],
      };
    }

    // Handle dot notation: variations[0].name
    const dotRegex = /^(\w+)\[(\d+)\]\.(\w+)/;
    match = fieldName.match(dotRegex);

    if (match) {
      return {
        arrayName: match[1],
        index: match[2],
        field: match[3],
      };
    }

    return null;
  }

  formatDisplayName(fieldName) {
    return fieldName
      .replace(/[_\-]/g, " ")
      .replace(/\w\S*/g, (txt) => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
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
  clearCache(fieldName = null) {
    if (fieldName) {
      this.cacheManager.clearField(fieldName);
    } else {
      this.cacheManager.clear();
    }
  }
  getCacheStats() {
    return this.cacheManager.getStats();
  }
}
