import BrowserLogger from "js/core/utils/logger";

const logger = new BrowserLogger("ErrorDisplayService");

export default class ErrorDisplayService {
  constructor() {
    this.errors = new Map();
  }

  findFieldContainer(field) {
    const inputField = field.closest(".input-field");
    if (inputField) {
      return {
        container: inputField,
        type: "input-field",
        fieldErrorClass: "is-error",
        containerErrorClass: "is-error"
      };
    }

    // Try input-box pattern (older)
    const inputBox = field.closest(".input-box");
    if (inputBox) {
      return {
        container: inputBox,
        type: "input-box",
        fieldErrorClass: "is-invalid",
        containerErrorClass: "has-error"
      };
    }

    // Fallback for unknown structures
    return {
      container: field.parentElement,
      type: "unknown",
      fieldErrorClass: "is-invalid",
      containerErrorClass: "has-error"
    };
  }

  displayError(field, error) {
    if (!field || !error) return;

    const { container, type, fieldErrorClass, containerErrorClass } =
      this.findFieldContainer(field);

    // Add error class to the field
    field.classList.add(fieldErrorClass);

    // Add error class to the container
    if (container) {
      container.classList.add(containerErrorClass);
    }

    // Ensure error message is displayed
    this.ensureErrorMessage(field, container, type, error);

    // Store for cleanup
    this.errors.set(field, {
      container,
      type,
      fieldErrorClass,
      containerErrorClass
    });

    logger.debug(`Error displayed for ${field.name} using ${type} pattern`);
  }

  /**
   * Ensure error message element exists and is populated
   */
  ensureErrorMessage(field, container, type, error) {
    if (type === "input-field") {
      // Get the footer
      const footer = container?.querySelector(".input-field__footer");

      if (footer) {
        // Find error element (already exists in your structure)
        let errorElement = footer.querySelector(".input-field__error");

        if (!errorElement) {
          // Create error element if it doesn't exist (fallback)
          errorElement = document.createElement("span");
          errorElement.className = "input-field__error";
          footer.insertBefore(errorElement, footer.firstChild);
          logger.debug(`Created error element for ${field.name}`);
        }

        // Set error message
        errorElement.textContent = error.message;

        // Make sure error is visible
        errorElement.style.display = "";

        // Add a data attribute to indicate this field has an error
        if (container) {
          container.setAttribute("data-has-error", "true");
        }
      } else {
        logger.warn(`No footer found for ${field.name}, error may not display properly`);
      }
    } else if (type === "input-box") {
      // input-box pattern: error lives inside container
      let existingError = container?.querySelector(".input-box__hint-text");
      if (existingError) {
        existingError.textContent = error.message;
      } else if (container) {
        const newError = document.createElement("div");
        newError.className = "input-box__hint-text";
        newError.textContent = error.message;
        container.appendChild(newError);
      }
    }
    // For unknown types, let SCSS handle it - no error element created
  }

  clearError(field) {
    if (!field) return;

    const errorInfo = this.errors.get(field);

    if (errorInfo) {
      // Remove error classes
      field.classList.remove(errorInfo.fieldErrorClass);

      if (errorInfo.container) {
        errorInfo.container.classList.remove(errorInfo.containerErrorClass);
        errorInfo.container.removeAttribute("data-has-error");
      }

      // Clear error message but keep structure
      if (errorInfo.type === "input-field") {
        const footer = errorInfo.container?.querySelector(".input-field__footer");
        const errorElement = footer?.querySelector(".input-field__error");
        if (errorElement) {
          errorElement.textContent = "";
          // Hide to prevent layout shifts but keep structure
          errorElement.style.display = "none";
        }
      } else if (errorInfo.type === "input-box") {
        const errorElement = errorInfo.container?.querySelector(".input-box__hint-text");
        if (errorElement) {
          errorElement.remove();
        }
      }

      this.errors.delete(field);
    } else {
      // Try to clean up based on detection
      this.cleanupByDetection(field);
    }
  }

  cleanupByDetection(field) {
    const { container, type, fieldErrorClass, containerErrorClass } =
      this.findFieldContainer(field);

    field.classList.remove(fieldErrorClass);

    if (container) {
      container.classList.remove(containerErrorClass);
      container.removeAttribute("data-has-error");

      if (type === "input-field") {
        const footer = container.querySelector(".input-field__footer");
        const errorElement = footer?.querySelector(".input-field__error");
        if (errorElement) {
          errorElement.textContent = "";
          errorElement.style.display = "none";
        }
      } else if (type === "input-box") {
        const errorElement = container.querySelector(".input-box__hint-text");
        if (errorElement) errorElement.remove();
      }
    }
  }

  clearAllErrors(form = null) {
    if (form) {
      // Clear input-field errors
      form.querySelectorAll(".input-field.is-error").forEach((container) => {
        container.classList.remove("is-error");
        container.removeAttribute("data-has-error");

        const field = container.querySelector("input, select, textarea");
        if (field) {
          field.classList.remove("is-error");
          const footer = container.querySelector(".input-field__footer");
          const errorEl = footer?.querySelector(".input-field__error");
          if (errorEl) {
            errorEl.textContent = "";
            errorEl.style.display = "none";
          }
        }
      });

      // Clear input-box errors
      form.querySelectorAll(".input-box.has-error").forEach((container) => {
        container.classList.remove("has-error");
        container.removeAttribute("data-has-error");

        const field = container.querySelector("input, select, textarea");
        if (field) {
          field.classList.remove("is-invalid");
          const errorEl = container.querySelector(".input-box__hint-text");
          if (errorEl) errorEl.remove();
        }
      });

      // Clear from internal map
      this.errors.forEach((_, field) => {
        if (form.contains(field)) {
          this.errors.delete(field);
        }
      });
    } else {
      this.errors.forEach((_, field) => this.clearError(field));
      this.errors.clear();
    }
  }

  hasErrors(form = null) {
    if (form) {
      return form.querySelectorAll(".input-field.is-error, .input-box.has-error").length > 0;
    }
    return this.errors.size > 0;
  }

  getErrors(form = null) {
    const errors = {};

    if (form) {
      // Get from input-field pattern
      form.querySelectorAll(".input-field.is-error").forEach((container) => {
        const field = container.querySelector("input, select, textarea");
        const footer = container.querySelector(".input-field__footer");
        const errorEl = footer?.querySelector(".input-field__error");
        if (field && errorEl?.textContent) {
          errors[field.name] = { message: errorEl.textContent };
        }
      });

      // Get from input-box pattern
      form.querySelectorAll(".input-box.has-error").forEach((container) => {
        const field = container.querySelector("input, select, textarea");
        const errorEl = container.querySelector(".input-box__hint-text");
        if (field && errorEl?.textContent) {
          errors[field.name] = { message: errorEl.textContent };
        }
      });
    }

    return errors;
  }

  scrollToFirstError(form = null) {
    const container = form || document;

    // Find first error container
    let firstError = container.querySelector(".input-field.is-error, .input-box.has-error");

    if (firstError) {
      firstError.scrollIntoView({
        behavior: "smooth",
        block: "center"
      });

      // Focus the field
      const field = firstError.querySelector("input, select, textarea");
      setTimeout(() => {
        if (field) {
          field.focus();
          // For select, also trigger a click to open dropdown? (optional)
          if (field.tagName === "SELECT") {
            // Optional: trigger focus without opening dropdown
            field.focus();
          }
        }
      }, 300);
    }

    return firstError;
  }

  /**
   * Check if a specific field has an error
   */
  hasFieldError(field) {
    if (!field) return false;
    return this.errors.has(field);
  }

  /**
   * Get error message for a specific field
   */
  getFieldError(field) {
    if (!field) return null;
    const errorInfo = this.errors.get(field);
    if (errorInfo && errorInfo.type === "input-field") {
      const footer = errorInfo.container?.querySelector(".input-field__footer");
      const errorEl = footer?.querySelector(".input-field__error");
      return errorEl?.textContent || null;
    }
    return null;
  }
}
