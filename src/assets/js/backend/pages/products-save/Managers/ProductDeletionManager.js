import BrowserLogger from "js/core/utils/logger";
import Validator from "js/core/helpers/NotificationHelper";

const logger = new BrowserLogger("ProductDeletionManager");

export default class ProductDeletionManager {
  constructor(options) {
    this.options = options;
    this.elements = {};
    this.validator = null;
    this.isInitialized = false;
    this.formSubmitted = false;
  }

  async init() {
    if (this.isInitialized) {
      logger.warn("Deletion manager already initialized");
      return;
    }

    try {
      logger.info("Initializing product deletion manager");

      // 1. Setup DOM elements
      this.setupElements();

      // 2. Setup validation
      await this.setupValidation();

      // 3. Setup UI
      this.setupUI();

      // 4. Setup events
      this.setupEvents();

      // 5. Setup safety features
      this.setupSafetyFeatures();

      this.isInitialized = true;
      logger.success("Product deletion manager initialized");
    } catch (error) {
      logger.error("Failed to initialize deletion manager", error);
      this.showCriticalError(error.message);
    }
  }

  setupElements() {
    this.elements = {
      form: document.getElementById("deleteForm"),
      confirmDelete: document.getElementById("confirmDelete"),
      confirmIrreversible: document.getElementById("confirmIrreversible"),
      deleteButton: document.getElementById("deleteButton"),
    };

    if (!this.validateElements()) {
      throw new Error("Required deletion form elements not found");
    }
  }

  validateElements() {
    const required = ["form", "confirmDelete", "confirmIrreversible", "deleteButton"];
    const missing = required.filter((key) => !this.elements[key]);

    if (missing.length > 0) {
      logger.error(`Missing elements: ${missing.join(", ")}`);
      return false;
    }

    return true;
  }

  async setupValidation() {
    // Load deletion rules
    const { rules, settings } = await this.options.rulesService.load("product_deletion");

    // Create validator
    this.validator = new Validator(rules, {}, settings);

    logger.debug("Validation setup complete");
  }

  setupUI() {
    this.updateButtonState();
  }

  updateButtonState() {
    const isReady =
      this.elements.confirmDelete.checked && this.elements.confirmIrreversible.checked;

    this.elements.deleteButton.disabled = !isReady;

    // Visual feedback
    if (isReady) {
      this.elements.deleteButton.classList.remove("btn-disabled", "btn-secondary");
      this.elements.deleteButton.classList.add("btn-danger");
    } else {
      this.elements.deleteButton.classList.add("btn-disabled", "btn-secondary");
      this.elements.deleteButton.classList.remove("btn-danger");
    }
  }

  setupEvents() {
    // Checkbox changes
    this.elements.confirmDelete.addEventListener("change", () => this.onCheckboxChange());
    this.elements.confirmIrreversible.addEventListener("change", () => this.onCheckboxChange());

    // Form submission
    this.elements.form.addEventListener("submit", (e) => this.handleSubmit(e));

    // Delete button (for manual trigger)
    this.elements.deleteButton.addEventListener("click", (e) => {
      if (e.target === this.elements.deleteButton) {
        this.initiateDeletion();
      }
    });
  }

  onCheckboxChange() {
    this.updateButtonState();
    this.clearValidationErrors();
  }

  clearValidationErrors() {
    [this.elements.confirmDelete, this.elements.confirmIrreversible].forEach((el) => {
      el.classList.remove("is-invalid");

      const errorElement = this.getErrorElement(el);
      if (errorElement) {
        errorElement.style.display = "none";
      }
    });
  }

  getErrorElement(inputElement) {
    // Look for error element
    const formCheck = inputElement.closest(".form-check");
    if (formCheck) {
      return formCheck.querySelector(".input-box__hint-text, .invalid-feedback");
    }

    // Check next sibling
    let sibling = inputElement.nextElementSibling;
    while (sibling) {
      if (
        sibling.classList.contains("input-box__hint-text") ||
        sibling.classList.contains("invalid-feedback")
      ) {
        return sibling;
      }
      sibling = sibling.nextElementSibling;
    }

    return null;
  }

  async handleSubmit(event) {
    event.preventDefault();
    await this.initiateDeletion();
  }

  async initiateDeletion() {
    if (this.formSubmitted) {
      logger.warn("Deletion already in progress");
      return;
    }

    try {
      // 1. Validate checkboxes
      if (!(await this.validateCheckboxes())) {
        return;
      }

      // 2. Show confirmation dialog
      if (!(await this.showConfirmationDialog())) {
        return;
      }

      // 3. Prompt for typed confirmation
      if (!(await this.promptConfirmationText())) {
        return;
      }

      // All checks passed - proceed with deletion
      this.setProcessingState(true);
      this.formSubmitted = true;

      await this.submitDeletion();
    } catch (error) {
      logger.error("Deletion process failed:", error);
      this.handleError(error.message || "Deletion failed");
    }
  }

  async validateCheckboxes() {
    const formData = {
      confirm_delete: this.elements.confirmDelete.checked,
      confirm_irreversible: this.elements.confirmIrreversible.checked,
    };

    this.validator.formData = formData;

    if (!this.validator.validateAll()) {
      const errors = this.validator.getErrors();
      this.displayValidationErrors(errors);
      return false;
    }

    return true;
  }

  displayValidationErrors(errors) {
    this.clearValidationErrors();

    Object.entries(errors).forEach(([fieldName, error]) => {
      const element = this.getElementByName(fieldName);
      if (element && error?.message) {
        this.showFieldError(element, error.message);
      }
    });

    // Scroll to first error
    const firstError = this.elements.form.querySelector(".is-invalid");
    if (firstError) {
      firstError.scrollIntoView({ behavior: "smooth", block: "center" });
    }
  }

  getElementByName(fieldName) {
    const fieldMap = {
      confirm_delete: this.elements.confirmDelete,
      confirm_irreversible: this.elements.confirmIrreversible,
    };

    return fieldMap[fieldName];
  }

  showFieldError(element, message) {
    element.classList.add("is-invalid");

    // Find or create error element
    let errorElement = this.getErrorElement(element);
    if (!errorElement) {
      errorElement = document.createElement("div");
      errorElement.className = "input-box__hint-text";
      element.parentNode.insertBefore(errorElement, element.nextSibling);
    }

    errorElement.textContent = message;
    errorElement.style.display = "block";
  }

  async showConfirmationDialog() {
    const message = `🚨 FINAL CONFIRMATION REQUIRED 🚨\n\nAre you absolutely sure you want to delete:\n"${this.options.productName}"?\n\nThis action is permanent and cannot be undone.\n\nType "DELETE" to confirm:`;

    return new Promise((resolve) => {
      const confirmed = window.confirm(message);
      resolve(confirmed);
    });
  }

  async promptConfirmationText() {
    return new Promise((resolve) => {
      const userInput = prompt('Type "DELETE" to confirm:');

      if (userInput === "DELETE") {
        resolve(true);
      } else {
        const errorMessage =
          userInput === null || userInput === ""
            ? "Confirmation cancelled"
            : `Confirmation text does not match. Expected: "DELETE"`;

        alert(errorMessage);
        resolve(false);
      }
    });
  }

  async submitDeletion() {
    try {
      const formData = new FormData(this.elements.form);

      const response = await fetch(this.elements.form.action, {
        method: "POST",
        body: formData,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
        },
      });

      const result = await response.json();

      if (response.ok && result.success) {
        this.handleSuccess(result);
      } else {
        this.handleError(result.message || "Deletion failed");
      }
    } catch (error) {
      this.handleNetworkError(error);
    }
  }

  handleSuccess(result) {
    logger.success("Product deleted successfully", result);

    // Show success message
    this.showNotification(result.message || "Product deleted successfully", "success");

    // Redirect if specified
    if (result.redirect) {
      setTimeout(() => {
        window.location.href = result.redirect;
      }, 2000);
    }
  }

  handleError(message) {
    this.setProcessingState(false);
    this.formSubmitted = false;

    logger.error("Deletion failed:", message);
    this.showNotification(message, "error");
  }

  handleNetworkError(error) {
    logger.error("Network error:", error);

    // Fallback to traditional form submission
    this.showNotification("Network issue detected. Trying alternative method...", "warning");

    setTimeout(() => {
      this.elements.form.submit();
    }, 1000);
  }

  setProcessingState(isProcessing) {
    const button = this.elements.deleteButton;

    if (isProcessing) {
      // Save original state
      if (!button.dataset.originalHtml) {
        button.dataset.originalHtml = button.innerHTML;
      }

      button.innerHTML = '<span class="spinner"></span> Deleting...';
      button.disabled = true;
      button.classList.add("processing");

      // Disable checkboxes
      this.elements.confirmDelete.disabled = true;
      this.elements.confirmIrreversible.disabled = true;
    } else {
      // Restore original state
      if (button.dataset.originalHtml) {
        button.innerHTML = button.dataset.originalHtml;
        delete button.dataset.originalHtml;
      }

      button.disabled = false;
      button.classList.remove("processing");

      // Re-enable checkboxes
      this.elements.confirmDelete.disabled = false;
      this.elements.confirmIrreversible.disabled = false;

      this.updateButtonState();
    }
  }

  setupSafetyFeatures() {
    window.addEventListener("beforeunload", (e) => {
      if (this.isFormPartiallyFilled() && !this.formSubmitted) {
        e.preventDefault();
        e.returnValue = "You have started the deletion process. Are you sure you want to leave?";
      }
    });
  }

  isFormPartiallyFilled() {
    return this.elements.confirmDelete.checked || this.elements.confirmIrreversible.checked;
  }

  showCriticalError(message) {
    const errorDiv = document.createElement("div");
    errorDiv.className = "critical-error";
    errorDiv.innerHTML = `
      <strong>Deletion System Error:</strong> ${message}
      <button onclick="location.reload()">Reload Page</button>
    `;

    this.elements.form.parentNode.insertBefore(errorDiv, this.elements.form);
  }

  showNotification(message, type = "info") {
    // Use your existing notification system
    const event = new CustomEvent("app:notification", {
      detail: { message, type },
    });
    document.dispatchEvent(event);
  }

  // ============ PUBLIC API ============

  reset() {
    this.elements.form.reset();
    this.clearValidationErrors();
    this.updateButtonState();
    this.setProcessingState(false);
    this.formSubmitted = false;
  }

  destroy() {
    window.removeEventListener("beforeunload", this.setupSafetyFeatures);
    this.isInitialized = false;
    logger.debug("ProductDeletionManager destroyed");
  }

  getStatus() {
    return {
      isInitialized: this.isInitialized,
      formSubmitted: this.formSubmitted,
      checkboxes: {
        confirmDelete: this.elements.confirmDelete?.checked,
        confirmIrreversible: this.elements.confirmIrreversible?.checked,
      },
    };
  }
}
