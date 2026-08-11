import AjaxHandler from "js/core/utils/AjaxHandler";
import BrowserLogger from "js/core/utils/BrowserLogger";

export default class AddToCartHandler {
  static instance = null;
  static MAX_RETRIES = 2;

  constructor(options = {}) {
    if (AddToCartHandler.instance) {
      return AddToCartHandler.instance;
    }

    this.logger = new BrowserLogger("AddToCart");
    this.logger.info("Initializing AddToCartHandler");

    this.options = {
      formSelector: ".add-to-cart-form",
      ...options
    };

    this.ajaxHandler = new AjaxHandler();
    this.isSubmitting = false;

    this.bindEvents();

    AddToCartHandler.instance = this;
  }

  bindEvents() {
    document.addEventListener(
      "submit",
      (e) => {
        const form = e.target.closest(this.options.formSelector);
        if (!form) return;

        e.preventDefault();
        e.stopPropagation();
        this.handleAddToCart(form);
      },
      true
    );
  }

  async handleAddToCart(form, retryCount = 0) {
    if (this.isSubmitting) {
      this.logger.info("Already submitting, ignoring duplicate request");
      return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    this.setButtonLoading(submitBtn, true);
    this.isSubmitting = true;

    try {
      const response = await this.ajaxHandler.postForm(form.action, form, {
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json"
        }
      });

      // ✅ Handle token mismatch - refresh and retry
      if (
        (response.status === 419 || response.code === "token_mismatch") &&
        retryCount < AddToCartHandler.MAX_RETRIES
      ) {
        this.logger.warn("Token mismatch, refreshing and retrying...");

        const refreshed = await this.refreshFormToken(form);

        if (refreshed) {
          this.isSubmitting = false;
          this.setButtonLoading(submitBtn, false);
          await new Promise((resolve) => setTimeout(resolve, 100));
          await this.handleAddToCart(form, retryCount + 1);
          return;
        }

        this.logger.error("Failed to refresh token");
        this.setButtonLoading(submitBtn, false);
        this.isSubmitting = false;
        return;
      }

      // ✅ Handle success
      if (response.success) {
        this.logger.info("Item added to cart successfully");
        document.dispatchEvent(
          new CustomEvent("cartUpdated", {
            detail: response.cart
          })
        );
      } else {
        this.logger.error("Add to cart failed:", response.error);
      }
    } catch (error) {
      this.logger.error("Add to cart error:", error);
    } finally {
      this.setButtonLoading(submitBtn, false);
      this.isSubmitting = false;
    }
  }

  /**
   * Refresh CSRF token for a specific form
   */
  async refreshFormToken(form) {
    try {
      const tokenResponse = await this.ajaxHandler.get("/csrf-token");

      // ✅ The server returns the token as 'token' in the response
      if (tokenResponse && tokenResponse.token) {
        const csrfToken = tokenResponse.token;

        // ✅ Update the specific form's CSRF token input
        const tokenInputs = form.querySelectorAll('input[name="csrfToken"]');
        if (tokenInputs.length > 0) {
          tokenInputs.forEach((input) => {
            input.value = csrfToken;
          });
        } else {
          // ✅ If no csrfToken input exists, create one
          const newInput = document.createElement("input");
          newInput.type = "hidden";
          newInput.name = "csrfToken";
          newInput.value = csrfToken;
          form.prepend(newInput);
        }

        // ✅ Update any other token-related inputs
        form.querySelectorAll('input[type="hidden"]').forEach((input) => {
          if (
            input.name &&
            (input.name.toLowerCase().includes("token") || input.name === "csrfToken")
          ) {
            input.value = csrfToken;
          }
        });

        window.csrfToken = csrfToken;
        return true;
      }

      return false;
    } catch (error) {
      this.logger.error("Error refreshing token:", error);
      return false;
    }
  }

  setButtonLoading(button, loading) {
    if (!button) return;

    if (loading) {
      button.disabled = true;
      button.classList.add("loading");
      button.dataset.originalHtml = button.innerHTML;
      button.innerHTML = `<span class="spinner"></span> <span class="btn__label">Adding...</span>`;
    } else {
      button.disabled = false;
      button.classList.remove("loading");
      if (button.dataset.originalHtml) {
        button.innerHTML = button.dataset.originalHtml;
      }
    }
  }
}
