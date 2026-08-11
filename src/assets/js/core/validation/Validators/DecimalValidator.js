import BaseValidator from "../BaseValidator";

export default class DecimalValidator extends BaseValidator {
  validate(formData = {}) {
    // Skip if empty (let RequiredValidator handle)
    if (this.isEmpty(this.value)) {
      return null;
    }

    // Ensure it's numeric first
    if (isNaN(Number(this.value))) {
      return this.errorMessage(this.errorParams.message || "%s must be a number", this.display);
    }

    // Parse rule value
    const totalDigits = this.ruleValue?.total || 15;
    const decimalPlaces = this.ruleValue?.decimals || 5;
    const integerDigits = totalDigits - decimalPlaces;

    const valueStr = this.value.toString();
    const parts = valueStr.split(".");

    let integerPart = parts[0];
    const decimalPart = parts[1] || "";

    // Remove negative sign for length check
    let isNegative = integerPart.startsWith("-");
    if (isNegative) {
      integerPart = integerPart.substring(1);
    }

    const integerLength = integerPart.length;
    const decimalLength = decimalPart.length;

    // Check integer part length
    if (integerLength > integerDigits) {
      const maxValue = this.calculateMaxValue(integerDigits, decimalPlaces);
      return this.errorMessage(
        this.errorParams.messages?.max_integer ||
          `%s has ${integerLength} digits before decimal, maximum is ${integerDigits}. Maximum value: ${maxValue}`,
        this.display
      );
    }

    // Check decimal part length
    if (decimalLength > decimalPlaces) {
      return this.errorMessage(
        this.errorParams.messages?.max_decimal ||
          `%s has ${decimalLength} decimal places, maximum is ${decimalPlaces}`,
        this.display
      );
    }

    return null;
  }

  calculateMaxValue(integerDigits, decimalPlaces) {
    const maxInteger = "9".repeat(integerDigits);
    const maxDecimal = "9".repeat(decimalPlaces);

    if (decimalPlaces > 0) {
      return maxInteger + "." + maxDecimal;
    }

    return maxInteger;
  }
}
