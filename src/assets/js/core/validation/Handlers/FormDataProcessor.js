import BrowserLogger from "js/core/utils/logger";

const logger = new BrowserLogger("FormDataProcessor");

export default class FormDataProcessor {
  processFormData(form) {
    const formData = new FormData(form);
    const result = {};

    // First pass: collect all flat field data
    for (const [key, value] of formData.entries()) {
      const field = form.querySelector(`[name="${key}"]`);
      this._processFlatField(field, key, value, result);
    }

    // Second pass: create nested structure for submission
    const nestedData = this._createNestedData(result);

    // Third pass: also keep flat fields for validation
    const finalData = { ...result, ...nestedData };

    logger.debug("Form data processed:", {
      flatKeys: Object.keys(result),
      nestedKeys: Object.keys(nestedData),
      hasVariations: !!nestedData.variations,
      variationTypeFields: Object.keys(result).filter((k) => k.includes("variation_type"))
    });

    return finalData;
  }

  _processFlatField(field, key, value, result) {
    // Convert checkbox values - FIXED VERSION
    if (field?.type === "checkbox") {
      // For checkboxes, use the checked property, not the form value
      result[key] = field.checked;
    }
    // Convert number fields
    else if (field?.type === "number") {
      result[key] = value === "" ? null : Number(value);
    }
    // Convert on/off to boolean
    else if (value === "on" || value === "off") {
      result[key] = value === "on";
    }
    // Keep everything else as-is
    else {
      result[key] = value;
    }
  }

  _createNestedData(flatData) {
    const nested = {};

    Object.entries(flatData).forEach(([key, value]) => {
      // Handle variations[0][field] format
      const variationMatch = key.match(/variations\[(\d+)\]\[([^\[\]]+)\]/);
      const attributeMatch = key.match(/variations\[(\d+)\]\[attributes\]\[(\d+)\]\[([^\]]+)\]/);

      if (attributeMatch) {
        this._setNestedValue(nested, key, value);
      } else if (variationMatch) {
        this._setNestedValue(nested, key, value);
      }
      // Skip - we already have flat version in result
    });

    return nested;
  }

  _setNestedValue(obj, path, value) {
    const parts = path
      .replace(/\]/g, "")
      .split("[")
      .filter((p) => p);
    let current = obj;

    for (let i = 0; i < parts.length - 1; i++) {
      const part = parts[i];
      const nextPart = parts[i + 1];

      if (!current[part]) {
        current[part] = /^\d+$/.test(nextPart) ? [] : {};
      }

      current = current[part];
    }

    const lastPart = parts[parts.length - 1];
    current[lastPart] = value;
  }
}
