import BrowserLogger from "js/core/utils/BrowserLogger";
import FooterMenuColumn from "./FooterMenuColumn";
import FooterMenuLink from "./FooterMenuLink";
import FooterSocialMedia from "./FooterSocialMedia";
import FooterAbout from "./FooterAbout";

export default class FooterDataManager {
  constructor(options = {}) {
    this.logger = new BrowserLogger("FooterDataManager");
    this.handlers = {};
    this._initHandlers(options);
  }

  _initHandlers(options) {
    this.handlers.column = new FooterMenuColumn({
      containerSelector: options.columnGridSelector || "#columns-grid",
      paginationSelector: options.columnPaginationSelector || ".pagination",
      perPage: options.perPage || 10
    });

    this.handlers.link = new FooterMenuLink({
      containerSelector: options.linkContainerSelector || "#links-container",
      filterContainer: options.filterContainer || ".footer-content__links"
    });

    this.handlers.social = new FooterSocialMedia({
      containerSelector: options.socialGridSelector || "#social-grid",
      paginationSelector: options.socialPaginationSelector || ".pagination",
      perPage: options.perPage || 10
    });

    this.handlers.about = new FooterAbout({
      containerSelector: options.aboutContainerSelector || ".about-items"
    });

    this.logger.debug("FooterDataManager initialized with handlers", Object.keys(this.handlers));
  }

  handleSave(type, result) {
    const operation = result.operation?.toLowerCase() || "update";
    const data = result.form_data || result.data || {};

    if (!data?.id) {
      this.logger.warn("No data or ID in response, cannot update DOM");
      return;
    }

    // Resolve type with fallback
    let resolvedType = this._resolveType(type, data);

    // If still unknown, detect from data
    if (resolvedType === "unknown") {
      resolvedType = this._detectTypeFromData(data);
    }

    this.logger.debug(`Handling ${operation} for ${resolvedType}`, { data });

    const handler = this.handlers[resolvedType];
    if (!handler) {
      this.logger.warn(`No handler found for type: ${resolvedType}`);
      return;
    }

    switch (operation) {
      case "insert":
        handler.insertItem(data);
        this.logger.success(`${resolvedType} inserted successfully (ID: ${data.id})`);
        break;
      case "update":
        handler.updateItem(data.id, data);
        this.logger.success(`${resolvedType} updated successfully (ID: ${data.id})`);
        break;
      case "delete":
      case "destroy":
        handler.deleteItem(data.id);
        this.logger.success(`${resolvedType} deleted (ID: ${data.id})`);
        break;
      default:
        this.logger.warn(`Unknown operation: ${operation}`);
    }
  }

  _resolveType(type, data = {}) {
    const normalizedType = String(type || "")
      .trim()
      .toLowerCase();
    if (["column", "link", "social", "about"].includes(normalizedType)) {
      return normalizedType;
    }
    return "unknown";
  }

  _detectTypeFromData(data) {
    const id = data.id;
    if (!id) return "unknown";

    // Check each handler's container
    for (const [type, handler] of Object.entries(this.handlers)) {
      const item = handler.findItem(id);
      if (item) {
        return type;
      }
    }

    // For links, they might be inside column groups
    if (this.handlers.link) {
      const linkItem = this.handlers.link.findLinkItem?.(id);
      if (linkItem) return "link";
    }

    return "unknown";
  }

  // Public methods
  getHandler(type) {
    return this.handlers[type] || null;
  }

  refreshAll() {
    Object.values(this.handlers).forEach((handler) => {
      if (handler.refresh) handler.refresh();
    });
  }

  destroy() {
    Object.values(this.handlers).forEach((handler) => handler.destroy());
    this.handlers = {};
    this.logger.debug("FooterDataManager destroyed");
  }
}
