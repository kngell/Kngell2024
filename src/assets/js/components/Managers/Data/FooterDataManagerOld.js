import BrowserLogger from "js/core/utils/BrowserLogger";
import PaginationManager from "js/components/List/PaginationManager.js";

export default class FooterDataManagerOld {
  static HIGHLIGHT_DURATION = 3000;
  static REMOVE_ANIMATION_DURATION = 300;

  static ITEM_SELECTORS = {
    column: ".grid-item",
    link: ".list-item",
    social: ".social-card",
    about: ".about-item"
  };

  static STATUS_TEXT = {
    column: { active: "Active", inactive: "Inactive" },
    link: { active: "Active Status", inactive: "Inactive Status" },
    social: { active: "Active", inactive: "Inactive" },
    about: { active: "Active", inactive: "Inactive" }
  };

  // Entity types that could have pagination
  static PAGINATED_ENTITIES = ["column", "social"];

  #pendingTimeouts = [];

  constructor(options = {}) {
    this.logger = new BrowserLogger("FooterDataManager");

    // Safe options merge
    const {
      columnGridSelector,
      linkContainerSelector,
      socialGridSelector,
      aboutContainerSelector,
      ...restOptions
    } = options;

    this.options = {
      columnGridSelector: columnGridSelector || "#columns-grid",
      linkContainerSelector: linkContainerSelector || "#links-container",
      socialGridSelector: socialGridSelector || "#social-grid",
      aboutContainerSelector: aboutContainerSelector || ".about-items",
      ...restOptions
    };

    this.columnGrid = document.querySelector(this.options.columnGridSelector);
    this.linkContainer = document.querySelector(this.options.linkContainerSelector);
    this.socialGrid = document.querySelector(this.options.socialGridSelector);
    this.aboutContainer = document.querySelector(this.options.aboutContainerSelector);

    // ✅ Per-entity pagination managers
    this.paginationManagers = this._initPaginationManagers();

    this.logger.debug("FooterDataManager initialized", {
      paginatedEntities: Object.keys(this.paginationManagers)
    });
  }

  _initPaginationManagers() {
    const managers = {};

    FooterDataManager.PAGINATED_ENTITIES.forEach((type) => {
      const container = this._getPaginationContainer(type);

      if (container) {
        managers[type] = new PaginationManager({
          container, // Pass DOM element directly
          perPage: 10
        });
        this.logger.debug(`Pagination initialized for ${type}`);
      }
    });

    return managers;
  }

  /**
   * Find the pagination container based on its proximity to the entity grid.
   */
  _getPaginationContainer(type) {
    switch (type) {
      case "column":
        return this.columnGrid?.closest(".footer-content__columns")?.querySelector(".pagination");
      case "social":
        return this.socialGrid?.closest(".footer-content__socials")?.querySelector(".pagination");
      default:
        return null;
    }
  }

  // ==================== MAIN HANDLER ====================

  handleSave(type, result) {
    const operation = result.operation?.toLowerCase() || "update";
    const data = result.form_data || result.data || {};
    const resolvedType = this._resolveType(type, data);

    this.logger.debug(`Handling ${operation} for ${resolvedType}`, { data });

    if (!data?.id) {
      this.logger.warn("No data or ID in response, cannot update DOM");
      return;
    }

    switch (operation) {
      case "insert":
        this._handleInsert(resolvedType, data);
        break;
      case "update":
        this._handleUpdate(resolvedType, data);
        break;
      case "delete":
      case "destroy":
        this._handleDelete(resolvedType, data);
        break;
      default:
        this.logger.warn(`Unknown operation: ${operation}`);
    }
  }

  // ==================== INSERT ====================

  _handleInsert(type, data) {
    this.logger.debug(`Inserting new ${type}`, data);

    const container = this._getContainer(type);
    if (!container) {
      this.logger.warn(`Container not found for ${type}`);
      return;
    }

    if (type === "link") {
      this._insertLinkIntoColumn(container, data);
      return;
    }

    this._insertItemIntoContainer(container, type, data);
    this._updatePaginationForInsert(type); // ✅ Update specific pagination
    this.logger.success(`${type} inserted successfully (ID: ${data.id})`);
  }

  _insertLinkIntoColumn(container, data) {
    const columnId = data.column_id || data.columnId;

    if (!columnId) {
      this.logger.warn("No column_id provided for link");
      this._insertItemIntoContainer(container, "link", data);
      return;
    }

    const columnGroup = this._findColumnGroup(container, columnId);

    if (columnGroup) {
      const sortableList = columnGroup.querySelector(".sortable-list");
      if (sortableList) {
        this._insertItemIntoContainer(sortableList, "link", data);
        this._updateLinkCountForColumn(columnGroup);
        this.logger.success(`Link inserted into column ${columnId} (ID: ${data.id})`);
        return;
      }
    }

    this.logger.warn(`Column group ${columnId} not found`);
    this._insertItemIntoContainer(container, "link", data);
  }

  _insertItemIntoContainer(container, type, data) {
    const itemHtml = this._createItemHtml(type, data);
    if (!itemHtml) {
      this.logger.warn(`Could not create HTML for ${type}`);
      return;
    }

    container.insertAdjacentHTML("afterbegin", itemHtml);

    const selector = FooterDataManager.ITEM_SELECTORS[type] || "[data-id]";
    const newItem = container.querySelector(`${selector}:first-child`);

    if (newItem) {
      newItem.classList.add("highlight-new");
      const timeoutId = setTimeout(() => {
        newItem?.classList.remove("highlight-new");
      }, FooterDataManager.HIGHLIGHT_DURATION);
      this.#pendingTimeouts.push(timeoutId);
    }

    this._updateSortOrders(container, type);
  }

  // ==================== UPDATE ====================

  _handleUpdate(type, data) {
    this.logger.debug(`Updating ${type}`, data);

    const item = this._findItemByType(type, data.id);
    if (!item) {
      this.logger.warn(`Item ${data.id} not found, cannot update`);
      return;
    }

    this._updateItemInDOM(item, data, type);
    this.logger.success(`${type} updated successfully (ID: ${data.id})`);
  }

  // ==================== DELETE ====================

  _handleDelete(type, data) {
    this.logger.debug(`Deleting ${type}`, data);

    const id = data.id || data.entityId || data.data?.id;
    if (!id) {
      this.logger.warn("No ID found for deletion");
      return;
    }

    const item = this._findItemByType(type, id);
    if (!item) {
      this.logger.warn(`Item ${id} not found in ${type} container`);
      return;
    }

    this._removeItemWithAnimation(item, type, id);
  }

  _removeItemWithAnimation(item, type, id) {
    const selector = FooterDataManager.ITEM_SELECTORS[type] || "[data-id]";
    const container = item.parentElement;
    const allItems = container.querySelectorAll(selector);
    const remainingOnPage = allItems.length - 1;

    // Capture column group BEFORE removing (for links)
    const columnGroup = type === "link" ? item.closest(".column-group") : null;

    // Animate removal
    item.style.transition = "all 0.3s ease";
    item.style.opacity = "0";
    item.style.transform = "scale(0.9)";

    const timeoutId = setTimeout(() => {
      item.remove();

      // ✅ Update pagination for the correct entity type
      this._updatePaginationForDelete(type, remainingOnPage);

      // Update link counts
      if (type === "link" && columnGroup) {
        this._updateLinkCountForColumn(columnGroup);
      }

      // Cascade-remove links for a deleted column on the frontend so UI matches server state
      if (type === "column" && this.linkContainer) {
        const colGroup = this._findColumnGroup(this.linkContainer, id);
        if (colGroup) {
          const removedLinks = colGroup.querySelectorAll(".list-item").length;
          colGroup.remove();
          this.logger.debug(`Removed ${removedLinks} link(s) for deleted column ${id}`);
        }
      }

      this.logger.success(`${type} deleted (ID: ${id})`);
    }, FooterDataManager.REMOVE_ANIMATION_DURATION);

    this.#pendingTimeouts.push(timeoutId);
  }

  // ==================== PAGINATION UPDATES ====================

  _updatePaginationForInsert(type) {
    const pagination = this.paginationManagers[type];
    if (!pagination) return;

    const currentTotal = pagination.totalItems;
    const newTotal = currentTotal + 1;

    const totalEl = pagination.container.querySelector(".pagination__total");
    if (totalEl) {
      totalEl.textContent = String(newTotal);
    }

    const currentEl = pagination.container.querySelector(".pagination__current");
    if (currentEl) {
      const match = currentEl.textContent.match(/(\d+)\s*-\s*(\d+)/);
      if (match) {
        const end = parseInt(match[2], 10);
        if (end < pagination.perPage) {
          currentEl.textContent = `${match[1]}-${end + 1}`;
        }
      }
    }

    pagination.totalItems = newTotal;
    this.logger.debug(`${type} pagination updated: total ${newTotal}`);
  }

  _updatePaginationForDelete(type, remainingOnPage) {
    const pagination = this.paginationManagers[type];
    if (!pagination) return;

    pagination.handleRowRemoved(remainingOnPage);
    this.logger.debug(`${type} pagination updated: ${JSON.stringify(pagination.getState())}`);
  }

  // ==================== COLUMN GROUP HELPERS ====================

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

  // ==================== ITEM FINDING ====================

  _findItemByType(type, id) {
    const container = this._getContainer(type);
    if (!container) return null;

    const escapedId = this._escapeSelectorValue(id);

    switch (type) {
      case "column":
        return container.querySelector(`.grid-item[data-id="${escapedId}"]`);

      case "link":
        return this._findLinkItem(container, escapedId);

      case "social":
        return container.querySelector(`.social-card[data-id="${escapedId}"]`);

      case "about":
        return this._findAboutItem(container, escapedId);

      default:
        return container.querySelector(`[data-id="${escapedId}"]`);
    }
  }

  _findLinkItem(container, escapedId) {
    // Links have incorrect data-id on list-item, use button's data-id
    const editBtn = container.querySelector(
      `button[data-action="edit-link"][data-id="${escapedId}"]`
    );
    if (editBtn) return editBtn.closest(".list-item");

    const deleteBtn = container.querySelector(
      `button[data-action="delete-link"][data-id="${escapedId}"]`
    );
    if (deleteBtn) return deleteBtn.closest(".list-item");

    return null;
  }

  _findAboutItem(container, escapedId) {
    // About items store data-id on the item itself
    const item = container.querySelector(`.about-item[data-id="${escapedId}"]`);
    if (item) return item;

    // Fallback: find by button data-id (edit button)
    const editBtn = container.querySelector(
      `button[data-action="edit-about"][data-id="${escapedId}"]`
    );
    if (editBtn) return editBtn.closest(".about-item");

    // Fallback: find by delete button
    const deleteBtn = container.querySelector(
      `button[data-action="confirm-delete"][data-id="${escapedId}"]`
    );
    if (deleteBtn) return deleteBtn.closest(".about-item");

    return null;
  }

  _escapeSelectorValue(value) {
    return String(value).replace(/(["'\\])/g, "\\$1");
  }

  // ==================== SORT ORDERS ====================

  _updateSortOrders(container, type) {
    // About items don't have sort order display
    if (type === "about") return;

    const selector = FooterDataManager.ITEM_SELECTORS[type] || "[data-id]";
    const items = container.querySelectorAll(selector);

    items.forEach((item, index) => {
      item.dataset.sort = index;
      const sortDisplay = item.querySelector(".item-details span");
      if (sortDisplay) {
        sortDisplay.textContent = `Sort : ${index}`;
      }
    });
  }

  // ==================== TYPE RESOLUTION ====================

  _resolveType(type, data = {}) {
    const normalizedType = String(type || "")
      .trim()
      .toLowerCase();

    if (["column", "link", "social", "about"].includes(normalizedType)) {
      return normalizedType;
    }

    const id = data.id || data.entityId || data.data?.id;
    if (!id) return "unknown";

    const escapedId = this._escapeSelectorValue(id);

    if (document.querySelector(`.social-card[data-id="${escapedId}"]`)) return "social";
    if (document.querySelector(`.grid-item[data-id="${escapedId}"]`)) return "column";
    if (document.querySelector(`.about-item[data-id="${escapedId}"]`)) return "about";
    if (document.querySelector(`button[data-action="edit-link"][data-id="${escapedId}"]`))
      return "link";

    return "unknown";
  }

  // ==================== CONTAINER GETTERS ====================

  _getContainer(type) {
    switch (type) {
      case "column":
        return this.columnGrid;
      case "link":
        return this.linkContainer;
      case "social":
        return this.socialGrid;
      case "about":
        return this.aboutContainer;
      default:
        return null;
    }
  }

  // ==================== HTML CREATION ====================

  _createItemHtml(type, data) {
    const container = this._getContainer(type);
    if (!container) return null;

    const selector = FooterDataManager.ITEM_SELECTORS[type] || "[data-id]";
    const existingItems = container.querySelectorAll(selector);

    if (existingItems.length > 0) {
      return this._createItemFromTemplate(existingItems[0], data, type);
    }

    return this._createFallbackHtml(type, data);
  }

  _createItemFromTemplate(template, data, type) {
    const clone = template.cloneNode(true);

    clone.dataset.id = data.id;
    if (data.sort_order !== undefined) {
      clone.dataset.sort = data.sort_order;
    }

    // For social cards, update icon
    if (type === "social") {
      const iconUse = clone.querySelector(".social-icon use");
      const iconSvg = clone.querySelector(".social-icon svg");
      const iconClass = data.icon_class || data.icon || "icon-default";

      if (iconUse) {
        iconUse.setAttribute("href", `/public/assets/img/icons-sprite.svg#/${iconClass}`);
      }

      if (iconSvg) {
        iconSvg.setAttribute("class", `icon ${iconClass}`);
      }
    }

    this._updateItemContent(clone, data, type);
    this._updateItemForms(clone, data, type);

    clone.classList.remove("highlight-new");
    clone.style.cssText = "";

    const wrapper = document.createElement("div");
    wrapper.innerHTML = clone.outerHTML;
    return wrapper.innerHTML;
  }

  _createFallbackHtml(type, data) {
    const escapedId = this._escapeHtml(data.id);
    const escapedTitle = this._escapeHtml(data.title || "New Item");

    switch (type) {
      case "column":
        return `
          <div class="grid-item" data-id="${escapedId}" data-sort="${data.sort_order || 0}">
            <div class="drag-handle">
              <svg class="icon drag" aria-label="Drag" role="img"><use href="/public/assets/img/icons-sprite.svg#icon-drag"></use></svg>
            </div>
            <div class="item-content">
              <div class="item-header">
                <h3>${escapedTitle}</h3>
                <span class="status-badge active">Active</span>
              </div>
              <div class="item-details">
                <code>key :${this._escapeHtml(data.column_key || "")}</code>
                <span>Sort : ${data.sort_order || 0}</span>
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
          </div>`;

      case "link":
        return `
          <div class="list-item" data-id="${escapedId}" data-sort="${data.sort_order || 0}">
            <div class="drag-handle">
              <svg class="icon drag" aria-label="Drag" role="img"><use href="/public/assets/img/icons-sprite.svg#icon-drag"></use></svg>
            </div>
            <div class="item-info">
              <strong>${escapedTitle}</strong>
              <code>${this._escapeHtml(data.url || "")}</code>
              <span class="target-badge">${this._escapeHtml(data.target || "_self")}</span>
            </div>
            <div class="item-status">
              <span class="status-badge active">Active Status</span>
            </div>
            <div class="item-actions">
              <button class="icon-btn" data-action="edit-link" data-id="${escapedId}" type="button">
                <svg class="icon edit-link" aria-label="Edit Link" role="img"><use href="/public/assets/img/icons-sprite.svg#icon-edit"></use></svg>
              </button>
              <button class="icon-btn delete" data-action="delete-link" data-id="${escapedId}" type="button">
                <svg class="icon delete-link" aria-label="Delete Link" role="img"><use href="/public/assets/img/icons-sprite.svg#icon-trash"></use></svg>
              </button>
            </div>
          </div>`;

      case "social":
        const socialTitle = data.name || data.title || "Social Link";
        const socialPlatform = data.platform || "";
        const socialUrl = data.url || "";
        const iconClass = data.icon_class || data.icon || "icon-default";
        const isActive = this._parseActiveStatus(data.is_active) ? "active" : "inactive";
        const statusText = isActive === "active" ? "Active" : "Inactive";

        return `
    <div class="social-card" data-id="${escapedId}" data-sort="${data.sort_order || 0}">
      <div class="social-icon">
        <svg class="icon ${iconClass}" aria-label="${this._escapeHtml(socialTitle)}" role="img">
          <use href="/public/assets/img/icons-sprite.svg#/${this._escapeHtml(iconClass)}"></use>
        </svg>
      </div>
      <div class="social-info">
        <h3>${this._escapeHtml(socialTitle)}</h3>
        <code>${this._escapeHtml(socialUrl)}</code>
        <span class="platform-badge">${this._escapeHtml(socialPlatform)}</span>
      </div>
      <div class="social-status">
        <span class="status-badge ${isActive}">${statusText}</span>
      </div>
      <div class="social-actions">
        <form style="display: inline;" action="/admin/footer-social/edit" method="GET">
          <input type="hidden" name="id" value="${escapedId}">
          <button class="btn btn--icon-only icon-btn" 
                  data-action="edit-social" 
                  data-id="${escapedId}" 
                  data-modal-type="social" 
                  type="submit">
            <span class="btn__icon">
              <svg class="icon" aria-label="Edit Social Link" role="img">
                <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
              </svg>
            </span>
          </button>
        </form>
        <form style="display: inline;" action="/admin/footer-socials-confirm-deletion/confirm" method="POST">
          <input type="hidden" name="csrfToken" value="">
          <input type="hidden" name="frm_name">
          <input type="hidden" name="id" value="${escapedId}">
          <button class="btn btn--icon-only icon-btn" 
                  data-action="confirm-delete" 
                  data-id="${escapedId}" 
                  data-modal-type="social" 
                  type="submit">
            <span class="btn__icon">
              <svg class="icon" aria-label="Delete Social Link" role="img">
                <use href="/public/assets/img/icons-sprite.svg#icon-trash"></use>
              </svg>
            </span>
          </button>
        </form>
      </div>
    </div>`;

      case "about":
        return `
          <div class="about-item" data-id="${escapedId}">
            <div class="about-item__content">
              <p class="about-item__content-text">${this._escapeHtml(data.content || data.text || "")}</p>
              <div class="about-item__content-meta">
                <span class="status-badge active">Active</span>
                <span class="valid_from">Valid from: ${this._escapeHtml(data.valid_from || data.validFrom || "")}</span>
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
          </div>`;

      default:
        return `<div data-id="${escapedId}">${escapedTitle}</div>`;
    }
  }

  // ==================== CONTENT UPDATES ====================

  _updateItemContent(item, data, type) {
    if (type === "column") {
      this._updateColumnContent(item, data);
    } else if (type === "link") {
      this._updateLinkContent(item, data);
    } else if (type === "social") {
      this._updateSocialContent(item, data);
    } else if (type === "about") {
      this._updateAboutContent(item, data);
    }

    this._updateStatusBadge(item, data, type);
  }

  _updateColumnContent(item, data) {
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
  }

  _updateLinkContent(item, data) {
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
  }

  _updateSocialContent(item, data) {
    // Update title
    const titleEl = item.querySelector(".social-info h3");
    if (titleEl) {
      titleEl.textContent = data.name || data.title || "Social Link";
    }

    // Update URL
    const codeEl = item.querySelector(".social-info code");
    if (codeEl) {
      codeEl.textContent = data.url || "";
    }

    // Update platform badge
    const platformBadge = item.querySelector(".platform-badge");
    if (platformBadge) {
      platformBadge.textContent = data.platform || "";
    }

    // Update icon
    const iconUse = item.querySelector(".social-icon use");
    const iconSvg = item.querySelector(".social-icon svg");
    const iconClass = data.icon_class || data.icon || "icon-default";

    if (iconUse) {
      iconUse.setAttribute("href", `/public/assets/img/icons-sprite.svg#/${iconClass}`);
    }

    if (iconSvg) {
      iconSvg.setAttribute("class", `icon ${iconClass}`);
    }

    // Update sort order if present
    if (data.sort_order !== undefined) {
      item.dataset.sort = data.sort_order;
    }
  }

  _updateAboutContent(item, data) {
    const textEl = item.querySelector(".about-item__content-text");
    if (textEl) {
      textEl.textContent = data.content || data.text || data.description || "";
    }

    const validFromEl = item.querySelector(".valid_from");
    if (validFromEl) {
      const validFrom = data.valid_from || data.validFrom || data.date || "";
      validFromEl.textContent = validFrom ? `Valid from: ${validFrom}` : "Valid from: ";
    }
  }

  _updateStatusBadge(item, data, type) {
    const statusBadge = item.querySelector(".status-badge");
    if (!statusBadge || data.is_active === undefined) return;

    const isActive = this._parseActiveStatus(data.is_active);
    const statusTexts = FooterDataManager.STATUS_TEXT[type] || FooterDataManager.STATUS_TEXT.column;
    const text = isActive ? statusTexts.active : statusTexts.inactive;

    statusBadge.textContent = text;
    statusBadge.className = `status-badge ${isActive ? "active" : "inactive"}`;
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

  // ==================== FORM UPDATES ====================

  _updateItemForms(item, data, type) {
    const id = String(data.id);

    // Update all hidden inputs with id
    item.querySelectorAll('input[name="id"]').forEach((input) => {
      input.value = id;
    });

    // Update all buttons with data-id
    item.querySelectorAll("button[data-id]").forEach((button) => {
      button.dataset.id = id;
    });

    // For about items, also update action attributes on forms if needed
    if (type === "about") {
      const editForm = item.querySelector('.edit-btn[action*="footer-about/edit"]');
      if (editForm) {
        // Form action already has the ID via hidden input
      }
    }
  }

  _updateItemInDOM(item, data, type) {
    if (data.sort_order !== undefined) {
      item.dataset.sort = data.sort_order;
    }

    this._updateItemContent(item, data, type);
    this._updateItemForms(item, data, type);
  }

  // ==================== UTILITIES ====================

  _escapeHtml(str) {
    if (!str) return "";
    const div = document.createElement("div");
    div.textContent = String(str);
    return div.innerHTML;
  }

  destroy() {
    this.#pendingTimeouts.forEach(clearTimeout);
    this.#pendingTimeouts = [];

    Object.values(this.paginationManagers).forEach((pm) => pm?.destroy?.());
    this.paginationManagers = {};

    this.columnGrid = null;
    this.linkContainer = null;
    this.socialGrid = null;
    this.aboutContainer = null;

    this.logger.debug("FooterDataManager destroyed");
  }
}
