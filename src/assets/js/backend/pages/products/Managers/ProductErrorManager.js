import BrowserLogger from "js/utils/logger";

const logger = new BrowserLogger("ProductErrorManager");

export default class ProductErrorManager {
  constructor() {
    this.errors = new Map();
  }
  displayError(field, error) {
    logger.debug("=== displayError() called ===");
    logger.debug("Field:", field.name);
    logger.debug("Error:", error.message);

    if (!field || !error) return;

    const inputBoxContainer = field.closest(".input-box");
    logger.debug("Found container?", !!inputBoxContainer);
    logger.debug("Container before:", inputBoxContainer.className);

    if (!inputBoxContainer) {
      logger.warn("No .input-box container found for field", { field: field.name });
      return;
    }

    // Add the class
    inputBoxContainer.classList.add("has-error");
    logger.debug("Container after adding class:", inputBoxContainer.className);

    // Force a DOM reflow to ensure styles apply
    void inputBoxContainer.offsetHeight;

    // Create error element
    const errorElement = document.createElement("div");
    errorElement.className = error.classes.join(" ");
    errorElement.textContent = error.message;
    errorElement.id = `error-${field.name}-${Date.now()}`; // Add ID for debugging

    logger.debug("Error element created:", errorElement);

    // Remove existing error
    const existingError = inputBoxContainer.querySelector(".input-box__hint-text");
    if (existingError) {
      logger.debug("Removing existing error:", existingError);
      existingError.remove();
    }

    // Add to DOM
    inputBoxContainer.appendChild(errorElement);
    logger.debug("Error element appended to container");

    // Check immediately if it's visible
    setTimeout(() => {
      logger.debug("Visibility check (after 10ms):");
      logger.debug("Error element display:", getComputedStyle(errorElement).display);
      logger.debug("Error element opacity:", getComputedStyle(errorElement).opacity);
      logger.debug(
        "Error element parent has has-error?",
        errorElement.parentElement.classList.contains("has-error"),
      );
    }, 10);

    field.classList.add("is-invalid");

    this.errors.set(field, { container: inputBoxContainer, element: errorElement });

    logger.debug("=== displayError() finished ===");
  }
  displayError(field, error) {
    if (!field || !error) return;

    field.classList.add("is-invalid");

    const inputBoxContainer = field.closest(".input-box");

    if (!inputBoxContainer) {
      logger.warn("No .input-box container found for field", { field: field.name });
      return;
    }

    inputBoxContainer.classList.add("has-error");

    const errorElement = document.createElement("div");
    errorElement.className = error.classes.join(" ");
    errorElement.textContent = error.message;

    const existingError = inputBoxContainer.querySelector(".input-box__hint-text");
    if (existingError) {
      existingError.remove();
    }

    inputBoxContainer.appendChild(errorElement);

    // Store reference for easy cleanup
    this.errors.set(field, { container: inputBoxContainer, element: errorElement });

    logger.debug("Error displayed for field", { field: field.name });
  }

  clearError(field) {
    if (!field) return;

    field.classList.remove("is-invalid");

    const errorInfo = this.errors.get(field);
    if (errorInfo) {
      errorInfo.container.classList.remove("has-error");
      errorInfo.element.remove();
      this.errors.delete(field);
    }
  }

  clearAllErrors(form) {
    const fields = Array.from(form.querySelectorAll("input, select, textarea"));
    fields.forEach((field) => this.clearError(field));
  }
}
