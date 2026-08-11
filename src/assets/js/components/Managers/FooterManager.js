import BrowserLogger from "js/core/utils/BrowserLogger";
import { getModalRegistry } from "js/components/Modals/ModalRegistry";
import FooterAbout from "js/backend/pages/FooterPage/FooterAbout";
import Sortable from "sortablejs";
import FilterManager from "js/components/Filters/FilterManager";
import ContentHandler from "js/core/handlers/ContentHandler";
import FooterDataManager from "js/components/Managers/Data/FooterDataManager.js";

export default class FooterManager {
  constructor(options = {}) {
    this.logger = new BrowserLogger("FooterManager");

    // ─── Bind handlers early ──────────────────────────────────
    this._handleModalTrigger = this._handleModalTrigger.bind(this);
    this._handleFormSubmit = this._handleFormSubmit.bind(this);
    this._handleEntityDeleted = this._handleEntityDeleted.bind(this); // ✅ Bound for removal

    // ─── ContentHandler ──────────────────────────────────────
    this.contentHandler = new ContentHandler({
      componentId: "FooterManager_" + Date.now(),
      flashSelector: options.flashSelector || ".footer-page__content-bis",
      containerClass: "flash-container footer-flash",
      position: "prepend",
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
          enabled: true,
          config: {
            redirectOnInsert: false,
            redirectOnUpdate: false,
            redirectOnDelete: false,
            operationDelays: {
              insert: 0,
              update: 0,
              delete: 0
            }
          }
        }
      },
      onSuccess: (response, context) => {
        this.logger.debug("Content response processed successfully", { response, context });
      },
      onError: (error, context) => {
        this.logger.error("Content response processing failed:", error);
      }
    });

    // ─── Safe options merge ──────────────────────────────────
    const {
      updateSortUrl,
      cacheUrl,
      previewUrl,
      publishUrl,
      exportUrl,
      importUrl,
      lazyLoadModals,
      preloadModals,
      filterContainer,
      aboutContainer,
      reloadDelay,
      flashSelector,
      ...restOptions
    } = options;

    this.options = {
      updateSortUrl: updateSortUrl || "/admin/footer/update-sort",
      cacheUrl: cacheUrl || "/admin/footer/clear-cache",
      previewUrl: previewUrl || "/admin/footer/preview",
      publishUrl: publishUrl || "/admin/footer/publish",
      exportUrl: exportUrl || "/admin/footer/export",
      importUrl: importUrl || "/admin/footer/import",
      lazyLoadModals: lazyLoadModals !== false,
      preloadModals: preloadModals || ["column"],
      filterContainer: filterContainer || ".footer-content__links",
      aboutContainer: aboutContainer || ".footer-page__content--about",
      reloadDelay: reloadDelay || 1500,
      flashSelector: flashSelector || ".footer-page__content-bis",
      ...restOptions
    };

    // ─── Properties ────────────────────────────────────────────
    this.sortableInstances = [];
    this.modalRegistry = getModalRegistry();
    this._modalPromises = new Map();
    this._preloaded = false;
    this.filterManager = null;
    this.aboutManager = null;
    this.container = null;
    this.dataManager = null; // Initialized in init()

    this.modalRegistry
      .getModal("deletion")
      .then((modal) => {
        if (modal && typeof modal.init === "function") {
          modal.init();
          this.logger.debug("DeletionModal pre-initialized");
        }
      })
      .catch((error) => {
        this.logger.warn("Failed to pre-initialize DeletionModal:", error);
      });

    this._debouncedUpdateSort = this._debounce(this.updateSortOrders.bind(this), 300);
    this._isInitialized = false;
  }

  // ─── Initialization ─────────────────────────────────────────

  async init() {
    if (this._isInitialized) return this;

    this.logger.debug("Initializing FooterManager");

    if (document.readyState === "loading") {
      await new Promise((resolve) => document.addEventListener("DOMContentLoaded", resolve));
    }

    this.container = document.querySelector(".footer-page__content-bis") || document;

    // ✅ Initialize DataManager AFTER DOM is ready
    this.dataManager = new FooterDataManager();

    this._initFilters();
    this._initAboutManager();
    this.initSettingsButtons();
    await this.initModals();
    this.initSortables();
    this.bindEvents();

    this._isInitialized = true;
    this.logger.success("FooterManager initialized");
    return this;
  }

  // ─── About Manager ──────────────────────────────────────────

  _initAboutManager() {
    const aboutContainer = document.querySelector(this.options.aboutContainer);
    if (!aboutContainer) {
      this.logger.debug("About container not found");
      return;
    }

    const aboutForm = aboutContainer.querySelector(
      'form[data-validate="true"][data-validation-rules*="footerAboutRules"], form#footer-about-frm-id'
    );

    if (!aboutForm) {
      this.logger.debug("No about form found");
      return;
    }

    try {
      this.aboutManager = new FooterAbout({
        notificationContainerId: "footer-notifications",
        flashSelector: this.options.aboutContainer,
        channelStrategy: "flash",
        processors: {
          enabled: true,
          redirect: { enabled: false }
        }
      });
      this.aboutManager._init();
      this.logger.debug("FooterAbout initialized");
    } catch (error) {
      this.logger.error("Failed to initialize FooterAbout:", error);
    }
  }

  // ─── FilterManager ──────────────────────────────────────────

  _initFilters() {
    const container = document.querySelector(this.options.filterContainer);
    if (!container) return;

    const filterElement = container.querySelector("#column-filter");
    if (!filterElement) return;

    const items = container.querySelectorAll(".column-group");
    if (items.length === 0) return;

    this.filterManager = new FilterManager(container, {
      filterSelector: "#column-filter",
      itemSelector: ".column-group",
      attribute: "data-column-id",
      allValue: "all"
    });
  }

  // ─── Sortable Drag & Drop ───────────────────────────────────

  initSortables() {
    // Destroy existing instances first
    this.sortableInstances.forEach((s) => s.destroy?.());
    this.sortableInstances = [];

    const columnsGrid = document.getElementById("columns-grid");
    if (columnsGrid && !columnsGrid.__sortable && columnsGrid.offsetParent !== null) {
      const sortable = new Sortable(columnsGrid, {
        handle: ".drag-handle",
        animation: 150,
        onEnd: () => this._debouncedUpdateSort("columns")
      });
      columnsGrid.__sortable = sortable;
      this.sortableInstances.push(sortable);
    }

    const container = document.querySelector(this.options.filterContainer);
    if (container) {
      container.querySelectorAll(".sortable-list").forEach((list) => {
        if (!list.__sortable && list.offsetParent !== null) {
          const sortable = new Sortable(list, {
            handle: ".drag-handle",
            animation: 150,
            onEnd: () => this._debouncedUpdateSort("links")
          });
          list.__sortable = sortable;
          this.sortableInstances.push(sortable);
        }
      });
    }
  }

  async updateSortOrders(type) {
    let items, idExtractor;

    if (type === "columns") {
      items = document.querySelectorAll("#columns-grid .grid-item");
      // Columns use obfuscated IDs like #g8DnpNyZz5
      idExtractor = (item) => item.dataset.id;
    } else {
      // Links: get ID from button's data-id (not the incorrect list-item data-id)
      items = document.querySelectorAll(".sortable-list .list-item");
      idExtractor = (item) => {
        const editBtn = item.querySelector('button[data-action="edit-link"]');
        return editBtn?.dataset.id || item.dataset.id;
      };
    }

    if (items.length === 0) return;

    const orders = Array.from(items).map((item, index) => ({
      id: idExtractor(item), // ✅ Use ID as-is (string or number)
      sort_order: index + 1
    }));

    this.logger.debug(`Updating ${type} sort orders`, orders);

    try {
      await this.contentHandler.post(
        this.options.updateSortUrl,
        { type, orders },
        { operation: "update", type: "sort" }
      );
    } catch (error) {
      this.logger.error("Failed to update sort order:", error);
    }
  }

  // ─── Settings Buttons ──────────────────────────────────────

  initSettingsButtons() {
    const btns = [
      { id: "clear-cache-btn", fn: () => this._post(this.options.cacheUrl, "Cache cleared") },
      { id: "preview-btn", fn: () => window.open(this.options.previewUrl, "_blank") },
      { id: "publish-btn", fn: () => this._publish() },
      { id: "export-btn", fn: () => this._export() }
    ];

    btns.forEach(({ id, fn }) => {
      const btn = document.getElementById(id);
      if (btn) {
        // Use AbortController pattern or store reference for proper cleanup
        btn.onclick = fn;
      }
    });

    const importBtn = document.getElementById("import-btn");
    const importInput = document.getElementById("import-file-input");
    if (importBtn && importInput) {
      importBtn.onclick = () => importInput.click();
      importInput.onchange = (e) => {
        if (e.target.files[0]) this._import(e.target.files[0]);
      };
    }
  }

  async _post(url, successMsg) {
    try {
      await this.contentHandler.post(url, null, { operation: "update" });
    } catch (error) {
      this.logger.error(`Request failed:`, error);
    }
  }

  async _publish() {
    if (!confirm("Publish changes to production?")) return;
    await this._post(this.options.publishUrl, "Changes published successfully");
  }

  async _export() {
    try {
      const response = await this.contentHandler.get(this.options.exportUrl, {
        operation: "export"
      });

      if (response?.success && response?.data) {
        const blob = new Blob([JSON.stringify(response.data, null, 2)], {
          type: "application/json"
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `footer-config-${new Date().toISOString()}.json`;
        a.click();
        URL.revokeObjectURL(url);
      }
    } catch (error) {
      this.logger.error("Export failed:", error);
    }
  }

  async _import(file) {
    const formData = new FormData();
    formData.append("config", file);

    try {
      const response = await this.contentHandler.post(this.options.importUrl, formData, {
        operation: "import"
      });

      if (response?.success) {
        setTimeout(() => location.reload(), this.options.reloadDelay);
      }
    } catch (error) {
      this.logger.error("Import failed:", error);
    }
  }

  // ─── Modals ─────────────────────────────────────────────────

  _handleModalTrigger = async (event) => {
    const trigger = event.target.closest("[data-modal-type]");
    if (!trigger) return;

    const action = trigger.dataset.action || "";
    if (!action.includes("edit") && !action.includes("add")) return;

    const type = trigger.dataset.modalType;
    if (!type) return;

    const form = trigger.closest("form");
    if (form) {
      const formAction = form.getAttribute("action") || "";
      if (formAction.includes("/add")) {
        this.logger.debug(`Trigger inside add form, letting form submit handler handle it`);
        return;
      }
    }

    event.preventDefault();
    event.stopPropagation();

    this.logger.debug(`Modal trigger clicked: ${action} for ${type}`);

    try {
      const modal = await this.getModal(type);
      if (modal) {
        await modal.openModal(trigger);
      }
    } catch (error) {
      this.logger.error(`Failed to open ${type} modal:`, error);
    }
  };

  _handleFormSubmit = async (event) => {
    const form = event.target;

    const trigger = form.querySelector("[data-modal-type]");
    if (!trigger) return;

    const action = form.getAttribute("action") || "";
    if (!action.includes("/add")) return;

    const type = trigger.dataset.modalType;
    if (!type) return;

    event.preventDefault();
    event.stopPropagation();

    this.logger.debug(`Fetching modal for ${type} with form data`);

    try {
      const modal = await this.getModal(type);
      if (modal) {
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        const url = `${action}?${params.toString()}`;

        await modal.openModalWithUrl(url);
      }
    } catch (error) {
      this.logger.error(`Failed to fetch ${type} modal:`, error);
    }
  };

  async initModals() {
    if (!this.options.lazyLoadModals) {
      await this.preloadModals(["column", "link", "social"]);
    }

    const target = this.container || document;

    target.removeEventListener("click", this._handleModalTrigger);
    target.removeEventListener("submit", this._handleFormSubmit);

    target.addEventListener("click", this._handleModalTrigger);
    target.addEventListener("submit", this._handleFormSubmit);

    this.logger.debug(
      `Modal event listeners bound with delegation on ${target === document ? "document" : "container"}`
    );
  }

  async getModal(type) {
    const key = `modal_${type}`;
    if (this._modalPromises.has(key)) return this._modalPromises.get(key);

    const promise = this.modalRegistry.getModal(type, {
      onModalOpened: () => this.logger.debug(`${type} modal opened`),
      onModalClosed: () => this.logger.debug(`${type} modal closed`),
      closeOnSuccess: true,
      reloadOnSuccess: false,

      onSuccess: (result, context) => {
        this.logger.debug(`🔥 ${type} saved, updating DOM`);
        this.logger.debug(`Form data:`, result?.form_data);

        if (result.form_data || result.data) {
          // ✅ DataManager handles ALL DOM updates
          this.dataManager.handleSave(type, result);
        }
      },
      onError: (error, context) => {
        this.logger.error(`❌ ${type} save failed:`, error);
      }
    });

    this._modalPromises.set(key, promise);
    return promise;
  }

  async preloadModals(types) {
    if (this._preloaded) return;
    try {
      await Promise.allSettled(types.map((t) => this.modalRegistry.getModal(t)));
      this._preloaded = true;
    } catch (error) {
      this.logger.warn("Failed to preload modals:", error);
    }
  }

  // ─── Events ─────────────────────────────────────────────────

  bindEvents() {
    // ✅ Use bound method that can be properly removed
    document.removeEventListener("entity:deleted", this._handleEntityDeleted);
    document.addEventListener("entity:deleted", this._handleEntityDeleted);
  }

  _handleEntityDeleted(event) {
    const { entityId, entityType, context } = event.detail || {};

    if (!entityId) {
      this.logger.warn("entity:deleted event missing entityId");
      return;
    }

    // Resolve type with context-awareness
    const resolvedType = this._resolveEntityType(entityType, entityId, context);

    if (resolvedType === "unknown") {
      this.logger.warn(`Could not resolve entity type for ID: ${entityId}`);
      return;
    }

    this.logger.debug(`Entity deleted: ${resolvedType} (ID: ${entityId})`);

    this.dataManager.handleSave(resolvedType, {
      operation: "delete",
      form_data: { id: entityId },
      data: { id: entityId }
    });

    this._updateAfterDelete();
  }
  _resolveEntityType(preferredType, entityId, context = {}) {
    const normalizedType = String(preferredType || "")
      .trim()
      .toLowerCase();

    // Valid types
    if (["column", "link", "social", "about"].includes(normalizedType)) {
      return normalizedType;
    }

    // Try context-based resolution
    const fallbackType = context?.entityType || context?.form?.dataset?.entityType || null;
    const normalizedFallback = String(fallbackType || "")
      .trim()
      .toLowerCase();

    if (["column", "link", "social", "about"].includes(normalizedFallback)) {
      return normalizedFallback;
    }

    if (!entityId) return "unknown";

    const escapedId = String(entityId).replace(/(["\\])/g, "\\$1");

    // Check DOM for the entity type (order matters - check about first)
    // About items
    if (document.querySelector(`.about-item[data-id="${escapedId}"]`)) return "about";

    // Social cards
    if (document.querySelector(`.social-card[data-id="${escapedId}"]`)) return "social";

    // Columns
    if (document.querySelector(`.grid-item[data-id="${escapedId}"]`)) return "column";

    // Links (use button data-id)
    if (document.querySelector(`button[data-action="edit-link"][data-id="${escapedId}"]`))
      return "link";

    return "unknown";
  }

  // _resolveEntityType(preferredType, entityId, context = {}) {
  //   const normalizedType = String(preferredType || "")
  //     .trim()
  //     .toLowerCase();

  //   // Add 'about' to valid types
  //   if (["column", "link", "social", "about"].includes(normalizedType)) {
  //     return normalizedType;
  //   }

  //   // Try context-based resolution
  //   const fallbackType = context?.entityType || context?.form?.dataset?.entityType || null;
  //   const normalizedFallback = String(fallbackType || "")
  //     .trim()
  //     .toLowerCase();

  //   if (["column", "link", "social", "about"].includes(normalizedFallback)) {
  //     return normalizedFallback;
  //   }

  //   if (!entityId) return "unknown";

  //   const escapedId = String(entityId).replace(/(["\\])/g, "\\$1");

  //   // Check in order of specificity
  //   if (document.querySelector(`.social-card[data-id="${escapedId}"]`)) return "social";
  //   if (document.querySelector(`.grid-item[data-id="${escapedId}"]`)) return "column";
  //   if (document.querySelector(`.about-item[data-id="${escapedId}"]`)) return "about";
  //   if (document.querySelector(`button[data-action="edit-link"][data-id="${escapedId}"]`))
  //     return "link";

  //   return "unknown";
  // }

  /**
   * ✅ Post-deletion cleanup only - DOM removal is handled by DataManager
   */
  _updateAfterDelete() {
    this.filterManager?.refresh();
    this.initSortables();
    // Note: Link counts are updated by DataManager, no need to call _updateLinkCounts
  }

  // ─── Helpers ─────────────────────────────────────────────────

  _debounce(func, wait) {
    let timeout;
    return (...args) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => func(...args), wait);
    };
  }

  // ─── Cleanup ─────────────────────────────────────────────────

  destroy() {
    // ✅ Remove event listener with properly bound reference
    document.removeEventListener("entity:deleted", this._handleEntityDeleted);

    // Remove delegated listeners
    if (this.container) {
      this.container.removeEventListener("click", this._handleModalTrigger);
      this.container.removeEventListener("submit", this._handleFormSubmit);
    }

    // Destroy managers
    this.filterManager?.destroy();
    this.filterManager = null;

    if (this.contentHandler) {
      this.contentHandler.destroy();
      this.contentHandler = null;
    }

    if (this.aboutManager) {
      try {
        this.aboutManager.destroy();
      } catch (error) {
        this.logger.debug("Error destroying aboutManager:", error);
      }
      this.aboutManager = null;
    }

    if (this.dataManager) {
      this.dataManager.destroy();
      this.dataManager = null;
    }

    // Destroy sortables
    this.sortableInstances.forEach((s) => s.destroy?.());
    this.sortableInstances = [];

    // Clear modal promises
    this._modalPromises.clear();

    this.container = null;
    this._isInitialized = false;

    this.logger.debug("FooterManager destroyed");
  }
}
