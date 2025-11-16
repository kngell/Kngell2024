import BrowserLogger from "js/utils/BrowserLogger.js";
import ProductListCheckboxManager from "js/backend/shared/ProducListCheckboxManager.js";

const logger = new BrowserLogger("ProductListMain");

export default class ProductListMain {
  constructor() {
    this._init();
  }

  async _init() {
    logger.info("Initializing product list page");

    // Product list specific components
    new ProductListCheckboxManager();
    logger.success("Product list page initialized");
  }
}

new ProductListMain();
