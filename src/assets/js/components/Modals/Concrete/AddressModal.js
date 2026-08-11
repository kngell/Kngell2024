import BaseFormModal from "../BaseFormModal";

export default class AddressModal extends BaseFormModal {
  constructor(options = {}) {
    super("AddressModal", {
      modalDataAttr: "modal",
      modalIdentifier: options.modalIdentifier || "checkout-address",
      formId: options.formId || "address-frm",
      triggerSelector:
        options.triggerSelector ||
        '[data-modal="addAddressModal"], [data-modal="editAddressModal"]',
      closeOnSuccess: true,
      reloadOnSuccess: false,
      submitButtonSelector: `button[form="address-frm"]`,
      autoBindTriggers: false,
      ...options
    });

    this.addressType = options.addressType || "shipping";
    this.isLoggedIn = options.isLoggedIn || false;
    this.addressId = options.addressId || null;
    this._isOpening = false;
    this.init();
  }

  bindTriggers() {
    if (this._boundGlobalClickHandler) {
      document.removeEventListener("click", this._boundGlobalClickHandler);
      this._boundGlobalClickHandler = null;
    }

    this._boundGlobalClickHandler = this._handleAddressClick.bind(this);
    document.addEventListener("click", this._boundGlobalClickHandler);

    this.logger.debug("Address modal triggers bound");
  }

  _handleAddressClick(event) {
    const trigger = event.target.closest(this.triggerSelector);
    if (!trigger) return;

    // Prevent double-opening
    if (this._isOpening || this.isRequesting) {
      this.logger.debug("Address modal already opening, ignoring click");
      event.preventDefault();
      event.stopPropagation();
      return;
    }

    // Prevent self-triggering
    if (this.currentModal && this.currentModal.contains(trigger)) {
      this.logger.debug("Trigger is inside modal, ignoring");
      return;
    }

    event.preventDefault();
    event.stopPropagation();

    // Get address type from trigger
    const addressType = trigger.dataset.addressType || "shipping";
    const addressId = trigger.dataset.addressId || null;

    this.addressType = addressType;
    this.addressId = addressId;

    this.openModal(trigger);
  }

  async openModal(trigger) {
    if (this._isOpening) {
      this.logger.warn("Address modal is already opening");
      return;
    }
    let modalUrl;
    if (this.addressId) {
      // Edit mode
      modalUrl = `/checkout/address/edit/${this.addressId}`;
    } else {
      // Add mode
      modalUrl = `/checkout-address/add?address_type=${this.addressType}`;
    }

    this._isOpening = true;
    this.isRequesting = true;
    this.setLoadingState(trigger, true);

    try {
      this.logger.debug(`Fetching address modal from: ${modalUrl}`);

      const result = await this.ajax.get(modalUrl, null, {
        json: true,
        timeout: 10000
      });

      if (result.success === false) {
        throw new Error(result.error || "Failed to load address form");
      }

      // Extract modal HTML from response
      const modalHtml = this._extractModalHtml(result);
      if (!modalHtml) {
        throw new Error("No modal HTML returned");
      }

      this.showModal(modalHtml);
      this.initializeModalComponents();

      if (this.onModalOpened) {
        this.onModalOpened(this.currentModal);
      }

      this.logger.debug(`Address modal opened successfully (${this.addressType})`);
    } catch (error) {
      this.logger.error("Failed to open address modal:", error);
      this._showError(error.message || "Failed to load address form");
    } finally {
      this._isOpening = false;
      this.isRequesting = false;
      this.setLoadingState(trigger, false);
    }
  }

  _extractModalHtml(result) {
    if (result.checkoutUserAddress) {
      return result.checkoutUserAddress;
    }

    this.logger.error("No modal HTML found in response", {
      keys: Object.keys(result)
    });

    return null;
  }

  processFormData(data, formEl) {
    data.address_type = this.addressType;

    // Add address ID if editing
    if (this.addressId) {
      data.id = this.addressId;
    }

    // Add logged in status
    data.is_logged_in = this.isLoggedIn;

    return data;
  }

  onSuccess(result, context) {
    this.logger.debug("Address saved successfully");

    if (this.feedbackChannel) {
      this.feedbackChannel.success(result.message || "Address saved successfully!");
    }

    // Dispatch custom event for other components
    document.dispatchEvent(
      new CustomEvent("address:saved", {
        detail: {
          address: result.data,
          addressType: this.addressType,
          isLoggedIn: this.isLoggedIn,
          result: result
        },
        bubbles: true
      })
    );
  }

  onError(error, context) {
    this.logger.error("Address save error:", error);

    // Show validation errors if present
    if (error.errors) {
      this._showValidationErrors(error.errors);
    } else {
      this._showError(error.message || "Failed to save address");
    }
  }

  _showValidationErrors(errors) {
    if (!this.currentModal) return;

    for (const [field, message] of Object.entries(errors)) {
      const input = this.currentModal.querySelector(`[name="${field}"]`);
      if (input) {
        input.classList.add("is-invalid");

        // Add error message
        const errorContainer = input.closest(".input-field");
        if (errorContainer) {
          const existingError = errorContainer.querySelector(".error-message");
          if (existingError) {
            existingError.textContent = message;
          } else {
            const errorEl = document.createElement("span");
            errorEl.className = "error-message";
            errorEl.textContent = message;
            errorContainer.appendChild(errorEl);
          }
        }
      }
    }
  }

  _showError(message) {
    if (this.feedbackChannel && this.feedbackChannel.error) {
      this.feedbackChannel.error(message);
    } else {
      document.dispatchEvent(
        new CustomEvent("entity:save-error", {
          detail: {
            error: {
              message: message,
              source: "AddressModal"
            }
          }
        })
      );
    }
  }

  closeModal() {
    super.closeModal();
    this._isOpening = false;
    this.isRequesting = false;

    // Clear validation states
    if (this.currentModal) {
      this.currentModal.querySelectorAll(".is-invalid").forEach((el) => {
        el.classList.remove("is-invalid");
      });
      this.currentModal.querySelectorAll(".error-message").forEach((el) => {
        el.remove();
      });
    }
  }

  destroy() {
    if (this._boundGlobalClickHandler) {
      document.removeEventListener("click", this._boundGlobalClickHandler);
      this._boundGlobalClickHandler = null;
    }
    this._isOpening = false;
    super.destroy();
  }
}
