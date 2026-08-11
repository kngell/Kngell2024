import BrowserLogger from "js/core/utils/logger";
import VariationProcessor from "./VariationProcessor";

const logger = new BrowserLogger("FormDataProcessor");

export default class FormDataProcessor {
  constructor() {
    this.variationProcessor = new VariationProcessor();
  }

  /**
   * Process a form into a data object suitable for validation and submission.
   *
   * @param {HTMLFormElement} form
   * @returns {Object} Processed form data
   */
  // processFormData(form) {
  //   const result = {};

  //   // Step 1: Process FormData entries (captures all submitted values)
  //   this._processFormDataEntries(form, result);

  //   // Step 2: Handle fields that FormData omits
  //   this._processOmittedFields(form, result);

  //   // Step 3: Build nested structures from bracket notation
  //   const nested = this._buildNestedStructures(result);

  //   // Step 4: Merge — nested overrides flat for structured fields
  //   let finalData = { ...result, ...nested };

  //   // Step 5: Delegate specific variation processing logic
  //   finalData = this.variationProcessor.process(finalData);

  //   logger.debug("Form data processed:", {
  //     fieldCount: Object.keys(result).length,
  //     nestedKeys: Object.keys(nested)
  //   });

  //   return finalData;
  // }

  // ─── Step 1: FormData Entries ─────────────────────────────
  processFormData(form) {
    const result = {};

    // Step 1: Process FormData entries
    this._processFormDataEntries(form, result);

    // Step 2: Handle omitted fields
    this._processOmittedFields(form, result);

    // Step 3: Build nested structures
    const nested = this._buildNestedStructures(result);

    // Step 4: Merge
    let finalData = { ...result, ...nested };

    // Step 5: Variation processing
    finalData = this.variationProcessor.process(finalData);

    return finalData;
  }
  /**
   * Extract all entries from FormData.
   * Handles multiple values for the same key (multi-select, checkbox groups).
   */
  _processFormDataEntries(form, result) {
    const formData = new FormData(form);

    // Track which keys have multiple entries
    const keyCounts = {};
    for (const [key] of formData.entries()) {
      keyCounts[key] = (keyCounts[key] || 0) + 1;
    }

    // Track which keys we've already started collecting
    const collected = new Set();

    for (const [key, value] of formData.entries()) {
      // For multi-value keys, collect all at once on first encounter
      if (keyCounts[key] > 1 && !collected.has(key)) {
        collected.add(key);
        const allValues = formData.getAll(key);
        const field = this._findField(form, key);
        result[key] = this._processMultiValue(field, key, allValues, form);
        continue;
      }

      // Skip if already collected as multi-value
      if (collected.has(key)) continue;

      // Single value
      const field = this._findField(form, key);
      result[key] = this._processValue(field, key, value);
    }

    // 👇 ADD THIS: Capture selects that FormData omitted due to disabled placeholder
    const selects = form.querySelectorAll("select[name]");
    selects.forEach((select) => {
      if (!(select.name in result)) {
        // Check if the selected option is a disabled placeholder
        const selectedOption = select.options?.[select.selectedIndex];
        if (selectedOption && selectedOption.disabled && selectedOption.value === "") {
          result[select.name] = ""; // Include empty for validation
          logger.debug(`Captured omitted select for validation: ${select.name} = ""`);
        } else {
          result[select.name] = select.value || "";
          logger.debug(`Captured omitted select: ${select.name} = "${select.value}"`);
        }
      }
    });
  }
  // _processFormDataEntries(form, result) {
  //   const formData = new FormData(form);

  //   // Track which keys have multiple entries
  //   const keyCounts = {};
  //   for (const [key] of formData.entries()) {
  //     keyCounts[key] = (keyCounts[key] || 0) + 1;
  //   }

  //   // Track which keys we've already started collecting
  //   const collected = new Set();

  //   for (const [key, value] of formData.entries()) {
  //     // For multi-value keys, collect all at once on first encounter
  //     if (keyCounts[key] > 1 && !collected.has(key)) {
  //       collected.add(key);
  //       const allValues = formData.getAll(key);
  //       const field = this._findField(form, key);
  //       result[key] = this._processMultiValue(field, key, allValues, form);
  //       continue;
  //     }

  //     // Skip if already collected as multi-value
  //     if (collected.has(key)) continue;

  //     // Single value
  //     const field = this._findField(form, key);
  //     result[key] = this._processValue(field, key, value);
  //   }
  // }

  // ─── Step 2: Omitted Fields ───────────────────────────────

  /**
   * Handle fields that FormData omits:
   * - Unchecked checkboxes
   * - Unselected radio groups
   * - Disabled fields (if needed for validation)
   */
  _processOmittedFields(form, result) {
    // Unchecked checkboxes
    const checkboxes = form.querySelectorAll('input[type="checkbox"][name]');
    checkboxes.forEach((cb) => {
      if (!(cb.name in result)) {
        result[cb.name] = false;
      }
    });

    // Unselected radio groups
    const radioNames = new Set();
    form.querySelectorAll('input[type="radio"][name]').forEach((radio) => {
      radioNames.add(radio.name);
    });

    radioNames.forEach((name) => {
      if (!(name in result)) {
        result[name] = null;
      }
    });
  }

  // ─── Value Processing ─────────────────────────────────────

  /**
   * Process a single field value based on its type.
   */
  _processValue(field, key, value) {
    if (!field) {
      return value;
    }

    const tag = field.tagName?.toLowerCase();
    const type = (field.type || "").toLowerCase();

    // Handle <select> elements explicitly
    if (tag === "select") {
      // Check if the selected option is a placeholder
      const selectedOption = field.options?.[field.selectedIndex];
      if (selectedOption && selectedOption.disabled && selectedOption.value === "") {
        return ""; // Placeholder selected — treat as empty
      }
      return value;
    }

    switch (type) {
      case "checkbox":
        return field.checked;

      case "radio":
        return value;

      case "number":
      case "range":
        return value === "" ? null : Number(value);

      case "file":
        return value;

      default:
        return value;
    }
  }

  /**
   * Process multiple values for the same field name.
   * Handles: checkbox groups, multi-selects.
   */
  _processMultiValue(field, key, values, form) {
    // Check if these are checkboxes (checkbox group)
    const fields = form.querySelectorAll(`input[type="checkbox"][name="${CSS.escape(key)}"]`);

    if (fields.length > 0) {
      // Return array of checked values
      return values;
    }

    // Multi-select or other multi-value field
    return values;
  }

  // ─── Field Finding ────────────────────────────────────────

  /**
   * Safely find a field by name, handling bracket notation.
   */
  _findField(form, name) {
    try {
      return form.querySelector(`[name="${CSS.escape(name)}"]`);
    } catch (e) {
      // Fallback: manual search
      return this._findFieldManually(form, name);
    }
  }

  /**
   * Fallback field finder for complex names.
   */
  _findFieldManually(form, name) {
    const fields = form.elements;
    for (let i = 0; i < fields.length; i++) {
      if (fields[i].name === name) {
        return fields[i];
      }
    }
    return null;
  }

  _buildNestedStructures(flatData) {
    const nested = {};
    let hasNested = false;

    Object.entries(flatData).forEach(([key, value]) => {
      if (!key.includes("[")) return;

      hasNested = true;
      this._setNestedValue(nested, key, value);
    });

    if (!hasNested) return {};

    return nested;
  }
  /**
   * Set a value at a nested path.
   * "items[0][name]" → obj.items[0].name = value
   */
  _setNestedValue(obj, path, value) {
    const parts = path
      .replace(/\]/g, "")
      .split("[")
      .filter((p) => p !== "");

    if (parts.length === 0) {
      obj[path] = value;
      return;
    }
    const processedParts = parts.map((part) => {
      return /^\d+$/.test(part) ? parseInt(part, 10) : part;
    });

    let current = obj;

    for (let i = 0; i < processedParts.length - 1; i++) {
      const part = processedParts[i];
      const nextPart = processedParts[i + 1];

      // Determine if current part should be an array or object
      const shouldBeArray = typeof nextPart === "number";

      if (current[part] === undefined) {
        current[part] = shouldBeArray ? [] : {};
      }

      current = current[part];
    }

    const lastPart = processedParts[processedParts.length - 1];
    current[lastPart] = value;
  }
}
