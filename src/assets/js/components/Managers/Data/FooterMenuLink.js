import BaseEntity from "./BaseEntity";

export default class FooterMenuLink extends BaseEntity {
  static STATUS_TEXT = { active: "Active Status", inactive: "Inactive Status" };

  constructor(options = {}) {
    super("link", {
      containerSelector: options.containerSelector || "#links-container",
      itemSelector: ".list-item",
      paginationSelector: null,
      statusText: FooterMenuLink.STATUS_TEXT
    });
    this.filterContainer = options.filterContainer || ".footer-content__links";
  }

  createItemHtml(data) {
    const escapedId = this._escapeHtml(data.id);
    const title = this._escapeHtml(data.title || "New Link");
    const url = this._escapeHtml(data.url || "");
    const target = this._escapeHtml(data.target || "_self");
    const sortOrder = data.sort_order || 0;
    const isActive = this._parseActiveStatus(data.is_active) ? "active" : "inactive";
    const statusText = isActive === "active" ? "Active Status" : "Inactive Status";

    return `
      <div class="list-item" data-id="${escapedId}" data-sort="${sortOrder}">
        <div class="drag-handle">
          <svg class="icon drag" aria-label="Drag" role="img"><use href="/public/assets/img/icons-sprite.svg#icon-drag"></use></svg>
        </div>
        <div class="item-info">
          <strong>${title}</strong>
          <code>${url}</code>
          <span class="target-badge">${target}</span>
        </div>
        <div class="item-status">
          <span class="status-badge ${isActive}">${statusText}</span>
        </div>
        <div class="item-actions">
          <button class="icon-btn" data-action="edit-link" data-id="${escapedId}" type="button">
            <svg class="icon edit-link" aria-label="Edit Link" role="img"><use href="/public/assets/img/icons-sprite.svg#icon-edit"></use></svg>
          </button>
          <button class="icon-btn delete" data-action="delete-link" data-id="${escapedId}" type="button">
            <svg class="icon delete-link" aria-label="Delete Link" role="img"><use href="/public/assets/img/icons-sprite.svg#icon-trash"></use></svg>
          </button>
        </div>
      </div>
    `;
  }

  updateItemContent(item, data) {
    const itemInfo = item.querySelector(".item-info");
    if (!itemInfo) return;

    const titleEl = itemInfo.querySelector("strong");
    if (titleEl && data.title) {
      titleEl.textContent = data.title;
    }

    const codeEl = itemInfo.querySelector("code");
    if (codeEl) {
      codeEl.textContent = data.url || "";
    }

    const targetBadge = itemInfo.querySelector(".target-badge");
    if (targetBadge && data.target) {
      targetBadge.textContent = data.target;
    }

    if (data.sort_order !== undefined) {
      item.dataset.sort = data.sort_order;
    }

    this._updateStatusBadge(item, data);
  }

  findLinkItem(id) {
    const escapedId = this._escapeSelectorValue(id);
    const editBtn = document.querySelector(
      `button[data-action="edit-link"][data-id="${escapedId}"]`
    );
    if (editBtn) return editBtn.closest(".list-item");
    const deleteBtn = document.querySelector(
      `button[data-action="delete-link"][data-id="${escapedId}"]`
    );
    if (deleteBtn) return deleteBtn.closest(".list-item");
    return null;
  }

  _findColumnGroup(container, columnId) {
    let columnGroup = container.querySelector(`.column-group[data-column-id="${columnId}"]`);
    if (columnGroup) return columnGroup;

    for (const group of container.querySelectorAll(".column-group")) {
      const dataColumnId = group.dataset.columnId;
      if (
        dataColumnId === columnId ||
        dataColumnId === `#${columnId}` ||
        dataColumnId.replace(/^#/, "") === String(columnId).replace(/^#/, "")
      ) {
        return group;
      }
    }
    return null;
  }

  _updateLinkCountForColumn(columnGroup) {
    if (!columnGroup) return;
    const countSpan = columnGroup.querySelector(".link-count");
    if (countSpan) {
      const links = columnGroup.querySelectorAll(".list-item");
      countSpan.textContent = `${links.length} link${links.length !== 1 ? "s" : ""}`;
    }
  }

  insertItem(data) {
    const container = this.getContainer();
    if (!container) {
      this.logger.warn(`Container not found for ${this.entityType}`);
      return false;
    }

    const columnId = data.column_id || data.columnId;
    if (!columnId) {
      this.logger.warn("No column_id provided for link");
      return this._insertIntoContainer(container, data);
    }

    const columnGroup = this._findColumnGroup(container, columnId);
    if (columnGroup) {
      const sortableList = columnGroup.querySelector(".sortable-list");
      if (sortableList) {
        const result = this._insertIntoContainer(sortableList, data);
        this._updateLinkCountForColumn(columnGroup);
        this.logger.success(`Link inserted into column ${columnId} (ID: ${data.id})`);
        return result;
      }
    }

    this.logger.warn(`Column group ${columnId} not found`);
    return this._insertIntoContainer(container, data);
  }

  _insertIntoContainer(container, data) {
    const itemHtml = this.createItemHtml(data);
    if (!itemHtml) return false;

    container.insertAdjacentHTML("afterbegin", itemHtml);
    const newItem = container.querySelector(`${this.itemSelector}:first-child`);

    if (newItem) {
      newItem.classList.add("highlight-new");
      const timeoutId = setTimeout(() => {
        newItem?.classList.remove("highlight-new");
      }, BaseEntity.HIGHLIGHT_DURATION);
      // ✅ Use _pendingTimeouts from parent
      this._pendingTimeouts.push(timeoutId);
    }

    this._updateSortOrders(container);
    return true;
  }

  _updateSortOrders(container) {
    const items = container.querySelectorAll(this.itemSelector);
    items.forEach((item, index) => {
      item.dataset.sort = index;
    });
  }

  _afterItemRemoved(context, id) {
    if (context?.columnGroup) {
      this._updateLinkCountForColumn(context.columnGroup);
    }
  }

  _captureRemovalContext(item) {
    return {
      columnGroup: item.closest(".column-group")
    };
  }
}
