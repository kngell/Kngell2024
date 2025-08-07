export default class StepValidator {
  /**
   * @param {HTMLElement} stepElement The DOM element of the current form step.
   */
  constructor(stepElement) {
    this.stepElement = stepElement;
    this.inputs = this.stepElement.querySelectorAll("input[required]");
  }

  /**
   * Validates all required inputs within the current step.
   * Displays error messages and highlights invalid fields.
   * @returns {boolean} True if all inputs are valid, false otherwise.
   */
  validate() {
    let isValid = true;
    this.inputs.forEach((input) => {
      const errorElement = document.getElementById(`${input.id}Error`);
      input.classList.remove("invalid"); // Clear previous invalid state
      if (errorElement) {
        errorElement.textContent = "";
        errorElement.style.display = "none";
      }

      if (!input.value.trim()) {
        isValid = false;
        input.classList.add("invalid");
        if (errorElement) {
          errorElement.textContent = "This field is required.";
          errorElement.style.display = "block";
        }
      } else if (input.pattern && !new RegExp(input.pattern).test(input.value)) {
        isValid = false;
        input.classList.add("invalid");
        if (errorElement) {
          errorElement.textContent = input.title || "Invalid format.";
          errorElement.style.display = "block";
        }
      }
    });
    return isValid;
  }
}
