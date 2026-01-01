import BrowserLogger from "js/utils/logger";
import ProductValidatorManager from "js/backend/pages/products/Managers/ProductValidatorManager.js";
import ProductComponentsManager from "js/backend/pages/products/Managers/ProductComponentsManager";
import ProductFormManager from "js/backend/pages/products/Managers/ProductFormManager.js";
import ProductErrorManager from "js/backend/pages/products/Managers/ProductErrorManager.js";

const logger = new BrowserLogger("ProductMain");

class ProductMain {
  constructor() {
    this._isInitialized = false;
    this.managers = {};

    this._init();
  }

  async _init() {
    if (this._isInitialized) {
      logger.warn("ProductMain already initialized, skipping");
      return;
    }
    this._isInitialized = true;

    logger.info("Initializing product page");

    try {
      this.managers.components = new ProductComponentsManager();
      this.managers.validator = new ProductValidatorManager();
      this.managers.errors = new ProductErrorManager();

      this.managers.form = new ProductFormManager(this.managers.validator, this.managers.errors);
      await this.managers.components.initialize();

      // Initialize validation
      await this.managers.validator.initialize();

      // Setup form handling
      this.managers.form.bindFormEvents();

      // Setup real-time validation
      this.managers.form.bindRealTimeValidation();

      logger.success("Product page initialized successfully");
    } catch (error) {
      logger.error("Failed to initialize product page", error);
    }
  }
}

// Initialize the application
new ProductMain();

// Development utilities
if (process.env.NODE_ENV === "development") {
  window.ProductApp = {
    reinit: () => new ProductMain(),
  };
}
