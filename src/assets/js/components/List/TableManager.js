import TableCheckboxManager from "./TableCheckboxManager";
import TableRowManager from "./TableRowManager";
import PaginationManager from "./PaginationManager";
import BrowserLogger from "js/core/utils/BrowserLogger";
import AjaxHandler from "js/core/utils/AjaxHandler";
import ContentHandler from "js/core/handlers/ContentHandler";
import { getModalRegistry } from "js/components/Modals/ModalRegistry";

const logger = new BrowserLogger("TableManager");

function looksLikeRawSqlError(msg) {
  if (!msg || typeof msg !== "string") return false;
  return /SQLSTATE$$|Integrity constraint|FOREIGN KEY|Cannot delete or update a parent row|SQL syntax|Duplicate entry|Cannot add or update a child row/i.test(
    msg
  );
}

function humanizeDeletionError({ status, message, displayName = "item" }) {
  const lower = displayName.toLowerCase();

  if (status == null) {
    if (looksLikeRawSqlError(message)) {
      return `This ${lower} can't be deleted because it's still referenced by other records.`;
    }
    return message || "Couldn't reach the server. Please check your connection and try again.";
  }

  if (status === 401 || status === 403) {
    return "You don't have permission to perform this action.";
  }
  if (status === 404) {
    return `This ${lower} no longer exists. The list will refresh.`;
  }
  if (status === 409) {
    return `This ${lower} can't be deleted because it's still in use by other records.`;
  }
  if (status === 422) {
    return looksLikeRawSqlError(message)
      ? `This ${lower} can't be deleted right now.`
      : message || `This ${lower} can't be deleted.`;
  }
  if (status >= 500) {
    return "Something went wrong on our end. Please try again.";
  }

  if (looksLikeRawSqlError(message)) {
    return `This ${lower} can't be deleted because it's still referenced by other records.`;
  }
  return message || `Failed to delete ${lower}. Please try again.`;
}

export default class TableManager {
  constructor(entityConfig = {}, options = {}) {
    const effective = {
      tableType: "products",
      entityName: "product",
      entityDisplayName: "Product",
      entityNamePlural: "products",
      idAttribute: "data-product-id",
      checkboxName: "products[]",
      ...entityConfig
    };

    this.entityConfig = {
      ...effective,
      tableSelector:
        entityConfig.tableSelector || `.table[data-table-type="${effective.tableType}"]`,
      bulkDeleteBtn: entityConfig.bulkDeleteBtn || `#bulk-delete-${effective.tableType}`,
      rowSelector: entityConfig.rowSelector || ".table__body--row",
      emptyStateConfig: {
        title: `No ${effective.entityDisplayName.toLowerCase()}s found`,
        message: `Get started by adding your first ${effective.entityDisplayName.toLowerCase()}`,
        actionUrl: `/${effective.tableType}/create`,
        actionText: `Add ${effective.entityDisplayName}`,
        ...(entityConfig.emptyStateConfig || {})
      },
      ...options
    };

    this.tableElement = options.tableElement || null;
    this.ajaxHandler = new AjaxHandler();
    this.contentHandler = new ContentHandler({
      componentId: `${this.entityConfig.entityName}_table_${Date.now()}`,
      flashSelector: this.entityConfig.tableSelector,
      containerClass: "flash-container table-flash",
      position: "before",
      durations: {
        success: 5000,
        error: 0,
        warning: 5000,
        info: 4000
      },
      autoHide: true,
      dismissible: true,
      showIcon: true,
      showProgress: true,
      pauseOnHover: true,
      enableRedirectProcessor: false,
      processors: {
        enabled: true,
        notification: {
          enabled: true,
          config: { permanentErrors: true }
        },
        redirect: {
          enabled: false
        }
      },
      onSuccess: (response, context) => {
        logger.debug(`${this.entityConfig.entityName} operation successful`, { response, context });
      },
      onError: (error, context) => {
        logger.error(`${this.entityConfig.entityName} operation failed:`, error);
      }
    });

    // ✅ Get ModalRegistry singleton
    this.modalRegistry = getModalRegistry();

    this.modalRegistry
      .getModal("deletion")
      .then((modal) => {
        if (modal && typeof modal.init === "function") {
          modal.init();
          logger.debug("DeletionModal pre-initialized");
        }
      })
      .catch((error) => {
        logger.warn("Failed to pre-initialize DeletionModal:", error);
      });

    // Sub-managers
    this.checkboxManager = null;
    this.rowManager = null;
    this.deletionModal = null;
    this.paginationManager = null;

    // Bound handlers
    this._boundDeletedHandler = null;
    this._boundErrorHandler = null;
    this._boundPaginationHandler = null;

    // Cache for modal promises
    this._modalPromises = new Map();

    this.init();
  }

  init() {
    logger.debug(`Initializing ${this.entityConfig.entityName} table manager`);

    const table = this.tableElement || document.querySelector(this.entityConfig.tableSelector);
    if (!table) {
      logger.warn(`${this.entityConfig.entityName} table not found`);
      return;
    }
    this.tableElement = table;

    // 1. Pagination manager with AJAX
    const paginationEl =
      table.parentElement?.querySelector(":scope > .pagination") ||
      table.parentElement?.querySelector(".pagination") ||
      document.querySelector(".pagination");

    this.paginationManager = new PaginationManager({
      container: paginationEl,
      tableSelector: this.entityConfig.tableSelector,
      onPageChange: (page, perPage) => this.loadTablePage(page, perPage)
    });

    // 2. Checkbox + Row managers
    this.checkboxManager = new TableCheckboxManager({
      tableElement: table,
      tableSelector: this.entityConfig.tableSelector,
      selectAllSelector: "#select-all-select",
      itemSelector: `input[name="${this.entityConfig.checkboxName}"]`,
      rowSelector: this.entityConfig.rowSelector,
      selectedClass: "row--selected",
      itemValueAttribute: this.entityConfig.idAttribute,
      entityName: this.entityConfig.entityNamePlural,
      onSelectionChange: (selected) => this.updateBulkButton(selected)
    });

    this.rowManager = new TableRowManager({
      tableElement: table,
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

    // 4. Subscribe to events
    this._bindDeletionEvents();
    this._bindPaginationEvents();

    this.bindBulkDelete();
    this.ensureRowAttributes();

    // Synchronous empty-state check
    if ((this.rowManager?.countRows() || 0) === 0) {
      this.rowManager?.showEmptyState();
    }

    logger.success(`${this.entityConfig.entityName} table manager initialized`);
  }

  _bindPaginationEvents() {
    this._boundPaginationHandler = (e) => this._handlePageChange(e);
    document.addEventListener("pagination:page-change", this._boundPaginationHandler);
  }

  async loadTablePage(page, perPage) {
    logger.debug(`Loading page ${page} with ${perPage} items`);

    const tbody = this.tableElement?.querySelector("tbody");
    const originalHtml = tbody?.innerHTML;
    if (tbody) {
      tbody.style.opacity = "0.5";
    }

    try {
      const url = new URL(window.location.href);
      url.searchParams.set("page", page);
      url.searchParams.set("per_page", perPage);
      url.searchParams.set("ajax", "1");

      // ✅ Use ContentHandler for the request
      const response = await this.contentHandler.get(url.toString(), {
        operation: "paginate",
        page: page,
        perPage: perPage
      });

      if (response.success && response.html) {
        if (tbody) {
          tbody.innerHTML = response.html;
          tbody.style.opacity = "1";
        }

        if (response.total !== undefined) {
          this.paginationManager?.updateUI(response.total, page, perPage);
        }

        this.ensureRowAttributes();
        this.checkboxManager?.refresh();
        this._refreshRowDeleteButtons();
      } else {
        logger.error("Failed to load page:", response.error);
        if (tbody) {
          tbody.innerHTML = originalHtml || "";
          tbody.style.opacity = "1";
        }
      }
    } catch (error) {
      logger.error("Failed to load page:", error);
      if (tbody) {
        tbody.innerHTML = originalHtml || "";
        tbody.style.opacity = "1";
      }
    }
  }

  _refreshRowDeleteButtons() {
    const rows = this.tableElement?.querySelectorAll(this.entityConfig.rowSelector) || [];
    rows.forEach((row) => {
      if (!row.hasAttribute(this.entityConfig.idAttribute)) {
        const checkbox = row.querySelector(`input[name="${this.entityConfig.checkboxName}"]`);
        if (checkbox && checkbox.value) {
          row.setAttribute(this.entityConfig.idAttribute, checkbox.value);
        }
      }
    });
  }

  _handlePageChange(event) {
    const { page, perPage } = event.detail;
    this.loadTablePage(page, perPage);
  }

  _bindDeletionEvents() {
    this._boundDeletedHandler = (e) => this._handleEntityDeleted(e);
    this._boundErrorHandler = (e) => this._handleDeletionError(e);

    document.addEventListener("entity:deleted", this._boundDeletedHandler);
    document.addEventListener("entity:delete-error", this._boundErrorHandler);

    logger.debug(`[${this.entityConfig.entityName}] Deletion event listeners registered`);
  }

  _belongsToThisTable(entityId) {
    if (!entityId) return false;
    return !!this.rowManager?.findRow(entityId);
  }

  _handleEntityDeleted(event) {
    const { entityId, result } = event.detail || {};
    if (!this._belongsToThisTable(entityId)) return;

    // ✅ Remove the row from DOM
    this.rowManager?.removeRowFromDOM(entityId);

    const remainingOnPage = this.rowManager?.countRows() || 0;
    this.paginationManager?.handleRowRemoved(remainingOnPage);

    if (remainingOnPage === 0 && this.paginationManager?.totalItems === 1) {
      this.rowManager?.showEmptyState();
    }
  }

  _handleDeletionError(event) {
    const { entityId, error, message } = event.detail || {};

    if (entityId && !this._belongsToThisTable(entityId)) return;

    const status = error?.status ?? error?.result?.status ?? null;
    const displayName = this.entityConfig.entityDisplayName;

    if (status === 404 && entityId) {
      this.rowManager?.removeRowFromDOM(entityId);
      return;
    }
  }

  ensureRowAttributes() {
    const rows = this.tableElement?.querySelectorAll(this.entityConfig.rowSelector) || [];

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
        const row = this.tableElement?.querySelector(
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
    if (!btn) return;

    const count = selected.length;
    btn.style.display = count > 0 ? "inline-flex" : "none";
    btn.textContent = count > 0 ? `Delete Selected (${count})` : "";
  }

  refresh() {
    this.ensureRowAttributes();
    this.checkboxManager?.refresh();
    this.rowManager?.refresh();
    this.loadTablePage(
      this.paginationManager?.currentPage || 1,
      this.paginationManager?.perPage || 10
    );
  }

  destroy() {
    if (this._boundDeletedHandler) {
      document.removeEventListener("entity:deleted", this._boundDeletedHandler);
    }
    if (this._boundErrorHandler) {
      document.removeEventListener("entity:delete-error", this._boundErrorHandler);
    }
    if (this._boundPaginationHandler) {
      document.removeEventListener("pagination:page-change", this._boundPaginationHandler);
    }

    this._boundDeletedHandler = null;
    this._boundErrorHandler = null;
    this._boundPaginationHandler = null;

    // ✅ Destroy ContentHandler
    if (this.contentHandler) {
      this.contentHandler.destroy();
      this.contentHandler = null;
    }

    this.checkboxManager?.destroy();
    this.rowManager?.destroy();

    if (this.deletionModal) {
      this.deletionModal.destroy?.();
      this.deletionModal = null;
    }

    this.paginationManager?.destroy();
    this._modalPromises.clear();
    this.tableElement = null;
  }
}
