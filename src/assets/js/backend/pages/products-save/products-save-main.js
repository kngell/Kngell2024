import BaseFormManager from "js/components/Managers/BaseFormManager";
import ProductComponentsManager from "js/backend/pages/products-save/Managers/ProductComponentsManager";
import VariationHandler from "js/backend/pages/products-save/Handlers/VariationHandler";
import CardActionHandler from "js/components/cardAction/CardActionHandler ";
import BrowserLogger from "js/core/utils/BrowserLogger";

class ProductSaveMain extends BaseFormManager {
  constructor(options = {}) {
    super({
      enableDropzone: true,
      enableCustomSelect: true,
      enableRadioOptions: true,
      enableToggleSwitch: true,
      enableActionBar: true,
      resetOnSuccess: options.resetOnSuccess || true,
      notificationContainerId: options.notificationContainerId || "product-notifications",

      channelStrategy: "flash", // 'flash', 'notification', or 'auto'

      // Flash channel configuration
      flashSelector: options.flashSelector || ".product__body",
      flashConfig: {
        durations: {
          success: 3000,
          error: 0, // Product errors persist
          warning: 5000,
          info: 4000
        },
        containerClass: "flash-container"
      },

      redirectConfig: {
        insert: true, // Redirect after insert
        update: false, // NO redirect after update
        delete: true, // Redirect after delete
        delay: 1500
      },

      notificationConfig: {
        error: {
          permanent: true,
          duration: 8000,
          position: "top-right"
        },
        success: {
          permanent: false,
          duration: 3000,
          position: "top-right"
        },
        warning: {
          permanent: false,
          duration: 5000,
          position: "top-right"
        }
      },

      ...options
    });

    // Product-specific components manager
    this.componentsManager = null;
  }

  // ProductSaveMain.js - Add init logging to verify

  // Optional: Override for dynamic strategy based on form state
  getChannelStrategy() {
    // You could add logic here to decide strategy dynamically
    // For example, check if user prefers flash messages
    return this.options.channelStrategy;
  }
  getFlashSelector() {
    return this.getFormSelector();
  }
  getDefaultNotificationContainerId() {
    return "product-notifications";
  }

  getFormSelector() {
    return 'form[data-validate="true"][data-validation-rules*="product"], form#product-form';
  }

  getValidationRules() {
    return "productRules";
  }

  // ─── ProductComponentsManager Integration ───────────────────

  /**
   * Initialize ProductComponentsManager as part of specific components
   */
  async initSpecificComponents() {
    try {
      this.logger.debug("Initializing ProductComponentsManager...");
      this.componentsManager = new ProductComponentsManager();
      await this.componentsManager.initialize();
      this.logger.debug("ProductComponentsManager initialized successfully");
      new CardActionHandler();
      this.logger.debug("CardActionHandler initialized successfully");

      new VariationHandler();
      this.logger.debug("VariationHandler initialized successfully");
    } catch (error) {
      this.logger.error("Failed to initialize ProductComponentsManager:", error);
    }
  }

  getCustomDataProcessors() {
    return [
      // Add ProductComponentsManager data to form submission
      (data, formEl) => {
        if (this.componentsManager && this.componentsManager.getData) {
          const componentsData = this.componentsManager.getData?.();
          if (componentsData) {
            Object.assign(data, componentsData);
            this.logger.debug("Added ProductComponentsManager data to form:", componentsData);
          }
        }
        return data;
      }
    ];
  }

  // ─── Override Deletion Modal Config ─────────────────────────

  getDeletionModalConfig() {
    return {
      onEntityDeleted: (entityId, result) => {
        this.logger.success("Product deleted:", entityId);
      },
      notificationConfig: {
        error: { permanent: true, duration: 8000 },
        success: { permanent: false, duration: 3000 }
      }
    };
  }

  // ─── Override Action Bar Config ────────────────────────────

  getActionBarConfig() {
    return {
      addButtonSelector: ".btn-add-product",
      deleteButtonSelector: ".btn-delete-product"
    };
  }

  // ─── Entity Lifecycle Hooks ────────────────────────────────

  onEntityDeleted(entityId, result) {
    this.logger.success("Product deleted:", entityId);
    // Additional cleanup if needed
  }

  onBeforeDelete(context) {
    this.logger.debug("Before deleting product:", context);
    return true;
  }

  onBeforeAdd(context) {
    this.logger.debug("Before adding new product:", context);
    return true;
  }

  // ─── Form Success Handler ──────────────────────────────────

  // ProductSaveMain.js - Clean version
  onSuccess(result, context) {
    this.logger.success("Product form submitted successfully");

    // Only handle resets for new products
    if (this.options.resetOnSuccess && result.operation === "insert") {
      this.formHandler?.form?.reset();

      if (this.componentsManager?.reset) {
        this.componentsManager.reset();
        this.logger.debug("ProductComponentsManager reset");
      }

      if (this.options.resetCustomSelectsOnSuccess) {
        this.resetAllCustomSelects();
      }

      this.dropzoneInstances.forEach((dz) => dz.reset?.());
    }
  }

  onError(error) {
    const errorKey = error.message || JSON.stringify(error);
    const now = Date.now();

    if (this._lastErrorLogKey === errorKey && now - this._lastErrorLogTime < 1000) {
      this.logger.debug("Suppressing duplicate error log");
      return;
    }

    this._lastErrorLogKey = errorKey;
    this._lastErrorLogTime = now;

    this.logger.error("Product form submission failed:", error);
  }

  // ─── Public API Extensions ─────────────────────────────────

  getComponentsManager() {
    return this.componentsManager;
  }

  getValidationStatus() {
    const baseStatus = {
      isInitialized: !!this.formHandler,
      hasComponentsManager: !!this.componentsManager,
      componentsStatus: this.componentsManager?.getStatus?.() || null
    };

    if (this.formHandler) {
      return {
        ...baseStatus,
        ...this.formHandler.getStatus?.()
      };
    }

    return baseStatus;
  }

  // ─── Destroy Override ──────────────────────────────────────

  destroy() {
    // Destroy ProductComponentsManager
    if (this.componentsManager) {
      try {
        this.componentsManager.destroy();
        this.logger.debug("ProductComponentsManager destroyed");
      } catch (error) {
        this.logger.warn("Error destroying ProductComponentsManager:", error);
      }
      this.componentsManager = null;
    }

    // Call parent destroy
    super.destroy();
  }
}

const initProductSaveMain = () => {
  if (!window.productMainInstance) {
    window.productMainInstance = new ProductSaveMain();
    window.productMainInstance._init();
  }
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initProductSaveMain);
} else {
  initProductSaveMain();
}

export default ProductSaveMain;
