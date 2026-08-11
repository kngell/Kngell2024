import BrowserLogger from "js/core/utils/BrowserLogger";
import { getModalRegistry } from "js/components/Modals/ModalRegistry";
import ContentHandler from "js/core/handlers/ContentHandler";

export default class CheckoutManager {
  constructor(options = {}) {
    this.logger = new BrowserLogger("CheckoutManager");

    // ─── Bind handlers early ──────────────────────────────────
    this._handleModalTrigger = this._handleModalTrigger.bind(this);
    this._handleFormSubmit = this._handleFormSubmit.bind(this);
    this._handleAddressSaved = this._handleAddressSaved.bind(this);
    this._handleBillingToggle = this._handleBillingToggle.bind(this);

    // ─── Options ──────────────────────────────────────────────
    this.options = {
      flashSelector: options.flashSelector || ".checkout__body",
      containerSelector: options.containerSelector || ".checkout__body",
      modalIdentifier: options.modalIdentifier || "checkout-address",
      formId: options.formId || "address-frm",
      triggerSelector:
        options.triggerSelector ||
        '[data-modal="addAddressModal"], [data-modal="editAddressModal"]',
      lazyLoadModals: options.lazyLoadModals !== false,
      channelStrategy: options.channelStrategy || "flash",
      notificationContainerId: options.notificationContainerId || "checkout-notifications",
      ...options
    };

    // ─── ContentHandler ──────────────────────────────────────
    this.contentHandler = new ContentHandler({
      componentId: "CheckoutManager_" + Date.now(),
      flashSelector: this.options.flashSelector,
      containerClass: "flash-container",
      position: "prepend",
      channelStrategy: this.options.channelStrategy,
      notificationContainerId: this.options.notificationContainerId,
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

    // ─── Properties ────────────────────────────────────────────
    this.modalRegistry = getModalRegistry();
    this._modalPromises = new Map();
    this._isInitialized = false;
    this.container = null;
    this._preloaded = false;
  }

  // ─── Initialization ─────────────────────────────────────────

  async init() {
    if (this._isInitialized) return this;

    this.logger.debug("Initializing CheckoutManager");

    // Wait for DOM ready
    if (document.readyState === "loading") {
      await new Promise((resolve) => document.addEventListener("DOMContentLoaded", resolve));
    }

    // Get container
    this.container = document.querySelector(this.options.containerSelector) || document;

    // Preload modals if not lazy loading
    if (!this.options.lazyLoadModals) {
      await this.preloadModals(["address"]);
    }

    // Initialize modals
    await this.initModals();

    // Initialize billing toggle
    this.initBillingToggle();

    // Bind events
    this.bindEvents();

    this._isInitialized = true;
    this.logger.success("CheckoutManager initialized");
    return this;
  }

  // ─── Modals ─────────────────────────────────────────────────

  async initModals() {
    const target = this.container || document;

    // Remove existing listeners to prevent duplicates
    target.removeEventListener("click", this._handleModalTrigger);
    target.removeEventListener("submit", this._handleFormSubmit);

    // Add listeners
    target.addEventListener("click", this._handleModalTrigger);
    target.addEventListener("submit", this._handleFormSubmit);

    this.logger.debug("Modal event listeners bound");
  }

  _handleModalTrigger = async (event) => {
    const trigger = event.target.closest(
      '[data-modal="addAddressModal"], [data-modal="editAddressModal"]'
    );
    if (!trigger) return;

    event.preventDefault();
    event.stopPropagation();

    const addressType = trigger.dataset.addressType || "shipping";
    const addressId = trigger.dataset.addressId || null;

    this.logger.debug(`Address modal trigger: ${addressId ? "edit" : "add"} (${addressType})`);

    try {
      const modal = await this.getModal("address");

      // Set address type and ID on the modal
      modal.addressType = addressType;
      modal.addressId = addressId;

      await modal.openModal(trigger);
    } catch (error) {
      this.logger.error("Failed to open address modal:", error);
      this.contentHandler.showFlash("error", "Failed to open address form. Please try again.");
    }
  };

  _handleFormSubmit = async (event) => {
    const form = event.target;

    // Check if this is our address form
    if (form.id !== this.options.formId) return;

    // Check if form has modal trigger
    const trigger = form.querySelector(
      '[data-modal="addAddressModal"], [data-modal="editAddressModal"]'
    );
    if (!trigger) return;

    event.preventDefault();
    event.stopPropagation();

    this.logger.debug("Address form submitted");

    try {
      const modal = await this.getModal("address");

      // Process form data
      const formData = new FormData(form);

      // Let modal handle the submission
      if (modal.handleFormSubmit) {
        await modal.handleFormSubmit(form, formData);
      } else {
        // Default: submit via AJAX
        const action = form.getAttribute("action") || "/checkout/address/save";
        const data = Object.fromEntries(formData);

        const result = await this.contentHandler.post(action, data, {
          operation: "save",
          type: "address"
        });

        if (result.success) {
          modal.closeModal();
          this.contentHandler.showFlash("success", result.message || "Address saved successfully!");
          this._handleAddressSaved(result);
        } else {
          this.contentHandler.showFlash("error", result.error || "Failed to save address");
        }
      }
    } catch (error) {
      this.logger.error("Failed to save address:", error);
      this.contentHandler.showFlash("error", error.message || "Failed to save address");
    }
  };

  async getModal(type) {
    const key = `modal_${type}`;
    if (this._modalPromises.has(key)) return this._modalPromises.get(key);

    this.logger.debug(`Getting ${type} modal`);

    const promise = this.modalRegistry.getModal(type, {
      modalIdentifier: this.options.modalIdentifier,
      formId: this.options.formId,
      triggerSelector: this.options.triggerSelector,
      closeOnSuccess: true,
      reloadOnSuccess: false,
      onSuccess: (result, context) => {
        this.logger.debug(`✅ ${type} saved successfully`, result);
        this._handleAddressSaved(result);
      },
      onError: (error, context) => {
        this.logger.error(`❌ ${type} save failed:`, error);
        this.contentHandler.showFlash("error", error.message || "Failed to save address");
      },
      onModalOpened: () => {
        this.logger.debug(`${type} modal opened`);
      },
      onModalClosed: () => {
        this.logger.debug(`${type} modal closed`);
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
      this.logger.debug(`Preloaded modals: ${types.join(", ")}`);
    } catch (error) {
      this.logger.warn("Failed to preload modals:", error);
    }
  }

  // ─── Billing Toggle ─────────────────────────────────────────

  initBillingToggle() {
    const toggle = document.getElementById("billingSameAsShipping");
    if (!toggle) return;

    // Remove existing listener
    toggle.removeEventListener("change", this._handleBillingToggle);
    toggle.addEventListener("change", this._handleBillingToggle);

    // Initialize state
    this._handleBillingToggle({ target: toggle });

    this.logger.debug("Billing toggle initialized");
  }

  _handleBillingToggle = (event) => {
    const toggle = event.target;
    const billingSection = document.querySelector(".billing-section__fields");
    const billingOptions = document.querySelector(".billing-section__options");

    if (!billingSection && !billingOptions) return;

    if (toggle.checked) {
      // Same as shipping - hide billing fields
      if (billingSection) billingSection.style.display = "none";
      if (billingOptions) billingOptions.style.display = "none";

      // Copy shipping data to billing fields
      this._copyShippingToBilling();

      this.logger.debug("Billing set to same as shipping");
    } else {
      // Different billing - show billing fields
      if (billingSection) billingSection.style.display = "block";
      if (billingOptions) billingOptions.style.display = "block";

      this.logger.debug("Billing is different from shipping");
    }
  };

  _copyShippingToBilling() {
    // Get shipping values
    const shippingFields = {
      firstName: document.querySelector('[name="shippingFirstName"]'),
      lastName: document.querySelector('[name="shippingLastName"]'),
      company: document.querySelector('[name="shippingCompany"]'),
      phone: document.querySelector('[name="shippingPhone"]'),
      email: document.querySelector('[name="shippingEmail"]'),
      address1: document.querySelector('[name="shippingAddressLine1"]'),
      address2: document.querySelector('[name="shippingAddressLine2"]'),
      city: document.querySelector('[name="shippingCity"]'),
      state: document.querySelector('[name="shippingState"]'),
      postalCode: document.querySelector('[name="shippingPostalCode"]'),
      country: document.querySelector('[name="shippingCountry"]')
    };

    // Fill billing fields
    const billingFields = {
      firstName: document.querySelector('[name="billingFirstName"]'),
      lastName: document.querySelector('[name="billingLastName"]'),
      company: document.querySelector('[name="billingCompany"]'),
      phone: document.querySelector('[name="billingPhone"]'),
      email: document.querySelector('[name="billingEmail"]'),
      address1: document.querySelector('[name="billingAddressLine1"]'),
      address2: document.querySelector('[name="billingAddressLine2"]'),
      city: document.querySelector('[name="billingCity"]'),
      state: document.querySelector('[name="billingState"]'),
      postalCode: document.querySelector('[name="billingPostalCode"]'),
      country: document.querySelector('[name="billingCountry"]')
    };

    for (const [key, shippingField] of Object.entries(shippingFields)) {
      const billingField = billingFields[key];
      if (shippingField && billingField) {
        billingField.value = shippingField.value || "";
      }
    }
  }

  // ─── Address Saved Handler ──────────────────────────────────

  _handleAddressSaved(result) {
    this.logger.debug("Address saved, updating UI", result);

    // Dispatch event for other components
    document.dispatchEvent(
      new CustomEvent("address:saved", {
        detail: {
          address: result.data || result.address_data,
          addressType: result.address_type || "shipping",
          isEdit: result.id ? true : false,
          result: result
        },
        bubbles: true
      })
    );

    // If we have address data, update the UI
    if (result.data) {
      this.updateAddressInUI(result.data);
    }
  }

  // ─── UI Updates ─────────────────────────────────────────────

  updateAddressInUI(addressData) {
    // Find address cards container
    const container = document.querySelector(".address-section__saved");
    if (!container) {
      this.logger.debug("Address container not found, skipping UI update");
      return;
    }

    // If we're in edit mode, update existing card
    if (addressData.id) {
      const card = container.querySelector(`[data-address-id="${addressData.id}"]`);
      if (card) {
        this._updateAddressCard(card, addressData);
        this.logger.debug(`Updated address card for ID ${addressData.id}`);
        return;
      }
    }

    // Otherwise, add new card (if we have a template)
    const template = container.querySelector(".address-card-template");
    if (template) {
      const newCard = this._createAddressCard(addressData, template);
      if (newCard) {
        container.appendChild(newCard);
        this.logger.debug("Added new address card");
      }
    }
  }

  _updateAddressCard(card, data) {
    // Update name
    const nameEl = card.querySelector(".address-card__name");
    if (nameEl) {
      nameEl.textContent = `${data.first_name || ""} ${data.last_name || ""}`.trim();
    }

    // Update address
    const addressEl = card.querySelector(".address-card__address");
    if (addressEl) {
      const parts = [data.address1, data.address2, data.city, data.state, data.postal_code].filter(
        Boolean
      );
      addressEl.innerHTML = parts.join("<br>");
    }

    // Update contact
    const contactEl = card.querySelector(".address-card__contact");
    if (contactEl) {
      contactEl.innerHTML = `${data.phone || ""}<br>${data.email || ""}`;
    }

    // Update tags
    const tagsContainer = card.querySelector(".address-card__header");
    if (tagsContainer) {
      // Remove existing tags
      tagsContainer.querySelectorAll(".address-card__tag").forEach((tag) => tag.remove());

      // Add default tag if applicable
      if (data.is_default_shipping || data.is_default_billing) {
        const defaultTag = document.createElement("span");
        defaultTag.className = "address-card__tag address-card__tag--default";
        defaultTag.textContent = "Default";
        tagsContainer.appendChild(defaultTag);
      }

      // Add shipping/billing tags
      if (data.is_default_shipping) {
        const shippingTag = document.createElement("span");
        shippingTag.className = "address-card__tag address-card__tag--shipping";
        shippingTag.textContent = "Shipping";
        tagsContainer.appendChild(shippingTag);
      }

      if (data.is_default_billing) {
        const billingTag = document.createElement("span");
        billingTag.className = "address-card__tag address-card__tag--billing";
        billingTag.textContent = "Billing";
        tagsContainer.appendChild(billingTag);
      }
    }
  }

  _createAddressCard(data, template) {
    const clone = template.cloneNode(true);
    clone.removeAttribute("class");
    clone.className = "address-card";
    clone.dataset.addressId = data.id;

    // Update radio
    const radio = clone.querySelector(".address-card__radio-input");
    if (radio) {
      radio.id = `address-${data.id}`;
      radio.value = data.id;
    }

    // Update label for attribute
    const label = clone.querySelector(".address-card__content");
    if (label) {
      label.setAttribute("for", `address-${data.id}`);
    }

    // Update actions
    const editBtn = clone.querySelector('[data-modal="editAddressModal"]');
    if (editBtn) {
      editBtn.dataset.addressId = data.id;
    }

    const deleteBtn = clone.querySelector('[data-modal="deleteConfirmModal"]');
    if (deleteBtn) {
      deleteBtn.dataset.addressId = data.id;
    }

    // Fill data
    this._updateAddressCard(clone, data);

    return clone;
  }

  // ─── Events ─────────────────────────────────────────────────

  bindEvents() {
    // Listen for address saved events from other components
    document.removeEventListener("address:saved", this._handleAddressSaved);
    document.addEventListener("address:saved", this._handleAddressSaved);

    this.logger.debug("Events bound");
  }

  // ─── Public Methods ─────────────────────────────────────────

  /**
   * Open address modal programmatically
   */
  async openAddressModal(addressType = "shipping", addressId = null) {
    try {
      const modal = await this.getModal("address");
      modal.addressType = addressType;
      modal.addressId = addressId;

      // Create a fake trigger for the modal
      const fakeTrigger = document.createElement("button");
      fakeTrigger.dataset.addressType = addressType;
      if (addressId) {
        fakeTrigger.dataset.addressId = addressId;
      }

      await modal.openModal(fakeTrigger);
      return true;
    } catch (error) {
      this.logger.error("Failed to open address modal:", error);
      this.contentHandler.showFlash("error", "Failed to open address form");
      return false;
    }
  }

  /**
   * Refresh address list
   */
  refreshAddresses() {
    this.logger.debug("Refreshing address list");
    // Implementation depends on your backend API
  }

  // ─── Cleanup ─────────────────────────────────────────────────

  destroy() {
    // Remove event listeners
    document.removeEventListener("address:saved", this._handleAddressSaved);

    // Remove billing toggle listener
    const toggle = document.getElementById("billingSameAsShipping");
    if (toggle) {
      toggle.removeEventListener("change", this._handleBillingToggle);
    }

    if (this.container) {
      this.container.removeEventListener("click", this._handleModalTrigger);
      this.container.removeEventListener("submit", this._handleFormSubmit);
    }

    // Clear modal promises
    this._modalPromises.clear();

    // Destroy content handler
    if (this.contentHandler) {
      this.contentHandler.destroy();
      this.contentHandler = null;
    }

    this.container = null;
    this._isInitialized = false;

    this.logger.debug("CheckoutManager destroyed");
  }
}
