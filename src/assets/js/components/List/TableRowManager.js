// js/components/List/TableRowManager.js
import BrowserLogger from "js/core/utils/BrowserLogger";

export default class TableRowManager {
  constructor(options = {}) {
    this.logger = new BrowserLogger("TableRowManager");
    this.notificationHelper = options.notificationHelper || null;

    this.options = {
      tableSelector: "table",
      rowSelector: ".table__body--row",
      tbodySelector: "tbody",
      itemIdAttribute: "data-id",
      entityName: "item",
      entityDisplayName: null,
      onRowRemoved: null,
      onEmptyStateShow: null,
      emptyStateConfig: {
        icon: null,
        title: null,
        message: null,
        actionUrl: "#",
        actionText: null,
        ...options.emptyStateConfig
      },
      ...options
    };

    this.setDefaultStrings();
  }

  setDefaultStrings() {
    const displayName =
      this.options.entityDisplayName ||
      this.options.entityName.charAt(0).toUpperCase() + this.options.entityName.slice(1);

    if (!this.options.emptyStateConfig.title) {
      this.options.emptyStateConfig.title = `No ${this.options.entityName}s found`;
    }
    if (!this.options.emptyStateConfig.message) {
      this.options.emptyStateConfig.message = `Get started by adding your first ${this.options.entityName}`;
    }
    if (!this.options.emptyStateConfig.actionText) {
      this.options.emptyStateConfig.actionText = `Add ${displayName}`;
    }
  }

  removeRowFromDOM(itemId) {
    if (!itemId) return false;

    const escapedId = this.escapeCssSelector(itemId);
    const selector = `${this.options.rowSelector}[${this.options.itemIdAttribute}="${escapedId}"]`;
    const row = document.querySelector(selector);

    if (row) {
      row.style.transition = "background-color 0.3s ease, opacity 0.3s ease";
      row.style.backgroundColor = "#fee2e2";
      row.style.opacity = "0.6";

      setTimeout(() => {
        row.remove();
        this.logger.success(`Removed row with ID: ${itemId}`);

        const remainingItems = this.countRows();

        if (remainingItems === 0) {
          this.showEmptyState();
        }

        if (this.options.onRowRemoved) {
          this.options.onRowRemoved(itemId, remainingItems);
        }
      }, 300);

      return true;
    }

    this.logger.warn(`Row with ID ${itemId} not found`);
    return false;
  }

  escapeCssSelector(str) {
    return String(str).replace(/([!"#$%&'()*+,./:;<=>?@[\\\]^`{|}~])/g, "\\$1");
  }

  countRows() {
    const table = document.querySelector(this.options.tableSelector);
    if (!table) return 0;

    const tbody = table.querySelector(this.options.tbodySelector) || table;
    const rows = Array.from(tbody.querySelectorAll(this.options.rowSelector)).filter((row) => {
      return row.hasAttribute(this.options.itemIdAttribute);
    });

    return rows.length;
  }

  findRow(itemId) {
    if (!itemId) return null;
    const escapedId = this.escapeCssSelector(itemId);
    const selector = `${this.options.rowSelector}[${this.options.itemIdAttribute}="${escapedId}"]`;
    return document.querySelector(selector);
  }

  getTableBody() {
    const table = document.querySelector(this.options.tableSelector);
    return table ? table.querySelector(this.options.tbodySelector) || table : null;
  }

  showEmptyState() {
    const tbody = this.getTableBody();
    if (!tbody) return;

    this.removeEmptyState();

    const colspan = this.getTableColspan();
    const emptyRow = document.createElement("tr");
    emptyRow.className = "empty-state-row";

    const iconSvg =
      this.options.emptyStateConfig.icon ||
      `
      <svg class="empty-state-icon" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
      </svg>
    `;

    emptyRow.innerHTML = `
      <td colspan="${colspan}" class="text-center py-8">
        <div class="empty-state">
          ${iconSvg}
          <h3 class="empty-state-title">${this.options.emptyStateConfig.title}</h3>
          <p class="empty-state-text">${this.options.emptyStateConfig.message}</p>
          <a href="${this.options.emptyStateConfig.actionUrl}" class="btn btn-primary">${this.options.emptyStateConfig.actionText}</a>
        </div>
      </td>
    `;

    tbody.appendChild(emptyRow);

    if (this.options.onEmptyStateShow) {
      this.options.onEmptyStateShow();
    }
  }

  removeEmptyState() {
    const allTables = document.querySelectorAll(this.options.tableSelector);
    allTables.forEach((table) => {
      const tbody = table.querySelector(this.options.tbodySelector) || table;
      const emptyRows = tbody.querySelectorAll(".empty-state-row");
      emptyRows.forEach((row) => row.remove());
    });
  }

  getTableColspan() {
    const table = document.querySelector(this.options.tableSelector);
    if (!table) return 8;

    const headerRow = table.querySelector("thead tr");
    return headerRow ? headerRow.children.length : 8;
  }

  refresh() {
    this.logger.debug(`${this.options.entityName} row manager refreshed`);
  }

  destroy() {
    this.logger.debug(`${this.options.entityName} row manager destroyed`);
  }
}
