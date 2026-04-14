import BrowserLogger from "js/core/utils/BrowserLogger";
import ProductListCheckboxManager from "js/backend/shared/ProducListCheckboxManager";
import ProductDeletionModal from "js/backend/pages/product-list/Modals/ProductDeletionModal";
import ProductDeletionHelper from "js/core/helpers/ProductDeletionHelper";

const logger = new BrowserLogger("ProductListMain");

export default class ProductListMain {
  constructor() {
    this.components = [];
    this.deletionHelper = null;
    this._init();
  }

  async _init() {
    logger.info("Initializing product list page");

    // No NotificationManager here - FormHandler will handle it
    // Just create helpers that might be needed
    this.deletionHelper = new ProductDeletionHelper({
      tableOptions: {
        tableSelector: "table",
        rowSelector: "tr",
        productIdAttribute: "data-product-id"
      },
      enableEmptyState: false
    });

    setTimeout(() => {
      if (this.deletionHelper) {
        this.deletionHelper.forceRemoveEmptyState();
        const productCount = this.deletionHelper.countProductRows();
        if (productCount === 0) {
          logger.debug("No products found on page load, showing empty state");
          this.deletionHelper.showEmptyState();
        }
        logger.debug(`Initial product count: ${productCount}`);
      }
    }, 100);

    // Pass deletion helper only - no notification helpers
    this.components.push(
      new ProductListCheckboxManager(),
      new ProductDeletionModal(this.deletionHelper)
    );

    this.makeHelpersGloballyAvailable();
    logger.success("Product list page initialized");
  }

  makeHelpersGloballyAvailable() {
    window.productListHelpers = {
      deletion: this.deletionHelper
    };
  }

  getDeletionHelper() {
    return this.deletionHelper;
  }

  destroy() {
    this.components.forEach((component) => {
      if (component && typeof component.destroy === "function") {
        component.destroy();
      }
    });

    if (this.deletionHelper) {
      this.deletionHelper.destroy();
    }

    this.components = [];
    logger.debug("ProductListMain destroyed");
  }
}

document.addEventListener("DOMContentLoaded", () => {
  window.ProductListMain = new ProductListMain();
});
