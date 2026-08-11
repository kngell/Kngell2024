import BaseEntity from "./BaseEntity.js";

export default class FooterAbout extends BaseEntity {
  static STATUS_TEXT = { active: "Active", inactive: "Inactive" };

  constructor(options = {}) {
    super("about", {
      containerSelector: options.containerSelector || ".about-items",
      itemSelector: ".about-item",
      paginationSelector: null,
      statusText: FooterAbout.STATUS_TEXT
    });
  }

  createItemHtml(data) {
    const escapedId = this._escapeHtml(data.id);
    const content = this._escapeHtml(data.content || data.text || data.description || "");
    const validFrom = this._escapeHtml(data.valid_from || data.validFrom || data.date || "");
    const isActive = this._parseActiveStatus(data.is_active) ? "active" : "inactive";
    const statusText = isActive === "active" ? "Active" : "Inactive";

    return `
      <div class="about-item" data-id="${escapedId}">
        <div class="about-item__content">
          <p class="about-item__content-text">${content}</p>
          <div class="about-item__content-meta">
            <span class="status-badge ${isActive}">${statusText}</span>
            <span class="valid_from">Valid from: ${validFrom}</span>
          </div>
        </div>
        <div class="about-item__actions">
          <form class="edit-btn" style="display: inline;" action="/admin/footer-about/edit" method="GET">
            <input type="hidden" name="id" value="${escapedId}">
            <button class="btn btn--icon-only edit-existing" data-action="edit-about" data-id="${escapedId}" data-modal-type="about" type="button">
              <span class="btn__icon"><svg class="icon" aria-label="Edit" role="img"><use href="/public/assets/img/icons-sprite.svg#icon-edit"></use></svg></span>
              <span class="btn__label">Edit</span>
            </button>
          </form>
          <form class="delete-btn" style="display: inline;" action="/admin/footer-about-confirm-deletion/confirm" method="POST">
            <input type="hidden" name="id" value="${escapedId}">
            <button class="btn btn--icon-only delete" data-action="confirm-delete" data-id="${escapedId}" data-modal-type="about" type="button">
              <span class="btn__icon"><svg class="icon" aria-label="Delete" role="img"><use href="/public/assets/img/icons-sprite.svg#icon-trash"></use></svg></span>
              <span class="btn__label">Delete</span>
            </button>
          </form>
        </div>
      </div>
    `;
  }

  updateItemContent(item, data) {
    const textEl = item.querySelector(".about-item__content-text");
    if (textEl) {
      textEl.textContent = data.content || data.text || data.description || "";
    }

    const validFromEl = item.querySelector(".valid_from");
    if (validFromEl) {
      const validFrom = data.valid_from || data.validFrom || data.date || "";
      validFromEl.textContent = validFrom ? `Valid from: ${validFrom}` : "Valid from: ";
    }

    this._updateStatusBadge(item, data);
  }
}
