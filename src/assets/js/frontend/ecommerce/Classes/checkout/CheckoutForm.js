import StepValidator from "./StepValidator";
export default class CheckoutForm {
  /**
   * @param {string} formId The ID of the main form element.
   */
  constructor(formId) {
    this.form = document.getElementById(formId);
    if (!this.form) {
      console.error(`Form with ID "${formId}" not found.`);
      return;
    }
    this.formSteps = this.form.querySelectorAll(".form-step");
    this.progressSteps = this.form.querySelectorAll(".progress-step");
    this.currentStep = 1; // Start at step 1
    this.validators = {}; // Store StepValidator instances for each step

    this._initializeValidators();
    this._addEventListeners();
    this.showStep(this.currentStep); // Display the first step on load
  }

  /**
   * Initializes StepValidator instances for each form step.
   * @private
   */
  _initializeValidators() {
    this.formSteps.forEach((stepElement) => {
      const stepNumber = parseInt(stepElement.dataset.step);
      this.validators[stepNumber] = new StepValidator(stepElement);
    });
  }

  /**
   * Adds event listeners for navigation buttons and form submission.
   * @private
   */
  _addEventListeners() {
    this.form.addEventListener("click", this._handleNavigationClick.bind(this));
    this.form.addEventListener("submit", this._handleSubmit.bind(this));
  }

  /**
   * Handles clicks on 'next' and 'previous' buttons.
   * @param {Event} event The click event.
   * @private
   */
  _handleNavigationClick(event) {
    if (event.target.classList.contains("next-btn")) {
      const nextStep = parseInt(event.target.dataset.nextStep);
      if (this.validators[this.currentStep].validate()) {
        if (nextStep === 3) {
          // Special case for review step
          this._populateReviewStep();
        }
        this.showStep(nextStep);
      }
    } else if (event.target.classList.contains("prev-btn")) {
      const prevStep = parseInt(event.target.dataset.prevStep);
      this.showStep(prevStep);
    }
  }

  /**
   * Handles the final form submission.
   * @param {Event} event The submit event.
   * @private
   */
  _handleSubmit(event) {
    event.preventDefault(); // Prevent default browser submission

    if (this.validators[this.currentStep].validate()) {
      // Collect all form data
      const formData = new FormData(this.form);
      const data = {};
      for (let [key, value] of formData.entries()) {
        data[key] = value;
      }

      console.log("Form Submitted!", data);
      alert("Order Placed Successfully! (Check console for data)");

      // In a real application, send 'data' to your backend using fetch() or similar
      // Example:
      // fetch('/api/place-order', {
      //     method: 'POST',
      //     headers: { 'Content-Type': 'application/json' },
      //     body: JSON.stringify(data)
      // })
      // .then(response => response.json())
      // .then(result => {
      //     console.log('Order API response:', result);
      //     // Handle success or error
      // })
      // .catch(error => {
      //     console.error('Error submitting order:', error);
      //     alert('There was an error placing your order. Please try again.');
      // });

      // Optionally reset the form or redirect after successful submission
      // this.form.reset();
      // this.showStep(1);
    } else {
      console.log("Validation failed on final submission.");
    }
  }

  /**
   * Displays a specific form step and updates the progress bar.
   * @param {number} stepNumber The number of the step to show.
   */
  showStep(stepNumber) {
    this.formSteps.forEach((step) => {
      if (parseInt(step.dataset.step) === stepNumber) {
        step.classList.add("active");
      } else {
        step.classList.remove("active");
      }
    });
    this._updateProgressBar(stepNumber);
    this.currentStep = stepNumber;
  }

  /**
   * Updates the visual state of the progress bar.
   * @param {number} stepNumber The number of the current active step.
   * @private
   */
  _updateProgressBar(stepNumber) {
    this.progressSteps.forEach((pStep) => {
      const pStepNum = parseInt(pStep.dataset.step);
      if (pStepNum < stepNumber) {
        pStep.classList.add("completed");
        pStep.classList.remove("active");
      } else if (pStepNum === stepNumber) {
        pStep.classList.add("active");
        pStep.classList.remove("completed");
      } else {
        pStep.classList.remove("active", "completed");
      }
    });
  }

  /**
   * Populates the review step with data from previous form fields.
   * @private
   */
  _populateReviewStep() {
    // Shipping Info
    document.getElementById("reviewFullName").textContent =
      document.getElementById("fullName").value;
    document.getElementById("reviewAddress").textContent = `${
      document.getElementById("addressLine1").value
    }`;
    document.getElementById("reviewCity").textContent = document.getElementById("city").value;
    document.getElementById("reviewPostalCode").textContent =
      document.getElementById("postalCode").value;
    document.getElementById("reviewCountry").textContent = document.getElementById("country").value;

    // Payment Info (masking card number for security)
    const cardNumber = document.getElementById("cardNumber").value;
    document.getElementById("reviewCardNumber").textContent = `**** **** **** ${cardNumber.slice(
      -4,
    )}`;
    document.getElementById("reviewCardName").textContent =
      document.getElementById("cardName").value;
    document.getElementById("reviewExpiryDate").textContent =
      document.getElementById("expiryDate").value;
  }
}
