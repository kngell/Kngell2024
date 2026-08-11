import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("ProductCard");

export default class ProductCard {
  constructor(selector, options = {}) {
    this.selector = selector;
    this.container = document.querySelector(selector);

    if (!this.container) {
      logger.error(`ProductCard container not found: ${selector}`);
      return;
    }

    // DOM Elements
    this.productCard = this.container.querySelector(".product-card");
    this.imgContainer = this.container.querySelector(".img-container");
    this.title = this.container.querySelector(".product-info__title");
    this.sku = this.container.querySelector(".product-info__sku");
    this.shortDescription = this.container.querySelector(".product-info__short-description");
    this.removeBtn = this.container.querySelector("[data-remove-product='true']");

    // State
    this.currentProduct = null;

    // Options
    this.options = {
      emptyTitle: options.emptyTitle || "No product selected",
      emptyMessage: options.emptyMessage || "Select a product to view details",
      customSelectSelector: options.customSelectSelector || ".input-field.custom-select",
      autoSync: options.autoSync !== false,
      ...options
    };

    // Bind methods
    this.handleRemove = this.handleRemove.bind(this);
    this.handleCustomSelectChange = this.handleCustomSelectChange.bind(this);
    this.handleCustomSelectReset = this.handleCustomSelectReset.bind(this);

    this.init();
  }

  init() {
    if (this.removeBtn) {
      this.removeBtn.addEventListener("click", this.handleRemove);
    }

    // Auto-subscribe to CustomSelect events if enabled
    if (this.options.autoSync) {
      this.subscribeToCustomSelect();
    }
    // Check if server already rendered product data
    const hasServerData = this.hasServerRenderedData();

    if (hasServerData) {
      this.currentProduct = this.extractProductFromDOM();
      logger.debug("ProductCard using server-rendered data", this.currentProduct);

      if (this.productCard && !this.productCard.classList.contains("is-visible")) {
        this.productCard.classList.add("is-visible");
        this.productCard.classList.remove("is-empty");
      }
    } else {
      // No server data, set empty state
      this.setEmptyState();
    }

    logger.debug("ProductCard initialized", { hasServerData });
  }
  extractProductFromDOM() {
    const productId = this.productCard?.getAttribute("data-product-id") || null;
    const titleText = this.title?.textContent || "";
    const skuText = this.sku?.textContent || "";
    const descText = this.shortDescription?.textContent || "";

    // Extract SKU from "SKU: XXX" format
    const sku = skuText.replace(/^SKU:\s*/, "");

    return {
      value: productId,
      id: productId,
      name: titleText,
      label: titleText,
      sku: sku,
      short_description: descText,
      description: descText,
      image: this.extractImageFromDOM() // ← Calls the correct method
    };
  }
  extractImageFromDOM() {
    if (!this.imgContainer) return null;

    const img = this.imgContainer.querySelector("img");
    if (img && img.src && !img.src.includes("placeholder")) {
      return img.src;
    }

    return null;
  }
  hasServerRenderedData() {
    if (!this.productCard) return false;

    // Check if card has is-visible class (server sets this when product exists)
    const hasVisibleClass = this.productCard.classList.contains("is-visible");

    // Check if there's actual content (not empty state text)
    const titleText = this.title?.textContent || "";
    const skuText = this.sku?.textContent || "";

    const hasContent =
      titleText !== this.options.emptyTitle &&
      skuText !== this.options.emptyMessage &&
      titleText !== "";

    return hasVisibleClass || hasContent;
  }

  /**
   * Subscribe to CustomSelect events on the DOM
   */
  subscribeToCustomSelect() {
    const customSelectContainer = document.querySelector(this.options.customSelectSelector);

    if (!customSelectContainer) {
      logger.warn(`CustomSelect not found: ${this.options.customSelectSelector}`);
      return;
    }

    // Listen for selection changes
    customSelectContainer.addEventListener("select:change", this.handleCustomSelectChange);

    // Listen for reset
    customSelectContainer.addEventListener("select:reset", this.handleCustomSelectReset);

    logger.debug(`Subscribed to CustomSelect events on: ${this.options.customSelectSelector}`);
  }

  /**
   * Handle CustomSelect change events
   */
  handleCustomSelectChange(event) {
    const { value, text, item } = event.detail;
    logger.debug("CustomSelect changed, updating ProductCard:", { value, text, item });

    if (item && item.value) {
      this.render(item);
    } else {
      this.clear();
    }
  }

  /**
   * Handle CustomSelect reset events
   */
  handleCustomSelectReset() {
    logger.debug("CustomSelect reset, clearing ProductCard");
    this.clear();
  }

  /**
   * Set empty state display
   */
  setEmptyState() {
    if (this.productCard) {
      // Remove is-visible if present, add is-empty
      this.productCard.classList.remove("is-visible");
      this.productCard.classList.add("is-empty");
    }

    if (this.title) {
      this.title.textContent = this.options.emptyTitle;
    }

    if (this.sku) {
      this.sku.textContent = this.options.emptyMessage;
    }

    if (this.shortDescription) {
      this.shortDescription.textContent = "";
    }

    if (this.imgContainer) {
      this.renderFallbackImage();
    }

    this.currentProduct = null;
  }

  /**
   * Set visible state (when product is loaded)
   */
  setVisibleState() {
    if (this.productCard) {
      // Remove is-empty if present, add is-visible
      this.productCard.classList.remove("is-empty");
      this.productCard.classList.add("is-visible");
    }
  }

  /**
   * Render product data
   */
  render(productData) {
    if (!productData || !productData.value) {
      this.clear();
      return;
    }

    logger.debug("Rendering product:", productData);

    this.currentProduct = productData;

    // Update card to visible state
    this.setVisibleState();
    console.log(this.productCard);
    // Update title
    if (this.title) {
      this.title.textContent = productData.name || productData.label || "";
    }

    // Update SKU
    if (this.sku) {
      this.sku.textContent = productData.sku ? `SKU: ${productData.sku}` : "";
    }

    // Update description
    if (this.shortDescription) {
      const desc = productData.short_description || productData.description || "";
      this.shortDescription.textContent = desc;
    }

    // Update image
    this.renderImage(productData.image || productData.image_url);

    // Dispatch event that product was rendered
    this.dispatchEvent("product-card:rendered", { product: this.currentProduct });
  }

  /**
   * Render image with SVG support
   */
  renderImage(image) {
    if (!this.imgContainer) return;

    this.imgContainer.innerHTML = "";

    if (!image) {
      this.renderFallbackImage();
      return;
    }

    // Handle SVG string
    if (typeof image === "string" && image.trim().startsWith("<svg")) {
      this.imgContainer.innerHTML = image;
      return;
    }

    // Handle SVG file
    if (typeof image === "string" && image.endsWith(".svg")) {
      fetch(image)
        .then((res) => res.text())
        .then((svg) => {
          if (this.currentProduct) {
            this.imgContainer.innerHTML = svg;
          }
        })
        .catch(() => this.renderFallbackImage());
      return;
    }

    // Handle regular image
    const img = document.createElement("img");
    img.className = "image";
    img.src = image;
    img.alt = this.title?.textContent || "Product Image";
    img.onerror = () => this.renderFallbackImage();
    this.imgContainer.appendChild(img);
  }

  /**
   * Render fallback/placeholder image
   */
  renderFallbackImage() {
    if (!this.imgContainer) return;

    // Use appropriate icon class based on state (empty vs visible)
    const iconClass = this.currentProduct ? "img" : "placeholder";

    this.imgContainer.innerHTML = `
      <svg class="icon ${iconClass}" aria-label="${this.currentProduct ? "Product with main image" : "No Product Selected"}" role="img">
        <use href="/public/assets/img/icons-sprite.svg#icon-image"></use>
      </svg>
    `;
  }

  /**
   * Clear product card (go back to empty state)
   */
  clear() {
    logger.debug("Clearing product card");
    this.setEmptyState();

    // Dispatch event that card was cleared
    this.dispatchEvent("product-card:cleared", { previousProduct: this.currentProduct });

    this.currentProduct = null;
  }

  /**
   * Handle remove button click
   */
  handleRemove(event) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }

    this.clear();

    // Also reset the CustomSelect if it exists
    const customSelectContainer = document.querySelector(this.options.customSelectSelector);
    if (customSelectContainer && customSelectContainer.customSelectInstance) {
      customSelectContainer.customSelectInstance.reset();
    }

    // Dispatch event for external listeners
    this.dispatchEvent("product-card:remove", {
      originalElement: this.container,
      removedProduct: this.currentProduct
    });
  }

  /**
   * Dispatch custom event from container
   */
  dispatchEvent(eventName, detail = {}) {
    const event = new CustomEvent(eventName, {
      bubbles: true,
      detail: detail
    });
    this.container.dispatchEvent(event);
  }

  /**
   * Get current product
   */
  getCurrentProduct() {
    return this.currentProduct;
  }

  /**
   * Check if card has product
   */
  hasProduct() {
    return this.currentProduct !== null;
  }

  /**
   * Unsubscribe from CustomSelect events
   */
  unsubscribeFromCustomSelect() {
    const customSelectContainer = document.querySelector(this.options.customSelectSelector);

    if (customSelectContainer) {
      customSelectContainer.removeEventListener("select:change", this.handleCustomSelectChange);
      customSelectContainer.removeEventListener("select:reset", this.handleCustomSelectReset);
    }
  }

  /**
   * Destroy instance
   */
  destroy() {
    this.unsubscribeFromCustomSelect();

    if (this.removeBtn) {
      this.removeBtn.removeEventListener("click", this.handleRemove);
    }

    this.clear();
    logger.debug("ProductCard destroyed");
  }
}
