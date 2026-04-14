import BrowserLogger from "js/core/utils/BrowserLogger";
import CustomSelect from "./custom-select";
import ProductCard from "./product-card";

const logger = new BrowserLogger("ProductSelector");

export default class ProductSelector {
  constructor(selector, options = {}) {
    this.selector = selector;
    this.options = {
      apiEndpoint: "/small-banner-search/load-products",
      onSelect: null,
      onReset: null,
      ...options
    };

    this.container = null;
    this.customSelect = null;
    this.productCard = null;
    this.relationshipBody = null;
  }

  init() {
    this.container = document.querySelector(this.selector);
    if (!this.container) {
      logger.error(`Container not found: ${this.selector}`);
      return;
    }

    this.relationshipBody = this.container.closest(".product-relationship__body");

    // Initialize ProductCard
    if (this.relationshipBody) {
      this.productCard = new ProductCard(this.relationshipBody);
      this.relationshipBody.addEventListener("product-card:remove", () => {
        if (this.customSelect) {
          this.customSelect.reset();
        }
      });
    }

    // Initialize CustomSelect
    this.customSelect = new CustomSelect(this.selector, {
      dataSource: async (page, limit) => {
        const params = new URLSearchParams({
          page,
          limit,
          search: ""
        });
        const response = await fetch(`${this.options.apiEndpoint}?${params}`);
        const data = await response.json();
        return {
          items: (data.products || data.data || []).map((p) => ({
            id: p.id,
            value: p.id,
            label: p.sku ? `${p.name} (${p.sku})` : p.name,
            name: p.name,
            sku: p.sku,
            description: p.description,
            shortDescription: p.shortDescription,
            image: p.image
          })),
          total: data.total || 0,
          hasMore: data.hasMore || false
        };
      },
      placeholder:
        this.container.querySelector(".text")?.dataset?.placeholder ||
        "Search Product by name or Sku...",
      emptyMessage: "No products found",
      loadingMessage: "Loading products...",
      enableSearch: true,
      enableInfiniteScroll: true,
      pageSize: 20,
      onSelect: (value, text, item) => {
        this.handleProductSelect(item);
        if (this.options.onSelect) {
          this.options.onSelect(value, text, item);
        }
      },
      onReset: () => {
        this.handleProductReset();
        if (this.options.onReset) {
          this.options.onReset();
        }
      }
    });

    this.customSelect.init();

    // Set initial value if exists
    const hiddenInput = this.container.querySelector(".input-field__hidden-value");
    if (hiddenInput?.value) {
      this.loadAndSelectProduct(hiddenInput.value);
    }
  }

  async loadAndSelectProduct(productId) {
    try {
      const response = await fetch(`${this.options.apiEndpoint}?id=${productId}`);
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
    if (this.customSelect) {
      this.customSelect.destroy();
    }
    if (this.productCard) {
      this.productCard.destroy();
    }
  }
}
