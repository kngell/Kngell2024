export default class FormFormatter {
  constructor(numericFieldPatterns) {
    this.numericFieldPatterns = numericFieldPatterns || [];
  }

  isNumeric(fieldName) {
    if (!fieldName) return false;
    const lowerFieldName = fieldName.toLowerCase();

    return this.numericFieldPatterns.some((pattern) =>
      lowerFieldName.includes(pattern.toLowerCase()),
    );
  }

  getRawValue(formattedValue, isQuantity = false) {
    return formattedValue.toString().replace(/\s/g, "");
  }

  formatForDisplay(value, isQuantity = false) {
    const raw = this.getRawValue(value);
    if (raw === "") return "";

    // Format with thousand separators (spaces)
    const parts = raw.split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    return parts.join(".");
  }
}
