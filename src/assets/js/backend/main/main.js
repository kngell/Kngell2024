import DashboardManager from "js/backend/shared/DashboardManager";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("Main");

class Main {
  constructor() {
    logger.debug("🔄 Main constructor called");
    this._mainInitialized = false;
    this._components = {
      dashboardManager: null
    };
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

    try {
      this._components.dashboardManager = await DashboardManager.create({ debug: true });
      logger.debug("DashboardManager initialized");
    } catch (error) {
      logger.error("Failed to initialize DashboardManager", error);
    }
  }
}

let mainInstance = null;
let initializationStarted = false;

function initializeApplication() {
  if (mainInstance || initializationStarted) return;

  initializationStarted = true;
  logger.info("Starting application initialization");
  mainInstance = new Main();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initializeApplication);
} else {
  initializeApplication();
}

if (process.env.NODE_ENV === "development") {
  window.MainApp = {
    getInstance: () => mainInstance,
    getState: () => ({
      mainInitialized: mainInstance?._mainInitialized
    }),
    refreshDashboard: async () => {
      if (mainInstance?._components?.dashboardManager) {
        return mainInstance._components.dashboardManager.refreshRoutePatterns();
      }
      throw new Error("DashboardManager not available");
    }
  };
}
