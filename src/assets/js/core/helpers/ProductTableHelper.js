// js/backend/helpers/ProductTableHelper.js
import BrowserLogger from "js/core/utils/BrowserLogger";

export default class ProductTableHelper {
  constructor(options = {}) {
    this.logger = new BrowserLogger("ProductTableHelper");

    // Table configuration
    this.tableSelector = options.tableSelector || "table";
    this.rowSelector = options.rowSelector || "tr[data-product-id]";
    this.productIdAttribute = options.productIdAttribute || "data-product-id";
    this.emptyStateCallback = options.onEmptyState || null;

    this.logger.debug("ProductTableHelper initialized with:", {
      tableSelector: this.tableSelector,
      rowSelector: this.rowSelector,
      productIdAttribute: this.productIdAttribute,
    });
  }

  /**
   * Get table body element
   */
  getTableBody() {
    const table = document.querySelector(this.tableSelector);
    if (!table) {
      this.logger.warn(`Table not found with selector: ${this.tableSelector}`);
      return null;
    }
    return table.querySelector("tbody") || table;
  }

  /**
   * Get all product rows
   */
  getProductRows() {
    const tbody = this.getTableBody();
    return tbody ? Array.from(tbody.querySelectorAll(this.rowSelector)) : [];
  }

  /**
   * Get current product count
   */
  getProductCount() {
    return this.getProductRows().length;
  }

  /**
   * Find product row by ID
   */
  findProductRow(productId) {
    if (!productId) return null;

    // Try direct selector with quoted UUID
    let row = document.querySelector(`tr[${this.productIdAttribute}="${productId}"]`);

    // Try without quotes
    if (!row) {
      row = document.querySelector(`tr[${this.productIdAttribute}=${productId}]`);
    }

    // Try via form with public_id
    if (!row) {
      const form = document
        .querySelector(`form input[name="public_id"][value="${productId}"]`)
        ?.closest("form");
      if (form) {
        row = form.closest("tr");
      }
    }

    // Try via delete button
    if (!row) {
      const deleteBtn = document.querySelector(
        `[data-action="open-delete-modal"][data-product-id="${productId}"]`,
      );
      if (deleteBtn) {
        row = deleteBtn.closest("tr");
      }
    }

    return row;
  }

  /**
   * Highlight a row
   */
  highlightRow(row, color = "#fff3cd", duration = 300) {
    if (!row) return;

    const originalBg = row.style.backgroundColor;
    row.style.transition = `background-color ${duration}ms ease`;
    row.style.backgroundColor = color;

    setTimeout(() => {
      row.style.backgroundColor = originalBg;
    }, duration);
  }

  /**
   * Get table colspan based on header row
   */
  getTableColspan() {
    const table = document.querySelector(this.tableSelector);
    if (!table) return 8;

    const headerRow = table.querySelector("thead tr");
    if (headerRow) {
      return headerRow.children.length;
    }

    // Fallback: count columns in first row
    const firstRow = table.querySelector("tr");
    return firstRow ? firstRow.children.length : 8;
  }

  /**
   * Check if table is empty (no product rows)
   */
  isEmpty() {
    return this.getProductCount() === 0;
  }

  /**
   * Get all product IDs from the table
   */
  getAllProductIds() {
    const rows = this.getProductRows();
    return rows
      .map((row) => {
        // Try data attribute first
        let id = row.getAttribute(this.productIdAttribute);

        // Try to find public_id in form
        if (!id) {
          const form = row.querySelector("form");
          if (form) {
            id = form.querySelector('input[name="public_id"]')?.value;
          }
        }

        return id;
      })
      .filter((id) => id); // Remove null/undefined
  }

  /**
   * Sort table rows by a specific column
   */
  sortRows(columnIndex, direction = "asc") {
    const tbody = this.getTableBody();
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll("tr"));
    const productRows = rows.filter((row) => row.matches(this.rowSelector));
    const nonProductRows = rows.filter((row) => !row.matches(this.rowSelector));

    const sortedProductRows = productRows.sort((a, b) => {
      const aCell = a.children[columnIndex]?.textContent?.trim() || "";
      const bCell = b.children[columnIndex]?.textContent?.trim() || "";

      if (direction === "asc") {
        return aCell.localeCompare(bCell);
      } else {
        return bCell.localeCompare(aCell);
      }
    });

    // Clear and reappend rows
    tbody.innerHTML = "";
    [...nonProductRows, ...sortedProductRows].forEach((row) => tbody.appendChild(row));

    this.logger.debug(`Sorted table by column ${columnIndex} (${direction})`);
  }

  /**
   * Filter rows by search term
   */
  filterRows(searchTerm, columns = [0, 1, 2]) {
    const tbody = this.getTableBody();
    if (!tbody) return;

    const rows = tbody.querySelectorAll("tr");
    const searchLower = searchTerm.toLowerCase();

    rows.forEach((row) => {
      if (row.matches(this.rowSelector)) {
        let matches = false;

        for (const colIndex of columns) {
          const cellText = row.children[colIndex]?.textContent?.toLowerCase() || "";
          if (cellText.includes(searchLower)) {
            matches = true;
            break;
          }
        }

        row.style.display = matches ? "" : "none";
      }
    });

    this.logger.debug(`Filtered table with term: "${searchTerm}"`);
  }

  /**
   * Clear all filters
   */
  clearFilters() {
    const tbody = this.getTableBody();
    if (!tbody) return;

    const rows = tbody.querySelectorAll("tr");
    rows.forEach((row) => {
      row.style.display = "";
    });

    this.logger.debug("Cleared all filters");
  }

  /**
   * Export table data as CSV
   */
  exportToCSV(filename = "products.csv") {
    const table = document.querySelector(this.tableSelector);
    if (!table) return;

    const rows = table.querySelectorAll("tr");
    const csvData = [];

    // Get headers
    const headers = [];
    const headerRow = table.querySelector("thead tr");
    if (headerRow) {
      headerRow.querySelectorAll("th").forEach((th) => {
        headers.push(th.textContent?.trim() || "");
      });
    }
    csvData.push(headers.join(","));

    // Get product rows data
    const productRows = table.querySelectorAll(this.rowSelector);
    productRows.forEach((row) => {
      const rowData = [];
      row.querySelectorAll("td").forEach((td) => {
        // Clean the text: remove commas, newlines, etc.
        let text = td.textContent?.trim() || "";
        text = text.replace(/,/g, ";"); // Replace commas with semicolons
        text = text.replace(/\n/g, " "); // Replace newlines with spaces
        rowData.push(`"${text}"`); // Wrap in quotes
      });
      csvData.push(rowData.join(","));
    });

    // Download CSV
    const csvString = csvData.join("\n");
    const blob = new Blob([csvString], { type: "text/csv" });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);

    this.logger.debug(`Exported ${productRows.length} products to ${filename}`);
  }

  /**
   * Refresh table data (to be implemented with your data service)
   */
  async refresh() {
    this.logger.debug("Refreshing table data...");
    // Implement your refresh logic here
    // This could be an AJAX call to reload the table content
  }

  /**
   * Debug method to log current table state
   */
  debug() {
    const table = document.querySelector(this.tableSelector);
    if (!table) {
      this.logger.warn(`Table not found with selector: ${this.tableSelector}`);
      return;
    }

    const tbody = table.querySelector("tbody") || table;
    const allRows = tbody.querySelectorAll("tr");
    const productRows = tbody.querySelectorAll(this.rowSelector);
    const emptyRows = tbody.querySelectorAll(".empty-state-row");

    this.logger.debug("=== TABLE STATE DEBUG ===");
    this.logger.debug(`Table selector: ${this.tableSelector}`);
    this.logger.debug(`Table ID: ${table.id || "none"}`);
    this.logger.debug(`Table classes: ${table.className || "none"}`);
    this.logger.debug(`Total rows: ${allRows.length}`);
    this.logger.debug(`Product rows (${this.rowSelector}): ${productRows.length}`);
    this.logger.debug(`Empty state rows: ${emptyRows.length}`);

    productRows.forEach((row, index) => {
      let productId = row.getAttribute(this.productIdAttribute);

      // Try to find ID in form if not in data attribute
      if (!productId) {
        const form = row.querySelector("form");
        if (form) {
          productId = form.querySelector('input[name="public_id"]')?.value;
        }
      }

      this.logger.debug(`  Product row ${index + 1}:`, {
        element: row,
        productId: productId || "unknown",
        html: row.outerHTML.substring(0, 100) + "...",
      });
    });

    this.logger.debug("===========================");

    return {
      totalRows: allRows.length,
      productRows: productRows.length,
      emptyRows: emptyRows.length,
      productIds: this.getAllProductIds(),
    };
  }

  /**
   * Destroy instance
   */
  destroy() {
    this.logger.debug("ProductTableHelper destroyed");
  }
}
