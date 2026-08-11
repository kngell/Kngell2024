import AjaxHandler from "js/core/utils/AjaxHandler";
import BrowserLogger from "js/core/utils/BrowserLogger";

export default class CartBadge {
  static instance = null;

  constructor(options = {}) {
    if (CartBadge.instance) {
      return CartBadge.instance;
    }

    this.logger = new BrowserLogger("CartBadge");
    this.logger.info("Initializing CartBadge");

    this.options = {
      cartCountSelector: ".menu__actions--cart-item-count",
      cartIconSelector: ".cart-icon",
      cartContainerSelector: ".menu__actions--cart",
      ...options
    };

    this.ajaxHandler = new AjaxHandler();
    this.countElement = document.querySelector(this.options.cartCountSelector);
    this.cartContainer = document.querySelector(this.options.cartContainerSelector);
    this.cartIcon = document.querySelector(this.options.cartIconSelector);
    this.isLoading = false;
    this.lastCount = -1;

    this.bindEvents();
    this.loadCartCount();

    CartBadge.instance = this;
  }

  bindEvents() {
    document.addEventListener("cartUpdated", (event) => {
      this.logger.info("Cart update event received", event.detail);
      this.updateBadge(event.detail);
    });

    document.addEventListener("turbo:load", () => {
      this.refreshElements();
      this.loadCartCount();
    });

    document.addEventListener("DOMContentLoaded", () => {
      this.loadCartCount();
    });
  }

  refreshElements() {
    this.countElement = document.querySelector(this.options.cartCountSelector);
    this.cartContainer = document.querySelector(this.options.cartContainerSelector);
    this.cartIcon = document.querySelector(this.options.cartIconSelector);
  }

  async loadCartCount() {
    if (this.isLoading) {
      this.logger.info("Cart count already loading, skipping duplicate request");
      return;
    }

    this.isLoading = true;

    try {
      this.logger.debug("Fetching cart count");
      const response = await this.ajaxHandler.get("/cart", { _ajax: "1" });

      if (response.success) {
        this.updateBadge(response.cart);
        this.logger.debug("Cart count loaded successfully");
      } else {
        this.logger.warn("Failed to load cart count:", response.error);
      }
    } catch (error) {
      this.logger.error("Error loading cart count:", error);
    } finally {
      this.isLoading = false;
    }
  }

  updateBadge(cartData) {
    if (!cartData) return;

    const count = cartData.totalCount || 0;

    if (this.lastCount === count) {
      this.logger.debug("Cart count unchanged, skipping update");
      return;
    }

    this.logger.debug(`Updating cart badge: ${this.lastCount} → ${count}`);

    if (this.countElement) {
      this.countElement.textContent = count;
      this.countElement.classList.toggle("visible", count > 0);
    }

    if (this.cartContainer) {
      this.cartContainer.dataset.count = count;
    }

    if (this.lastCount !== -1 && count > this.lastCount) {
      this.animateCartIcon();
    }

    this.lastCount = count;
  }

  animateCartIcon() {
    if (this.cartIcon) {
      this.cartIcon.classList.add("bounce");
      setTimeout(() => {
        this.cartIcon.classList.remove("bounce");
      }, 500);
    }
  }

  refresh() {
    this.logger.info("Forcing cart badge refresh");
    this.loadCartCount();
  }

  getCount() {
    return this.lastCount;
  }
}
