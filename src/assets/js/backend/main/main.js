import DashboardManager from "js/backend/shared/DashboardManager";
import ProductListCheckboxManager from "js/backend/shared/ProducListCheckboxManager";

import { BrowserLogger } from "js/utils/BrowserLogger";
const logger = new BrowserLogger("Main");

class Main {
  constructor() {
    logger.debug("🔄 Main constructor called");
    this._mainInitialized = false;
    this._init();
  }

  async _init() {
    if (this._mainInitialized) {
      logger.warn("Main already initialized, skipping");
      return;
    }
    this._mainInitialized = true;

    logger.info("Initializing core application");

    try {
      await this._initializeCoreComponents();
      logger.success("Core application initialized successfully");
    } catch (error) {
      logger.error("Failed to initialize core application", error);
    }
  }

  async _initializeCoreComponents() {
    logger.debug("Initializing core components");

    // Components needed on most pages
    new DashboardManager();

    // Conditionally initialize product list manager
    if (this._isProductListPage()) {
      new ProductListCheckboxManager();
    }
  }

  _isProductListPage() {
    return (
      window.location.pathname.includes("/admin/product-list") ||
      document.querySelector('[data-product-list="true"]')
    );
  }
}

// Keep your existing initialization pattern
let mainInstance = null;
let initializationStarted = false;

function initializeApplication() {
  if (mainInstance || initializationStarted) return;

  initializationStarted = true;
  logger.info("Starting application initialization");
  mainInstance = new Main();
}

// DOM ready handling
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initializeApplication);
} else {
  initializeApplication();
}

// Development exports
if (process.env.NODE_ENV === "development") {
  window.MainApp = {
    getInstance: () => mainInstance,
    getState: () => ({
      mainInitialized: mainInstance?._mainInitialized,
    }),
  };
}
