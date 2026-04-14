import BrowserLogger from "js/core/utils/BrowserLogger";
import ProductComponentsManager from "js/backend/pages/products-save/Managers/ProductComponentsManager";
import FormHandler from "js/core/forms/FormHandler";

const logger = new BrowserLogger("ProductMain");

export default class ProductSaveMain {
  constructor() {
    this.componentsManager = null;
    this.formHandlers = [];
    this.isInitialized = false;
    // REMOVED: this.notificationManager = null;
    this.init();
  }

  async init() {
    if (this.isInitialized) return;

    try {
      logger.info("Initializing product create/edit page");

      // NO NotificationManager here - FormHandler will handle it

      this.componentsManager = new ProductComponentsManager();
      await this.componentsManager.initialize();
      logger.debug("Components manager initialized");

      // Small delay to ensure DOM is fully ready
      setTimeout(() => {
        this.initializeFormHandlers();
      }, 100);
    } catch (error) {
      logger.error("Initialization failed:", error);
      this.showCriticalError(error.message);
    }
  }

  async initializeFormHandlers() {
    try {
      // Double-check that forms exist
      const forms = document.querySelectorAll('form[data-validate="true"]');

      logger.debug(`Found ${forms.length} form(s) with data-validate="true"`, {
        forms: Array.from(forms).map((f) => ({
          id: f.id,
          action: f.action,
          method: f.method,
          rules: f.dataset.validationRules
        }))
      });

      if (forms.length === 0) {
        logger.warn("No forms found with data-validate='true'");
        return;
      }

      // Create form handlers for each form - FormHandler will manage all notifications
      const handlerPromises = Array.from(forms).map(async (form) => {
        try {
          logger.debug(`Creating form handler for form: ${form.id || "unnamed"}`);

          const handler = new FormHandler(form, {
            rulesName: form.dataset.validationRules || "productRules",
            enableRealTime: true,
            submissionMode: "ajax",
            ajaxHandler: true,

            // Configure notification behavior for product forms
            // FormHandler will use this config internally
            notificationConfig: {
              error: {
                permanent: true, // Product errors are permanent
                duration: 8000,
                position: "top-right"
              },
              success: {
                permanent: false, // Success auto-closes
                duration: 3000,
                position: "top-right"
              },
              warning: {
                permanent: false,
                duration: 5000,
                position: "top-right"
              },
              info: {
                permanent: false,
                duration: 5000,
                position: "top-right"
              }
            },

            // Custom notification container for product forms
            notificationContainerId: "product-notifications",
            notificationPosition: "top-right",

            // Components manager for custom data processing
            componentsManager: this.componentsManager,

            // Redirect delays
            redirectDelays: {
              success: 1500,
              error: 1000,
              warning: 2500,
              info: 2000,
              danger: 1000
            },

            // Success callback - business logic ONLY (no notifications)
            onSuccess: (result, context, handler) => {
              logger.success("Product form submitted successfully", {
                result,
                context,
                formId: handler.form.id
              });

              // Business logic: handle redirects or post-submit actions
              if (result.redirect) {
                // Navigate after a short delay (FormHandler already showed success notification)
                setTimeout(() => {
                  window.location.href = result.redirect;
                }, 1500);
              } else if (result.operation === "insert") {
                // Business logic for new products
                logger.debug("New product created");
                // Example: reset form, show custom message, etc.
              }
            },

            // Error callback - business logic ONLY (no notifications)
            onError: (error, handler) => {
              logger.error("Product form submission failed:", {
                message: error.message,
                formId: handler.form.id,
                details: error.original
              });

              // Business logic only: analytics, error tracking, etc.
              // Example: Track error for analytics
              if (window.ga) {
                window.ga("send", "event", "Form", "Error", error.message);
              }

              // Example: Log to error tracking service
              if (window.Sentry) {
                window.Sentry.captureException(error.original || error);
              }
            },

            // Initialization callback - business logic only
            onInitialize: (handler) => {
              logger.debug(`Form handler initialized: ${handler.form.id}`);
            }
          });

          const initializedHandler = await handler.initialize();
          logger.success(`Form handler initialized for form: ${form.id || "unnamed"}`);

          return initializedHandler;
        } catch (error) {
          logger.error(`Failed to initialize handler for form:`, error);
          // Don't show notification here - FormHandler would show it if it was initialized
          // Just log and return null
          return null;
        }
      });

      const handlers = await Promise.all(handlerPromises);
      this.formHandlers = handlers.filter((h) => h !== null);

      if (this.formHandlers.length > 0) {
        this.isInitialized = true;
        logger.success(`Initialized ${this.formHandlers.length} form handler(s)`);

        // Log validation status (debug only)
        this.logValidationStatus();

        // No success notification here - that's FormHandler's job
        // Users will see a success message when they actually submit the form
      } else {
        logger.warn("No form handlers were successfully initialized");
        // Critical error - show using fallback (no FormHandler available)
        this.showCriticalError("Failed to initialize product forms. Please refresh the page.");
      }
    } catch (error) {
      logger.error("Failed to initialize form handlers:", error);
      this.showCriticalError("Failed to initialize product forms. Please refresh the page.");
    }
  }

  logValidationStatus() {
    const status = this.getValidationStatus();
    logger.debug("Current validation status:", status);
  }

  getFormHandler(form) {
    if (!form) return null;

    // Try to find by element reference
    let handler = this.formHandlers.find((handler) => handler.form === form);

    // If not found, try by ID
    if (!handler && form.id) {
      handler = this.formHandlers.find((handler) => handler.form.id === form.id);
    }

    return handler;
  }

  /**
   * Validate all forms on the page
   */
  async validateAllForms() {
    if (this.formHandlers.length === 0) {
      logger.warn("No form handlers available for validation");
      return [];
    }

    const results = await Promise.all(
      this.formHandlers.map(async (handler) => {
        try {
          const isValid = await handler.validateAll();
          return {
            formId: handler.form.id,
            isValid,
            handler
          };
        } catch (error) {
          logger.error(`Validation failed for form:`, error);
          return {
            formId: handler.form.id,
            isValid: false,
            error: error.message
          };
        }
      })
    );

    logger.debug("Validation results:", results);
    return results;
  }

  /**
   * Get validation status for debugging
   */
  getValidationStatus() {
    return {
      isInitialized: this.isInitialized,
      hasComponentsManager: !!this.componentsManager,
      formHandlersCount: this.formHandlers.length,
      formStatuses: this.formHandlers.map((handler) => ({
        ...handler.getStatus(),
        formId: handler.form.id,
        formAction: handler.form.action,
        hasValidator: !!handler.validator
      })),
      componentsStatus: this.componentsManager?.getStatus?.()
    };
  }

  /**
   * Show critical error message (only for initialization failures)
   * This is a fallback when FormHandler isn't available
   */
  showCriticalError(message) {
    // Simple DOM-based error for critical initialization failures
    const errorDiv = document.createElement("div");
    errorDiv.className = "critical-error";
    errorDiv.style.cssText = `
      background: #f8d7da;
      border: 1px solid #f5c6cb;
      color: #721c24;
      padding: 15px;
      margin: 20px;
      border-radius: 4px;
      font-size: 14px;
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 10000;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    errorDiv.innerHTML = `
      <strong>⚠️ Initialization Error:</strong> ${message}
      <br>
      <button onclick="location.reload()" style="
        background: #721c24;
        color: white;
        border: none;
        padding: 8px 16px;
        margin-top: 10px;
        border-radius: 3px;
        cursor: pointer;
        font-weight: 500;
      ">Reload Page</button>
    `;

    document.body.prepend(errorDiv);
  }

  /**
   * Manual form submission with custom options
   */
  async submitForm(formElement, options = {}) {
    const handler = this.getFormHandler(formElement);
    if (!handler) {
      // No handler available - can't show notification (FormHandler not available)
      logger.error("Form handler not found");
      return null;
    }

    try {
      const result = await handler.submit(options);
      return result;
    } catch (error) {
      // FormHandler already shows the error notification
      // Just log and re-throw
      logger.error("Form submission error:", error);
      throw error;
    }
  }

  /**
   * Clean up resources
   */
  destroy() {
    // Destroy form handlers
    this.formHandlers.forEach((handler) => {
      try {
        handler.destroy();
        logger.debug(`Destroyed handler for form: ${handler.form.id}`);
      } catch (error) {
        logger.warn("Error destroying form handler:", error);
      }
    });
    this.formHandlers = [];

    // Destroy components manager
    if (this.componentsManager) {
      try {
        this.componentsManager.destroy();
      } catch (error) {
        logger.warn("Error destroying components manager:", error);
      }
      this.componentsManager = null;
    }

    this.isInitialized = false;
    logger.debug("ProductMain destroyed");
  }
}

// Wait for DOM to be fully loaded before initializing
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    const isProductFormPage = document.querySelector('form[data-validate="true"]') !== null;

    if (isProductFormPage) {
      logger.debug("DOM loaded, initializing ProductSaveMain");
      window.productMain = new ProductSaveMain();
    }
  });
} else {
  // DOM is already loaded
  const isProductFormPage = document.querySelector('form[data-validate="true"]') !== null;

  if (isProductFormPage) {
    logger.debug("DOM already loaded, initializing ProductSaveMain");
    window.productMain = new ProductSaveMain();
  }
}
