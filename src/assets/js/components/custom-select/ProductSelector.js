import BrowserLogger from "js/core/utils/BrowserLogger";
import ProductCard from "./product-card";

const logger = new BrowserLogger("ProductSelector");

export default class ProductSelector {
  constructor(selector, customSelectInstance, options = {}) {
    this.selector = selector;
    this.customSelect = customSelectInstance;
    this.options = {
      onSelect: null,
      onReset: null,
      ...options
    };

    this.container = null;
    this.productCard = null;
    this.relationshipBody = null;
  }

  init() {
    this.container = document.querySelector(this.selector);
    if (!this.container) {
      logger.error(`Container not found: ${this.selector}`);
      return;
    }

    this.relationshipBody = this.container.closest(".form-section__body");

    // Initialize ProductCard
    if (this.relationshipBody) {
      this.productCard = new ProductCard(this.relationshipBody);
      this.relationshipBody.addEventListener("product-card:remove", () => {
        if (this.customSelect) {
          this.customSelect.reset();
        }
      });
    }

    // Attach to existing CustomSelect events
    if (this.customSelect) {
      // Store original handlers
      const originalOnSelect = this.customSelect.options.onSelect;
      const originalOnReset = this.customSelect.options.onReset;

      // Wrap handlers
      this.customSelect.options.onSelect = (value, text, item) => {
        this.handleProductSelect(item);
        if (originalOnSelect) originalOnSelect(value, text, item);
        if (this.options.onSelect) this.options.onSelect(value, text, item);
      };

      this.customSelect.options.onReset = () => {
        this.handleProductReset();
        if (originalOnReset) originalOnReset();
        if (this.options.onReset) this.options.onReset();
      };
    }

    // Set initial value if exists
    const hiddenInput = this.container.querySelector(".input-field__hidden-value");
    if (hiddenInput?.value && this.customSelect) {
      this.loadAndSelectProduct(hiddenInput.value);
    }
  }

  async loadAndSelectProduct(productId) {
    try {
      const apiEndpoint =
        this.customSelect?.options?.apiEndpoint || "/small-banner-search/load-products";
      const response = await fetch(`${apiEndpoint}?id=${productId}`);
      const data = await response.json();
      const product = data.product || data;

      if (product && this.customSelect) {
        const item = {
          id: product.id,
          value: product.id,
          label: product.sku ? `${product.name} (${product.sku})` : product.name,
          name: product.name,
          sku: product.sku,
          description: product.description,
          shortDescription: product.shortDescription,
          image: product.image
        };
        this.customSelect.selectOption(item);
      }
    } catch (error) {
      logger.error("Failed to load product", error);
    }
  }

  handleProductSelect(item) {
    if (this.productCard) {
      this.productCard.render({
        id: item.id,
        name: item.name,
        sku: item.sku,
        description: item.description,
        shortDescription: item.shortDescription || item.description,
        image: item.image
      });
    }
  }

  handleProductReset() {
    if (this.productCard) {
      this.productCard.clear();
    }
  }

  getValue() {
    return this.customSelect?.getValue();
  }

  reset() {
    this.customSelect?.reset();
  }

  destroy() {
    if (this.productCard) {
      this.productCard.destroy();
    }
  }
}
