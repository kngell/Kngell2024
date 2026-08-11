import BrowserLogger from "js/core/utils/BrowserLogger";

export default class ModalClose {
  constructor(modalElement, options = {}) {
    if (!modalElement) {
      throw new Error("ModalCloseManager requires a modal element");
    }

    this.modal = modalElement;
    this.logger = new BrowserLogger("ModalCloseManager");
    this.isDestroyed = false;

    this.options = {
      closeOnEsc: true,
      closeOnOverlayClick: true,
      onClose: null,
      ...options
    };

    // Bound references for clean removal
    this._onEscKey = this._handleEscKey.bind(this);
    this._onOverlayClick = this._handleOverlayClick.bind(this);
    this._onCloseClick = this._handleCloseClick.bind(this);
    this._onCancelClick = this._handleCancelClick.bind(this);

    this._boundElements = [];

    this._bind();
  }

  // ─── Binding ────────────────────────────────────────────────

  _bind() {
    // ESC key (document-level)
    if (this.options.closeOnEsc) {
      document.addEventListener("keydown", this._onEscKey);
    }

    // Overlay click (the .modal-overlay element itself)
    if (this.options.closeOnOverlayClick) {
      this.modal.addEventListener("click", this._onOverlayClick);
    }

    // Close buttons: [data-modal-close]
    this._bindAll("[data-modal-close]", "click", this._onCloseClick);

    // Cancel buttons: [data-modal-cancel]
    this._bindAll("[data-modal-cancel]", "click", this._onCancelClick);
  }

  /**
   * Bind an event to all matching elements inside the modal.
   * Tracks references for clean teardown.
   */
  _bindAll(selector, event, handler) {
    const elements = this.modal.querySelectorAll(selector);

    elements.forEach((el) => {
      el.addEventListener(event, handler);
      this._boundElements.push({ el, event, handler });
    });
  }

  // ─── Handlers ───────────────────────────────────────────────

  _handleEscKey(e) {
    if (e.key === "Escape" && !this.isDestroyed) {
      e.preventDefault();
      this._requestClose("esc");
    }
  }

  _handleOverlayClick(e) {
    // Only trigger on the overlay itself, not children
    if (e.target === this.modal) {
      this._requestClose("overlay");
    }
  }

  _handleCloseClick(e) {
    e.preventDefault();
    e.stopPropagation();
    this._requestClose("close-button");
  }

  _handleCancelClick(e) {
    e.preventDefault();
    e.stopPropagation();

    // If the cancel button is inside a no-JS form, prevent form submission
    const form = e.target.closest("form");
    if (form) {
      e.stopImmediatePropagation();
    }

    this._requestClose("cancel-button");
  }

  // ─── Close Request ──────────────────────────────────────────

  _requestClose(source) {
    if (this.isDestroyed) return;

    this.logger.debug(`Close requested via: ${source}`);

    if (typeof this.options.onClose === "function") {
      this.options.onClose(source);
    }
  }

  // ─── Dynamic Rebinding ─────────────────────────────────────

  /**
   * Call after dynamically injecting new close/cancel buttons
   * into the modal (e.g., after enhancing server-rendered HTML).
   */
  rebind() {
    this._unbindElements();
    this._bindAll("[data-modal-close]", "click", this._onCloseClick);
    this._bindAll("[data-modal-cancel]", "click", this._onCancelClick);
  }

  // ─── Teardown ───────────────────────────────────────────────

  _unbindElements() {
    this._boundElements.forEach(({ el, event, handler }) => {
      el.removeEventListener(event, handler);
    });
    this._boundElements = [];
  }

  destroy() {
    if (this.isDestroyed) return;
    this.isDestroyed = true;

    document.removeEventListener("keydown", this._onEscKey);
    this.modal.removeEventListener("click", this._onOverlayClick);
    this._unbindElements();

    this.modal = null;
    this.options.onClose = null;

    this.logger.debug("ModalCloseManager destroyed");
  }
}
