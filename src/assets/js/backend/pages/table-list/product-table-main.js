import TableManager from "js/components/List/TableManager";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("ProductTableMain");

class ProductTableMain {
  constructor() {
    this.config = {
      tableType: "products",
      entityName: "product",
      entityDisplayName: "Product",
      entityNamePlural: "products",
      idAttribute: "data-product-id",
      checkboxName: "products[]",
      emptyStateConfig: {
        title: "No products found",
        message: "Get started by adding your first product",
        actionUrl: "/products/create",
        actionText: "Add Product"
      }
    };
  }

  init() {
    const table = document.querySelector('.table[data-table-type="products"]');
    if (table && !window.__productTableManager) {
      logger.debug("Initializing ProductTableMain");
      window.__productTableManager = new TableManager(this.config);
    }
  }
}

// Auto-initialize only on product page
if (document.querySelector('.table[data-table-type="products"]')) {
  const productTable = new ProductTableMain();
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => productTable.init());
  } else {
    productTable.init();
  }
}
