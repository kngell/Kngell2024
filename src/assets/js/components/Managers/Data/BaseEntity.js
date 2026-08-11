// src/js/components/Managers/Data/BaseEntity.js
import BrowserLogger from "js/core/utils/BrowserLogger";
import PaginationManager from "js/components/List/PaginationManager.js";

export default class BaseEntity {
  static HIGHLIGHT_DURATION = 3000;
  static REMOVE_ANIMATION_DURATION = 300;

  constructor(entityType, options = {}) {
    this.entityType = entityType;
    this.logger = new BrowserLogger(`${entityType}Handler`);

    this.containerSelector = options.containerSelector;
    this.itemSelector = options.itemSelector;
    this.paginationSelector = options.paginationSelector || null;
    this.perPage = options.perPage || 10;
    this.statusText = options.statusText || { active: "Active", inactive: "Inactive" };

    this.container = document.querySelector(this.containerSelector);
    this.paginationManager = null;
    this._pendingTimeouts = []; // ✅ Protected property (convention)

    if (this.paginationSelector && this.container) {
      const paginationEl =
        this.container.closest(".footer-content__columns")?.querySelector(".pagination") ||
        this.container.closest(".footer-content__socials")?.querySelector(".pagination") ||
        document.querySelector(this.paginationSelector);
      if (paginationEl) {
        this.paginationManager = new PaginationManager({
          container: paginationEl,
          perPage: this.perPage
        });
        this.logger.debug(`Pagination initialized for ${entityType}`);
      }
    }
  }

  getContainer() {
    return this.container;
  }

  findItem(id) {
    if (!this.container) return null;
    const escapedId = this._escapeSelectorValue(id);
    return this.container.querySelector(`${this.itemSelector}[data-id="${escapedId}"]`);
  }

  findAllItems() {
    if (!this.container) return [];
    return this.container.querySelectorAll(this.itemSelector);
  }

  insertItem(data) {
    const container = this.getContainer();
    if (!container) {
      this.logger.warn(`Container not found for ${this.entityType}`);
      return false;
    }

    const itemHtml = this.createItemHtml(data);
    if (!itemHtml) return false;

    container.insertAdjacentHTML("afterbegin", itemHtml);
    const newItem = container.querySelector(`${this.itemSelector}:first-child`);

    if (newItem) {
      newItem.classList.add("highlight-new");
      const timeoutId = setTimeout(() => {
        newItem?.classList.remove("highlight-new");
      }, BaseEntity.HIGHLIGHT_DURATION);
      this._pendingTimeouts.push(timeoutId);
    }

    this._updateSortOrders(container);
    this._updatePaginationForInsert();
    return true;
  }

  updateItem(id, data) {
    const item = this.findItem(id);
    if (!item) {
      this.logger.warn(`Item ${id} not found for ${this.entityType}`);
      return false;
    }

    if (data.sort_order !== undefined) {
      item.dataset.sort = data.sort_order;
    }

    this.updateItemContent(item, data);
    this._updateItemForms(item, data);
    return true;
  }

  async deleteItem(id) {
    const item = this.findItem(id);
    if (!item) {
      this.logger.warn(`Item ${id} not found in ${this.entityType} container`);
      return false;
    }

    return new Promise((resolve) => {
      const container = item.parentElement;
      const allItems = container.querySelectorAll(this.itemSelector);
      const remainingOnPage = allItems.length - 1;

      const context = this._captureRemovalContext(item);

      item.style.transition = "all 0.3s ease";
      item.style.opacity = "0";
      item.style.transform = "scale(0.9)";

      const timeoutId = setTimeout(() => {
        item.remove();
        this._updatePaginationForDelete(remainingOnPage);
        this._afterItemRemoved(context, id);
        this.logger.success(`${this.entityType} deleted (ID: ${id})`);
        resolve(true);
      }, BaseEntity.REMOVE_ANIMATION_DURATION);

      this._pendingTimeouts.push(timeoutId);
    });
  }

  // To be overridden by child classes
  createItemHtml(data) {
    throw new Error("createItemHtml must be implemented by child class");
  }

  updateItemContent(item, data) {
    throw new Error("updateItemContent must be implemented by child class");
  }

  _captureRemovalContext(item) {
    return null;
  }

  _afterItemRemoved(context, id) {
    // Override if needed
  }

  _updateSortOrders(container) {
    // Override if needed
  }

  _updatePaginationForInsert() {
    if (!this.paginationManager) return;

    const currentTotal = this.paginationManager.totalItems;
    const newTotal = currentTotal + 1;

    const totalEl = this.paginationManager.container.querySelector(".pagination__total");
    if (totalEl) {
      totalEl.textContent = String(newTotal);
    }

    const currentEl = this.paginationManager.container.querySelector(".pagination__current");
    if (currentEl) {
      const match = currentEl.textContent.match(/(\d+)\s*-\s*(\d+)/);
      if (match) {
        const end = parseInt(match[2], 10);
        if (end < this.paginationManager.perPage) {
          currentEl.textContent = `${match[1]}-${end + 1}`;
        }
      }
    }

    this.paginationManager.totalItems = newTotal;
  }

  _updatePaginationForDelete(remainingOnPage) {
    if (!this.paginationManager) return;
    this.paginationManager.handleRowRemoved(remainingOnPage);
  }

  _updateItemForms(item, data) {
    const id = String(data.id);
    item.querySelectorAll('input[name="id"]').forEach((input) => {
      input.value = id;
    });
    item.querySelectorAll("button[data-id]").forEach((button) => {
      button.dataset.id = id;
    });
    item.querySelectorAll("button[id]").forEach((button) => {
      button.id = id;
    });
  }

  _escapeSelectorValue(value) {
    return String(value).replace(/(["'\\])/g, "\\$1");
  }

  _escapeHtml(str) {
    if (!str) return "";
    const div = document.createElement("div");
    div.textContent = String(str);
    return div.innerHTML;
  }

  _parseActiveStatus(value) {
    return (
      value === true ||
      value === 1 ||
      value === "1" ||
      value === "common.yes" ||
      String(value).toLowerCase() === "active"
    );
  }

  _updateStatusBadge(item, data) {
    const statusBadge = item.querySelector(".status-badge");
    if (!statusBadge || data.is_active === undefined) return;

    const isActive = this._parseActiveStatus(data.is_active);
    const text = isActive ? this.statusText.active : this.statusText.inactive;
    statusBadge.textContent = text;
    statusBadge.className = `status-badge ${isActive ? "active" : "inactive"}`;
  }

  refresh() {
    if (this.paginationSelector && this.container) {
      const paginationEl =
        this.container.closest(".footer-content__columns")?.querySelector(".pagination") ||
        this.container.closest(".footer-content__socials")?.querySelector(".pagination") ||
        document.querySelector(this.paginationSelector);
      if (paginationEl) {
        this.paginationManager = new PaginationManager({
          container: paginationEl,
          perPage: this.perPage
        });
      }
    }
  }

  destroy() {
    this._pendingTimeouts.forEach(clearTimeout);
    this._pendingTimeouts = [];
    if (this.paginationManager) {
      this.paginationManager.destroy?.();
      this.paginationManager = null;
    }
    this.container = null;
  }
}
