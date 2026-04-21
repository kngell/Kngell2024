import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("CardManager");

export default class CardManager {
  constructor(container, options = {}) {
    this.container = container;
    this.options = {
      cardSelector: ".product-card",
      cardClass: "product-card",
      emptyClass: "is-empty",
      visibleClass: "is-visible",
      renderer: null,
      onRender: null,
      onClear: null,
      onRemove: null,
      ...options
    };

    this.card = null;
    this.removeHandler = null;
  }

  init() {
    this.card = this.container.querySelector(this.options.cardSelector);
    if (!this.card) {
      logger.warn(`Card not found: ${this.options.cardSelector}`);
      return;
    }

    // Setup remove button
    const removeBtn = this.card.querySelector("[data-remove-card], .product-card__remove");
    if (removeBtn) {
      this.removeHandler = (e) => {
        e.preventDefault();
        this.clear();
        if (this.options.onRemove) {
          this.options.onRemove();
        }
      };
      removeBtn.addEventListener("click", this.removeHandler);
    }
  }

  render(data) {
    if (!this.card) return;

    if (this.options.renderer) {
      this.options.renderer(this.card, data);
    } else {
      this.renderDefault(data);
    }

    this.card.classList.remove(this.options.emptyClass);
    this.card.classList.add(this.options.visibleClass);

    if (this.options.onRender) {
      this.options.onRender(data);
    }

    const event = new CustomEvent("card:render", { detail: data, bubbles: true });
    this.container.dispatchEvent(event);
  }

  renderDefault(data) {
    const img = this.card.querySelector(".img-container img");
    if (img && data.image) {
      img.src = data.image;
      img.alt = data.name || "";
    }

    const title = this.card.querySelector(".product-info__title");
    if (title && data.name) {
      title.textContent = data.name;
    }

    const sku = this.card.querySelector(".product-info__sku");
    if (sku && data.sku) {
      sku.textContent = data.sku;
    }

    const description = this.card.querySelector(".product-info__short-description");
    if (description && (data.shortDescription || data.description)) {
      description.textContent = data.shortDescription || data.description;
    }
  }

  clear() {
    if (!this.card) return;

    this.card.classList.add(this.options.emptyClass);
    this.card.classList.remove(this.options.visibleClass);

    // Clear default content
    const img = this.card.querySelector(".img-container img");
    if (img) {
      img.src = "";
      img.alt = "";
    }

    const title = this.card.querySelector(".product-info__title");
    if (title) title.textContent = "";

    const sku = this.card.querySelector(".product-info__sku");
    if (sku) sku.textContent = "";

    const description = this.card.querySelector(".product-info__short-description");
    if (description) description.textContent = "";

    if (this.options.onClear) {
      this.options.onClear();
    }

    const event = new CustomEvent("card:clear", { bubbles: true });
    this.container.dispatchEvent(event);
  }

  destroy() {
    if (this.removeHandler) {
      const removeBtn = this.card?.querySelector("[data-remove-card], .product-card__remove");
      if (removeBtn) {
        removeBtn.removeEventListener("click", this.removeHandler);
      }
    }
    this.card = null;
    logger.debug("CardManager destroyed");
  }
}
