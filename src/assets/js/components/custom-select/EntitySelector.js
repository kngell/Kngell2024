import BrowserLogger from "js/core/utils/BrowserLogger";
import CustomSelect from "./custom-select";
import CardManager from "./card-manager";

const logger = new BrowserLogger("EntitySelector");

export default class EntitySelector {
  constructor(selector, options = {}) {
    this.selector = selector;
    this.options = {
      entityType: "entity",
      apiEndpoint: null,
      dataSource: null,
      fieldName: null,
      cardRenderer: null,
      itemFormatter: null,
      loadEntity: null,
      onSelect: null,
      onReset: null,
      placeholder: null,
      ...options
    };

    this.container = null;
    this.customSelect = null;
    this.cardManager = null;
    this.relationshipBody = null;
  }

  init() {
    this.container = document.querySelector(this.selector);
    if (!this.container) {
      logger.error(`Container not found: ${this.selector}`);
      return;
    }

    this.relationshipBody = this.container.closest(".form-section__body");

    // Initialize CardManager
    if (this.relationshipBody) {
      this.cardManager = new CardManager(this.relationshipBody, {
        cardSelector: ".product-card",
        renderer: this.options.cardRenderer,
        onRemove: () => {
          if (this.customSelect) {
            this.customSelect.reset();
          }
          if (this.options.onReset) {
            this.options.onReset();
          }
        }
      });
      this.cardManager.init();
    }

    // Create data source for CustomSelect
    const dataSource = this.options.dataSource || this.createDataSource();

    // Initialize CustomSelect
    this.customSelect = new CustomSelect(this.selector, {
      dataSource: dataSource,
      placeholder: this.options.placeholder || `Select ${this.options.entityType}...`,
      emptyMessage: `No ${this.options.entityType}s found`,
      loadingMessage: `Loading ${this.options.entityType}s...`,
      enableSearch: true,
      enableInfiniteScroll: true,
      pageSize: 20,
      itemFormatter: this.options.itemFormatter,
      name: this.options.fieldName,
      onSelect: (value, text, item) => {
        this.handleEntitySelect(item);
        if (this.options.onSelect) {
          this.options.onSelect(value, text, item);
        }
      },
      onReset: () => {
        this.handleEntityReset();
        if (this.options.onReset) {
          this.options.onReset();
        }
      }
    });

    this.customSelect.init();

    // Set initial value if exists
    const hiddenInput = this.container.querySelector(".input-field__hidden-value");
    if (hiddenInput?.value) {
      this.loadAndSelectEntity(hiddenInput.value);
    }
  }

  createDataSource() {
    return async (page, limit, search = "") => {
      if (!this.options.apiEndpoint) {
        return { items: [], total: 0 };
      }

      const params = new URLSearchParams({
        page,
        limit,
        search
      });

      const response = await fetch(`${this.options.apiEndpoint}?${params}`);
      const data = await response.json();

      return {
        items: (data.items || data.data || []).map((item) => ({
          id: item.id,
          value: item.id,
          label: item.label || item.name || item.title,
          name: item.name || item.title,
          sku: item.sku,
          code: item.code,
          description: item.description,
          shortDescription: item.shortDescription,
          image: item.image || item.image_url,
          ...item
        })),
        total: data.total || 0,
        hasMore: data.hasMore || false
      };
    };
  }

  async loadAndSelectEntity(entityId) {
    try {
      let entity = null;

      if (this.options.loadEntity) {
        entity = await this.options.loadEntity(entityId);
      } else if (this.options.apiEndpoint) {
        const response = await fetch(`${this.options.apiEndpoint}?id=${entityId}`);
        const data = await response.json();
        entity = data.entity || data.data || data;
      }

      if (entity && this.customSelect) {
        const item = {
          id: entity.id,
          value: entity.id,
          label: entity.label || entity.name || entity.title,
          name: entity.name || entity.title,
          sku: entity.sku,
          code: entity.code,
          description: entity.description,
          shortDescription: entity.shortDescription,
          image: entity.image || entity.image_url,
          ...entity
        };
        this.customSelect.selectOption(item);
      }
    } catch (error) {
      logger.error(`Failed to load ${this.options.entityType}`, error);
    }
  }

  handleEntitySelect(item) {
    if (this.cardManager) {
      this.cardManager.render(item);
    }
  }

  handleEntityReset() {
    if (this.cardManager) {
      this.cardManager.clear();
    }
  }

  getValue() {
    return this.customSelect?.getValue();
  }

  getSelectedItem() {
    return this.customSelect?.getSelectedItem();
  }

  reset() {
    this.customSelect?.reset();
  }

  destroy() {
    if (this.customSelect) {
      this.customSelect.destroy();
    }
    if (this.cardManager) {
      this.cardManager.destroy();
    }
  }
}
