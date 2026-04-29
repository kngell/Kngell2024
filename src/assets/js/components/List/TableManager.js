import TableCheckboxManager from "./TableCheckboxManager";
import TableRowManager from "./TableRowManager";
import DeletionModal from "js/components/Modals/DeletionModal.js";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("TableManager");

export default class TableManager {
  constructor(entityConfig, options = {}) {
    this.entityConfig = {
      // Required
      tableType: "products",
      entityName: "product",
      entityDisplayName: "Product",
      entityNamePlural: "products",
      idAttribute: "data-product-id",
      checkboxName: "products[]",

      // Optional with defaults
      tableSelector: `.table[data-table-type="${entityConfig?.tableType || "products"}"]`,
      bulkDeleteBtn: `#bulk-delete-${entityConfig?.tableType || "products"}`,
      rowSelector: ".table__body--row",
      emptyStateConfig: {
        title: `No ${entityConfig?.entityDisplayName?.toLowerCase() || "items"} found`,
        message: `Get started by adding your first ${entityConfig?.entityDisplayName?.toLowerCase() || "item"}`,
        actionUrl: `/${entityConfig?.tableType || "items"}/create`,
        actionText: `Add ${entityConfig?.entityDisplayName || "Item"}`
      },
      ...entityConfig,
      ...options
    };

    this.checkboxManager = null;
    this.rowManager = null;
    this.deletionModal = null;
    this.init();
  }

  init() {
    logger.debug(`Initializing ${this.entityConfig.entityName} table manager`);

    const table = document.querySelector(this.entityConfig.tableSelector);
    if (!table) {
      logger.warn(`${this.entityConfig.entityName} table not found`);
      return;
    }

    // 1. Checkbox Manager
    this.checkboxManager = new TableCheckboxManager({
      tableSelector: this.entityConfig.tableSelector,
      selectAllSelector: "#select-all-select",
      itemSelector: `input[name="${this.entityConfig.checkboxName}"]`,
      rowSelector: this.entityConfig.rowSelector,
      selectedClass: "row--selected",
      itemValueAttribute: this.entityConfig.idAttribute,
      entityName: this.entityConfig.entityNamePlural,
      onSelectionChange: (selected) => this.updateBulkButton(selected)
    });

    // 2. Row Manager
    this.rowManager = new TableRowManager({
      tableSelector: this.entityConfig.tableSelector,
      rowSelector: this.entityConfig.rowSelector,
      itemIdAttribute: this.entityConfig.idAttribute,
      entityName: this.entityConfig.entityName,
      entityDisplayName: this.entityConfig.entityDisplayName,
      emptyStateConfig: this.entityConfig.emptyStateConfig,
      onRowRemoved: (id, remaining) => {
        logger.debug(`${this.entityConfig.entityName} ${id} removed, ${remaining} remaining`);
        this.checkboxManager?.refresh();
      }
    });

    // 3. Deletion Modal - Pass onEntityDeleted callback in options
    this.deletionModal = new DeletionModal({
      onEntityDeleted: (entityId, result) => {
        logger.debug(
          `Deletion modal callback: ${this.entityConfig.entityName} ${entityId} deleted`
        );
        this.rowManager?.removeRowFromDOM(entityId);
        if (this.entityConfig.notificationHelper) {
          this.entityConfig.notificationHelper.success(
            `${this.entityConfig.entityDisplayName} deleted successfully`
          );
        }
      },
      onModalOpened: () => {
        logger.debug(`${this.entityConfig.entityName} deletion modal opened`);
      },
      onModalClosed: (source) => {
        logger.debug(`${this.entityConfig.entityName} deletion modal closed via ${source}`);
      }
    });

    this.bindBulkDelete();
    this.ensureRowAttributes();

    // Check initial empty state
    setTimeout(() => {
      const rowCount = this.rowManager?.countRows() || 0;
      if (rowCount === 0) {
        logger.debug(`No ${this.entityConfig.entityName}s found, showing empty state`);
        this.rowManager?.showEmptyState();
      }
    }, 100);

    logger.success(`${this.entityConfig.entityName} table manager initialized`);
  }

  ensureRowAttributes() {
    const rows = document.querySelectorAll(
      `${this.entityConfig.tableSelector} ${this.entityConfig.rowSelector}`
    );

    rows.forEach((row) => {
      if (!row.hasAttribute(this.entityConfig.idAttribute)) {
        const checkbox = row.querySelector(`input[name="${this.entityConfig.checkboxName}"]`);
        if (checkbox && checkbox.value) {
          row.setAttribute(this.entityConfig.idAttribute, checkbox.value);
        }
      }
    });
  }

  bindBulkDelete() {
    const btn = document.querySelector(this.entityConfig.bulkDeleteBtn);
    if (!btn) return;

    btn.addEventListener("click", async () => {
      const selectedItems = this.checkboxManager?.getSelectedItems() || [];
      if (selectedItems.length === 0) return;

      const confirmed = confirm(
        `Delete ${selectedItems.length} ${this.entityConfig.entityDisplayName.toLowerCase()}(s)?`
      );
      if (!confirmed) return;

      for (const itemId of selectedItems) {
        const row = document.querySelector(
          `${this.entityConfig.rowSelector}[${this.entityConfig.idAttribute}="${itemId}"]`
        );
        const deleteBtn = row?.querySelector('.modal-open-btn[data-action="open-delete-modal"]');
        if (deleteBtn) {
          deleteBtn.click();
          await new Promise((resolve) => setTimeout(resolve, 300));
        }
      }
    });
  }

  updateBulkButton(selected) {
    const btn = document.querySelector(this.entityConfig.bulkDeleteBtn);
    if (btn) {
      const count = selected.length;
      btn.style.display = count > 0 ? "inline-flex" : "none";
      btn.textContent = count > 0 ? `Delete Selected (${count})` : "";
    }
  }

  refresh() {
    this.ensureRowAttributes();
    this.checkboxManager?.refresh();
    this.rowManager?.refresh();
  }

  destroy() {
    this.checkboxManager?.destroy();
    this.rowManager?.destroy();
    if (this.deletionModal && typeof this.deletionModal.destroy === "function") {
      this.deletionModal.destroy();
    }
  }
}
