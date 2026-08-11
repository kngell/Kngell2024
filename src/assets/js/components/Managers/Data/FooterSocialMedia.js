import BaseEntity from "./BaseEntity.js";

export default class FooterSocialMedia extends BaseEntity {
  static STATUS_TEXT = { active: "Active", inactive: "Inactive" };

  constructor(options = {}) {
    super("social", {
      containerSelector: options.containerSelector || "#social-grid",
      itemSelector: ".social-card",
      paginationSelector: options.paginationSelector || ".pagination",
      perPage: options.perPage || 10,
      statusText: FooterSocialMedia.STATUS_TEXT
    });
  }

  createItemHtml(data) {
    const escapedId = this._escapeHtml(data.id);
    const title = this._escapeHtml(data.name || data.title || "Social Link");
    const platform = this._escapeHtml(data.platform || "");
    const url = this._escapeHtml(data.url || "");
    const iconClass = data.icon_class || data.icon || "icon-default";
    const sortOrder = data.sort_order || 0;
    const isActive = this._parseActiveStatus(data.is_active) ? "active" : "inactive";
    const statusText = isActive === "active" ? "Active" : "Inactive";

    return `
      <div class="social-card" data-id="${escapedId}" data-sort="${sortOrder}">
        <div class="social-icon">
          <svg class="icon ${iconClass}" aria-label="${title}" role="img">
            <use href="/public/assets/img/icons-sprite.svg#/${iconClass}"></use>
          </svg>
        </div>
        <div class="social-info">
          <h3>${title}</h3>
          <code>${url}</code>
          <span class="platform-badge">${platform}</span>
        </div>
        <div class="social-status">
          <span class="status-badge ${isActive}">${statusText}</span>
        </div>
        <div class="social-actions">
          <form style="display: inline;" action="/admin/footer-social/edit" method="GET">
            <input type="hidden" name="id" value="${escapedId}">
            <button class="btn btn--icon-only icon-btn" data-action="edit-social" data-id="${escapedId}" data-modal-type="social" type="submit">
              <span class="btn__icon"><svg class="icon" aria-label="Edit Social Link" role="img"><use href="/public/assets/img/icons-sprite.svg#icon-edit"></use></svg></span>
            </button>
          </form>
          <form style="display: inline;" action="/admin/footer-socials-confirm-deletion/confirm" method="POST">
            <input type="hidden" name="csrfToken" value="">
            <input type="hidden" name="frm_name">
            <input type="hidden" name="id" value="${escapedId}">
            <button class="btn btn--icon-only icon-btn" data-action="confirm-delete" data-id="${escapedId}" data-modal-type="social" type="submit">
              <span class="btn__icon"><svg class="icon" aria-label="Delete Social Link" role="img"><use href="/public/assets/img/icons-sprite.svg#icon-trash"></use></svg></span>
            </button>
          </form>
        </div>
      </div>
    `;
  }

  updateItemContent(item, data) {
    const titleEl = item.querySelector(".social-info h3");
    if (titleEl) {
      titleEl.textContent = data.name || data.title || "Social Link";
    }

    const codeEl = item.querySelector(".social-info code");
    if (codeEl) {
      codeEl.textContent = data.url || "";
    }

    const platformBadge = item.querySelector(".platform-badge");
    if (platformBadge) {
      platformBadge.textContent = data.platform || "";
    }

    const iconUse = item.querySelector(".social-icon use");
    const iconSvg = item.querySelector(".social-icon svg");
    const iconClass = data.icon_class || data.icon || "icon-default";
    if (iconUse) {
      iconUse.setAttribute("href", `/public/assets/img/icons-sprite.svg#/${iconClass}`);
    }
    if (iconSvg) {
      iconSvg.setAttribute("class", `icon ${iconClass}`);
    }

    if (data.sort_order !== undefined) {
      item.dataset.sort = data.sort_order;
    }

    this._updateStatusBadge(item, data);
  }

  _updateSortOrders(container) {
    const items = container.querySelectorAll(this.itemSelector);
    items.forEach((item, index) => {
      item.dataset.sort = index;
    });
  }

  findItem(id) {
    const escapedId = this._escapeSelectorValue(id);
    let item = this.container?.querySelector(`${this.itemSelector}[data-id="${escapedId}"]`);
    if (!item) {
      // Try finding by button id attribute (for older markup)
      const button = document.querySelector(`button[id="${escapedId}"][data-modal-type="social"]`);
      if (button) {
        item = button.closest(this.itemSelector);
      }
    }
    return item || null;
  }
}
