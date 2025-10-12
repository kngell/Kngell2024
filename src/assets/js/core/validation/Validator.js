import ValidatorFactory from "js/core/validation/factory/ValidatorFactory";

import { BrowserLogger } from "js/utils/BrowserLogger";
const logger = new BrowserLogger("Validator");

export default class Validator {
  constructor(rules, formData = {}, globalSettings = {}) {
    this.rules = rules?.rules || rules || {};
    this.formData = formData;
    this.globalSettings = globalSettings;
    this.errors = {};

    logger.debug("✅ Validator initialized with rules", {
      hasRules: !!rules,
      ruleCount: Object.keys(this.rules).length,
      ruleKeys: Object.keys(this.rules),
    });

    if (Object.keys(this.rules).length > 0) {
      this.debugActualRulesStructure();
      this.debugRulesStructure();
    } else {
      logger.warn("⚠️ Validator initialized with empty rules");
    }
  }
  debugActualRulesStructure() {
    logger.debug("🔍 COMPREHENSIVE RULES ANALYSIS");

    // 1. Show the complete rules structure
    logger.debug("📋 FULL RULES OBJECT:", this.rules);

    // 2. List ALL rule keys to see what's available
    const ruleKeys = Object.keys(this.rules);
    logger.debug("🔑 ALL RULE KEYS:", ruleKeys);

    // 3. Check for variations-related rules
    const variationsKeys = ruleKeys.filter(
      (key) => key.includes("variation") || key.includes("variant") || key.includes("attribute"),
    );
    logger.debug("🎯 VARIATIONS-RELATED KEYS:", variationsKeys);

    // 4. Check for array notation rules (like variations[0][name])
    const arrayNotationKeys = ruleKeys.filter((key) => key.includes("["));
    logger.debug("🧩 ARRAY NOTATION KEYS:", arrayNotationKeys);

    // 5. Show examples of what rules exist
    if (arrayNotationKeys.length > 0) {
      logger.debug("📝 EXAMPLES OF ARRAY RULES:");
      arrayNotationKeys.slice(0, 3).forEach((key) => {
        logger.debug(`   ${key}:`, this.rules[key]);
      });
    }

    // 6. Check if we have any nested structures
    const nestedStructures = ruleKeys.filter(
      (key) =>
        this.rules[key] &&
        typeof this.rules[key] === "object" &&
        (this.rules[key].items || this.rules[key].array),
    );
    logger.debug("🏗️ NESTED STRUCTURES:", nestedStructures);
  }
  // Also add this method to check a specific field:
  debugFieldLookup(fieldName) {
    logger.debug("🔎 FIELD LOOKUP DEBUG for:", fieldName);

    // Direct lookup
    logger.debug("📋 Direct lookup result:", this.rules[fieldName]);

    // Parse nested field
    const nestedMatch = this.parseNestedFieldName(fieldName);
    logger.debug("🧩 Nested field parsing:", nestedMatch);

    if (nestedMatch) {
      const { arrayName, index, field } = nestedMatch;
      logger.debug("🔍 Looking for array rules:", {
        arrayName,
        hasArrayRules: !!this.rules[arrayName],
        arrayRules: this.rules[arrayName],
      });

      if (this.rules[arrayName] && this.rules[arrayName].items) {
        logger.debug("📦 Array items structure:", this.rules[arrayName].items);

        if (this.rules[arrayName].items.rules) {
          logger.debug("🎯 Field rules from nested structure:", {
            field,
            fieldRules: this.rules[arrayName].items.rules[field],
          });
        }
      }
    }
  }

  validateField(fieldName) {
    logger.debug("🎯 Starting field validation", { fieldName });

    const fieldRules = this.getFieldRules(fieldName);
    const value = this.formData[fieldName];

    logger.debug("📋 Field rules and value", {
      fieldRules,
      value,
      fieldName,
    });

    delete this.errors[fieldName];

    if (!fieldRules) {
      logger.warn("❌ No validation rules found for field", { fieldName });
      return true;
    }

    logger.debug("✅ Rules found, processing validation rules");

    let isValid = true;

    // Process ALL rules except 'display'
    for (const [ruleName, ruleValue] of Object.entries(fieldRules)) {
      if (ruleName === "display") continue;

      logger.debug("🔄 Processing validation rule", {
        ruleName,
        ruleValue,
        fieldName,
      });

      const validator = this.createValidator(ruleName, fieldName, value, ruleValue);
      if (validator) {
        try {
          const error = validator.validate(this.formData);
          if (error) {
            logger.warn("🚨 Validation failed", {
              fieldName,
              ruleName,
              error,
            });
            this.errors[fieldName] = error;
            isValid = false;
            break;
          } else {
            logger.debug("✅ Validation rule passed", { fieldName, ruleName });
          }
        } catch (error) {
          logger.error("💥 Validator error", error, { fieldName, ruleName });
        }
      } else {
        logger.warn("⚠️ No validator available for rule", { ruleName, fieldName });
      }
    }

    logger.debug("📊 Field validation completed", {
      fieldName,
      isValid,
      errors: this.errors,
    });

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
      logger.debug("✅ Found display name for field", { fieldName, displayName });
      return displayName;
    }

    // Fallback: try to get from field rules directly
    const fieldRules = this.getFieldRules(fieldName);
    if (fieldRules && fieldRules.display) {
      return fieldRules.display;
    }

    // Final fallback: format the field name
    const fallbackName = this.formatDisplayName(fieldName);
    logger.debug("⚠️ Using fallback display name", { fieldName, fallbackName });
    return fallbackName;
  }
  // NEW METHOD: Get rules for nested fields
  getFieldRules(fieldName) {
    logger.debug("🔎 Looking up field rules", { fieldName });

    // Direct field rules (like "name", "sku")
    if (this.rules[fieldName]) {
      logger.debug("✅ Found direct field rules", { fieldName });
      return this.rules[fieldName];
    }

    // Parse nested field names like "variations[0][name]"
    const nestedMatch = this.parseNestedFieldName(fieldName);
    if (nestedMatch) {
      const { arrayName, index, field } = nestedMatch;

      logger.debug("🧩 Processing nested field", {
        fieldName,
        arrayName,
        index,
        field,
      });

      // Check if we have array rules with items
      const arrayRules = this.rules[arrayName];
      logger.debug("📦 Array rules lookup", {
        arrayName,
        hasArrayRules: !!arrayRules,
        arrayRules,
      });

      if (arrayRules && arrayRules.items && arrayRules.items.rules) {
        const fieldRules = arrayRules.items.rules[field];
        logger.debug("🎯 Field rules from nested structure", {
          field,
          fieldRules,
        });
        return fieldRules;
      } else {
        logger.warn("❌ No array items rules found", {
          arrayName,
          hasItems: !!(arrayRules && arrayRules.items),
          hasItemsRules: !!(arrayRules && arrayRules.items && arrayRules.items.rules),
        });
      }
    } else {
      logger.warn("❌ Could not parse nested field name", { fieldName });
    }

    logger.warn("❌ No rules found for field", { fieldName });
    return null;
  }

  // NEW METHOD: Parse nested field names
  parseNestedFieldName(fieldName) {
    logger.debug("🧩 Parsing nested field name", { fieldName });

    // Match patterns like:
    // variations[0][name]
    // variations[0][attributes][0][attribute_name]
    const regex = /^(\w+)\[(\d+)\]\[(\w+)\]/;
    const match = fieldName.match(regex);

    logger.debug("🔍 Regex parsing result", {
      fieldName,
      match,
      regex: regex.toString(),
    });

    if (!match) {
      logger.debug("❌ Field name doesn't match nested pattern", { fieldName });
      return null;
    }

    const arrayName = match[1];
    const index = match[2];
    const field = match[3];

    logger.debug("✅ Successfully parsed nested field", {
      arrayName,
      index,
      field,
    });

    return { arrayName, index, field };
  }

  // NEW METHOD: Debug rules structure
  debugRulesStructure() {
    logger.debug("🔍 VALIDATION RULES STRUCTURE ANALYSIS");

    // Check if variations rules exist
    if (this.rules.variations) {
      logger.debug("✅ Top-level 'variations' rules found", this.rules.variations);

      if (this.rules.variations.items) {
        logger.debug("✅ 'variations.items' found", this.rules.variations.items);

        if (this.rules.variations.items.rules) {
          logger.debug("✅ 'variations.items.rules' found", this.rules.variations.items.rules);

          // Check specific fields
          logger.debug("🔎 Checking nested field rules", {
            name: this.rules.variations.items.rules.name,
            sku: this.rules.variations.items.rules.sku,
            variant_type: this.rules.variations.items.rules.variant_type,
          });
        } else {
          logger.error("❌ 'variations.items.rules' is missing");
        }
      } else {
        logger.error("❌ 'variations.items' is missing");
      }
    } else {
      logger.error("❌ Top-level 'variations' rules are missing");
    }

    // Log all available rule keys for reference
    logger.debug("📋 All available rule keys", Object.keys(this.rules));
  }

  createValidator(ruleName, fieldName, value, ruleValue) {
    logger.debug("🔧 Creating validator", {
      ruleName,
      fieldName,
      ruleValue,
      value,
    });

    const display = this.getFieldDisplayName(fieldName);
    const messageTemplate = this.getMessageTemplate(ruleName);
    const classes = this.getErrorClasses();
    const errorParams = {
      message: messageTemplate,
      classes: classes,
    };

    // Use ValidatorFactory for all standard validators
    const standardValidator = ValidatorFactory.createValidator(
      ruleName,
      errorParams,
      display,
      value,
      ruleValue,
      this.formData,
    );

    if (standardValidator) {
      return standardValidator;
    }
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

    for (const fieldName in this.rules) {
      if (!this.validateField(fieldName)) {
        isValid = false;
      }
    }

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
