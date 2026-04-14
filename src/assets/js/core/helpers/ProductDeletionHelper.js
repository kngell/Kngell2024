import BrowserLogger from "js/core/utils/BrowserLogger";

export default class ProductDeletionHelper {
  constructor(options = {}) {
    this.logger = new BrowserLogger("ProductDeletionHelper");
    this.notificationHelper = options.notificationHelper || null;

    // Table configuration
    this.tableSelector = options.tableOptions?.tableSelector || "table";
    this.rowSelector = options.tableOptions?.rowSelector || "tr[data-product-id]";
    this.productIdAttribute = options.tableOptions?.productIdAttribute || "data-product-id";

    this.logger.debug("ProductDeletionHelper initialized");
  }

  removeProductRow(productId) {
    if (!productId) return false;

    this.logger.debug(`Attempting to remove product row with ID: ${productId}`);

    const productRow = this.findProductRow(productId);

    if (productRow) {
      // FIRST: Remove any existing empty state rows BEFORE deleting
      this.removeEmptyState();

      // Visual feedback
      productRow.style.transition = "background-color 0.3s ease, opacity 0.3s ease";
      productRow.style.backgroundColor = "#fee2e2";
      productRow.style.opacity = "0.6";

      setTimeout(() => {
        productRow.remove();
        this.logger.success(`Removed product row with ID: ${productId}`);

        // Check if table is empty by counting remaining rows
        const remainingProducts = this.countProductRows();
        this.logger.debug(`Remaining products: ${remainingProducts}`);

        // Only show empty state if there are truly no products left
        if (remainingProducts === 0) {
          this.logger.debug("No products left, showing empty state");
          this.showEmptyState();
        }

        // Show success notification
        if (this.notificationHelper) {
          this.notificationHelper.success("Product deleted successfully", { duration: 5000 });
        }
      }, 300);

      return true;
    }

    this.logger.warn(`Product row with ID ${productId} not found`);
    return false;
  }

  countProductRows() {
    const table = document.querySelector(this.tableSelector);
    if (!table) return 0;

    const tbody = table.querySelector("tbody") || table;

    // Count rows that contain a form with a public_id input
    const rows = Array.from(tbody.querySelectorAll("tr")).filter((row) => {
      return row.querySelector('form input[name="public_id"]') !== null;
    });

    return rows.length;
  }

  findProductRow(productId) {
    if (!productId) return null;

    // Find the form with this public_id, then get its parent row
    const form = document
      .querySelector(`form input[name="public_id"][value="${productId}"]`)
      ?.closest("form");
    return form?.closest("tr") || null;
  }

  /**
   * Get table body
   */
  getTableBody() {
    const table = document.querySelector(this.tableSelector);
    return table ? table.querySelector("tbody") || table : null;
  }

  showEmptyState() {
    this.logger.debug("Showing empty state");

    const tbody = this.getTableBody();
    if (!tbody) return;

    // ALWAYS remove any existing empty state first
    this.removeEmptyState();

    const colspan = this.getTableColspan();
    const emptyRow = document.createElement("tr");
    emptyRow.className = "empty-state-row";
    emptyRow.innerHTML = `
    <td colspan="${colspan}" class="text-center py-8">
      <div class="empty-state">
        <svg class="empty-state-icon" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
        <h3 class="empty-state-title">No products found</h3>
        <p class="empty-state-text">Get started by adding your first product</p>
        <a href="/products/create" class="btn btn-primary">Add Product</a>
      </div>
    </td>
  `;

    tbody.appendChild(emptyRow);
    this.logger.debug("Empty state added to table");
  }
  forceRemoveEmptyState() {
    this.logger.debug("Force removing all empty states");
    this.removeEmptyState();
  }
  removeEmptyState() {
    this.logger.debug("Removing empty state rows");

    // Remove from all tables, not just the main one
    const allTables = document.querySelectorAll("table");
    allTables.forEach((table) => {
      const tbody = table.querySelector("tbody") || table;
      const emptyRows = tbody.querySelectorAll(".empty-state-row");
      emptyRows.forEach((row) => row.remove());
      if (emptyRows.length > 0) {
        this.logger.debug(`Removed ${emptyRows.length} empty state row(s) from table`);
      }
    });

    const emptyStates = document.querySelectorAll(".empty-state");
    emptyStates.forEach((el) => {
      if (!el.closest("tr")) {
        // Don't remove if it's inside a row we just removed
        el.remove();
        this.logger.debug("Removed standalone empty state element");
      }
    });
  }

  /**
   * Get table colspan
   */
  getTableColspan() {
    const table = document.querySelector(this.tableSelector);
    if (!table) return 8;

    const headerRow = table.querySelector("thead tr");
    return headerRow ? headerRow.children.length : 8;
  }

  destroy() {
    this.logger.debug("ProductDeletionHelper destroyed");
  }
}
