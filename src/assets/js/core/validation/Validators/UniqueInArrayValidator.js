import BaseValidator from "../BaseValidator";

export default class UniqueInArrayValidator extends BaseValidator {
  validate(formData = {}) {
    if (this.isEmpty(this.value) || !formData) {
      return null;
    }
    const fieldPath = this.errorParams.fieldPath;
    if (!fieldPath) return null;

    const parentKey = fieldPath.split("[")[0];
    const subField = this.extractFieldName(fieldPath);

    const items = formData[parentKey];
    if (!Array.isArray(items)) {
      return null;
    }

    const occurrences = items.filter((item) => {
      const itemValue = item[subField];
      return itemValue !== undefined && String(itemValue) === String(this.value);
    }).length;

    if (occurrences > 1) {
      return this.errorMessage(this.errorParams.message, this.display);
    }

    return null;
  }
  extractFieldName(path) {
    const matches = path.match(/\[([^\]]+)\]$/);
    return matches ? matches[1] : path;
  }
}
