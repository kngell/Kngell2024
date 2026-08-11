import AjaxHandler from "js/core/utils/AjaxHandler";
import BrowserLogger from "js/core/utils/BrowserLogger";

export default class ShoppingCartManager {
  static instance = null;

  constructor(options = {}) {
    if (ShoppingCartManager.instance) {
      return ShoppingCartManager.instance;
    }

    this.logger = new BrowserLogger("ShoppingCartManager");
    this.logger.info("Initializing ShoppingCartManager");

    this.options = {
      itemsContainerSelector: ".shopping-cart__items",
      summaryContainerSelector: ".shopping-cart__summary",
      contentContainerSelector: ".shopping-cart__content",
      itemFormSelector: ".handle-item",
      ...options
    };

    this.ajaxHandler = new AjaxHandler();
    this.isProcessing = false;

    this.bindEvents();

    ShoppingCartManager.instance = this;
  }

  bindEvents() {
    document.addEventListener("click", (e) => {
      const form = e.target.closest(this.options.itemFormSelector);
      if (!form) return;
      const button = e.target.closest("button[data-action]");
      if (!button) return;

      e.preventDefault();
      e.stopPropagation();

      const action = button.dataset.action;

      if (action === "minus" || action === "plus" || action === "cancel") {
        this.handleCartAction(form, action);
      }
    });
  }

  async handleCartAction(form, action) {
    if (this.isProcessing) {
      this.logger.warn("Already processing, ignoring duplicate request");
      return;
    }

    const url = form.action;
    const productId = form.querySelector('input[name="product_id"]')?.value;
    if (!productId) {
      this.logger.error("Product ID not found in form");
      return;
    }

    const cartItem = form.closest(".cart-item");
    const quantityInput = cartItem?.querySelector(".quantity-box input");
    let quantity = parseInt(quantityInput?.value || 0);

    if (action === "minus") {
      quantity = Math.max(0, quantity - 1);
      if (quantity === 0) {
        await this.removeItem(form, productId);
        return;
      }
    } else if (action === "plus") {
      quantity = quantity + 1;
    } else if (action === "cancel") {
      await this.removeItem(form, productId);
      return;
    }

    const quantityField = form.querySelector('input[name="quantity"]');
    if (quantityField) {
      quantityField.value = quantity;
    }

    this.isProcessing = true;

    try {
      const formData = new FormData(form);
      if (action === "minus" || action === "plus") {
        formData.set("quantity", quantity.toString());
      }

      this.logger.info(`Processing ${action} action for product ${productId}`);
      const response = await this.ajaxHandler.post(url, formData, {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json"
        }
      });

      this.logger.debug("Response received:", response);

      if (response.success) {
        // ✅ Handle empty cart state - REPLACE the entire content container
        if (response.empty) {
          this.logger.info("Cart is empty, replacing content container with empty state");

          const contentContainer = document.querySelector(this.options.contentContainerSelector);
          if (!contentContainer) {
            this.logger.error(
              `Content container ${this.options.contentContainerSelector} not found`
            );
            return;
          }

          if (response.content) {
            this.logger.debug("Replacing content container with server empty state");
            // ✅ Use outerHTML to replace the entire container
            contentContainer.outerHTML = response.content;
            this.logger.info("Empty state displayed successfully");
          } else {
            this.logger.warn("No content from server, using fallback empty state");
            contentContainer.outerHTML = this.buildEmptyStateHTML();
          }
        } else {
          // ✅ Normal update - update items and summary
          this.logger.debug("Cart has items, updating normally");

          if (response.cartItems) {
            this.updateCartItems(response.cartItems);
          } else {
            this.logger.warn("No cartItems in response");
          }

          if (response.cartSummary) {
            this.updateCartSummary(response.cartSummary);
          } else {
            this.logger.warn("No cartSummary in response");
          }
        }

        // ✅ Always dispatch event for CartBadge
        if (response.cart) {
          this.logger.debug("Dispatching cartUpdated event", response.cart);
          document.dispatchEvent(
            new CustomEvent("cartUpdated", {
              detail: response.cart
            })
          );
        } else {
          this.logger.warn("No cart data in response");
        }

        this.logger.info(`Cart updated successfully after ${action} action`);
      } else {
        this.logger.error(`Cart update failed:`, response.error || "Unknown error");
      }
    } catch (error) {
      this.logger.error(`Error processing ${action}:`, error);
    } finally {
      this.isProcessing = false;
    }
  }

  async removeItem(form, productId) {
    const cartItem = form.closest(".cart-item");
    const removeForm = cartItem?.querySelector(
      'form[data-ajax-form="true"][action*="remove-item"]'
    );

    if (!removeForm) {
      this.logger.error("Remove form not found");
      return;
    }

    this.isProcessing = true;

    try {
      const formData = new FormData(removeForm);

      this.logger.info(`Removing product ${productId} from cart`);

      const response = await this.ajaxHandler.post(removeForm.action, formData, {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json"
        }
      });

      this.logger.debug("Remove response:", response);

      if (response.success) {
        // ✅ Handle empty cart state - REPLACE the entire content container
        if (response.empty) {
          this.logger.info(
            "Cart is empty after removal, replacing content container with empty state"
          );

          const contentContainer = document.querySelector(this.options.contentContainerSelector);
          if (!contentContainer) {
            this.logger.error(
              `Content container ${this.options.contentContainerSelector} not found`
            );
            return;
          }

          if (response.content) {
            this.logger.debug("Replacing content container with server empty state");
            // ✅ Use outerHTML to replace the entire container
            contentContainer.outerHTML = response.content;
            this.logger.info("Empty state displayed successfully");
          } else {
            this.logger.warn("No content from server, using fallback empty state");
            contentContainer.outerHTML = this.buildEmptyStateHTML();
          }
        } else {
          // ✅ Normal update - update items and summary
          this.logger.debug("Cart has items after removal, updating normally");

          if (response.cartItems) {
            this.updateCartItems(response.cartItems);
          } else {
            this.logger.warn("No cartItems in response");
          }

          if (response.cartSummary) {
            this.updateCartSummary(response.cartSummary);
          } else {
            this.logger.warn("No cartSummary in response");
          }
        }

        // ✅ Always dispatch event for CartBadge
        if (response.cart) {
          this.logger.debug("Dispatching cartUpdated event", response.cart);
          document.dispatchEvent(
            new CustomEvent("cartUpdated", {
              detail: response.cart
            })
          );
        } else {
          this.logger.warn("No cart data in response");
        }

        this.logger.info(`Product ${productId} removed successfully`);
      } else {
        this.logger.error(`Failed to remove product:`, response.error || "Unknown error");
      }
    } catch (error) {
      this.logger.error(`Error removing product:`, error);
    } finally {
      this.isProcessing = false;
    }
  }

  updateCartItems(html) {
    const itemsContainer = document.querySelector(this.options.itemsContainerSelector);
    if (!itemsContainer) {
      this.logger.error(`Items container ${this.options.itemsContainerSelector} not found`);
      return;
    }

    itemsContainer.innerHTML = html;
    this.logger.info("Cart items content updated");
  }

  updateCartSummary(html) {
    const summaryContainer = document.querySelector(this.options.summaryContainerSelector);
    if (!summaryContainer) {
      this.logger.error(`Summary container ${this.options.summaryContainerSelector} not found`);
      return;
    }

    summaryContainer.innerHTML = html;
    this.logger.info("Cart summary content updated");
  }

  /**
   * Fallback empty state HTML (used if server doesn't provide content)
   * This replaces the entire .shopping-cart__content container
   */
  buildEmptyStateHTML() {
    return `
      <div class="shopping-cart__empty" style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; padding: 80px 20px; text-align: center; min-height: 500px;">
        <div class="empty-cart-content" style="max-width: 500px; width: 100%;">
          <div class="empty-cart-icon" style="width: 120px; height: 120px; margin: 0 auto 30px; display: block; opacity: 0.5;">🛒</div>
          <h2 style="font-size: 32px; font-weight: 600; margin-bottom: 16px; color: #2d3748;">Your cart is empty</h2>
          <p style="font-size: 18px; color: #718096; margin-bottom: 32px; line-height: 1.6;">Looks like you haven't added any items to your cart yet.</p>
          <a class="btn btn-primary btn-lg" href="/shop" style="display: inline-block; padding: 14px 40px; background-color: #3182ce; color: #ffffff; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; transition: background-color 0.2s;">Start Shopping</a>
        </div>
      </div>
    `;
  }

  refresh() {
    this.logger.info("Refreshing cart");
    window.location.reload();
  }
}
