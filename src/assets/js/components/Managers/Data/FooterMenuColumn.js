import BaseEntity from "./BaseEntity.js";

export default class FooterMenuColumn extends BaseEntity {
  static STATUS_TEXT = { active: "Active", inactive: "Inactive" };

  constructor(options = {}) {
    super("column", {
      containerSelector: options.containerSelector || "#columns-grid",
      itemSelector: ".grid-item",
      paginationSelector: options.paginationSelector || ".pagination",
      perPage: options.perPage || 10,
      statusText: FooterMenuColumn.STATUS_TEXT
    });
  }

  createItemHtml(data) {
    const escapedId = this._escapeHtml(data.id);
    const title = this._escapeHtml(data.title || data.name || "New Column");
    const columnKey = this._escapeHtml(data.column_key || "");
    const sortOrder = data.sort_order || 0;
    const isActive = this._parseActiveStatus(data.is_active) ? "active" : "inactive";
    const statusText = isActive === "active" ? "Active" : "Inactive";

    return `
      <div class="grid-item" data-id="${escapedId}" data-sort="${sortOrder}">
        <div class="drag-handle">
          <svg class="icon drag" aria-label="Drag" role="img"><use href="/public/assets/img/icons-sprite.svg#icon-drag"></use></svg>
        </div>
        <div class="item-content">
          <div class="item-header">
            <h3>${title}</h3>
            <span class="status-badge ${isActive}">${statusText}</span>
          </div>
          <div class="item-details">
            <code>key :${columnKey}</code>
            <span>Sort : ${sortOrder}</span>
          </div>
        </div>
        <div class="item-actions">
          <form style="display: inline;" action="/admin/footer-column/edit" method="GET">
            <input type="hidden" name="id" value="${escapedId}">
            <button class="icon-btn" data-action="edit-column" data-id="${escapedId}" data-modal-type="column">
              <svg class="icon edit" aria-label="Edit" role="img"><use href="/public/assets/img/icons-sprite.svg#icon-edit"></use></svg>
            </button>
          </form>
          <form style="display: inline;" action="/admin/footer-column-confirm-deletion/confirm" method="POST">
            <input type="hidden" name="id" value="${escapedId}">
            <button class="icon-btn delete" data-action="confirm-delete" data-id="${escapedId}">
              <svg class="icon delete" aria-label="Delete" role="img"><use href="/public/assets/img/icons-sprite.svg#icon-trash"></use></svg>
            </button>
          </form>
        </div>
      </div>
    `;
  }

  updateItemContent(item, data) {
    const titleEl = item.querySelector("h3");
    if (titleEl && (data.title || data.name)) {
      titleEl.textContent = data.title || data.name;
    }

    const codeEl = item.querySelector("code");
    if (codeEl && data.column_key) {
      codeEl.textContent = `key :${data.column_key}`;
    }

    const sortDisplay = item.querySelector(".item-details span");
    if (sortDisplay && data.sort_order !== undefined) {
      sortDisplay.textContent = `Sort : ${data.sort_order}`;
    }

    this._updateStatusBadge(item, data);
  }

  _updateSortOrders(container) {
    const items = container.querySelectorAll(this.itemSelector);
    items.forEach((item, index) => {
      item.dataset.sort = index;
      const sortDisplay = item.querySelector(".item-details span");
      if (sortDisplay) {
        sortDisplay.textContent = `Sort : ${index}`;
      }
    });
  }
}
