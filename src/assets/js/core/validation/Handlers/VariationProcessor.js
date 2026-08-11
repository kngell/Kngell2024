// js/core/utils/VariationProcessor.js
import BrowserLogger from "js/core/utils/logger";

const logger = new BrowserLogger("VariationProcessor");

export default class VariationProcessor {
  /**
   * Processes parsed form data to apply variation-specific business logic.
   *
   * @param {Object} data - The parsed form data
   * @returns {Object} The processed data with correct variation structures
   */
  process(data) {
    this._ensureCollectionsAreArrays(data);
    return data;
  }

  _ensureCollectionsAreArrays(obj) {
    if (!obj || typeof obj !== "object") return;

    for (const key in obj) {
      const value = obj[key];

      if (value && typeof value === "object") {
        this._ensureCollectionsAreArrays(value);
      }

      if (
        (key === "attributes" || key === "variations") &&
        value &&
        typeof value === "object" &&
        !Array.isArray(value)
      ) {
        const keys = Object.keys(value);
        const arr = [];

        if (keys.length > 0 && keys.every((k) => /^\d+$/.test(k))) {
          const sortedKeys = keys.sort((a, b) => parseInt(a, 10) - parseInt(b, 10));
          for (const k of sortedKeys) {
            arr.push(value[k]);
          }
        } else {
          // Keys are strings/UUIDs - just push their values
          for (const k of keys) {
            arr.push(value[k]);
          }
        }

        obj[key] = arr;
        logger.debug(`Converted ${key} object to array`, { originalKeys: keys });
      }
    }
  }
}
