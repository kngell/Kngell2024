import BrowserLogger from "js/core/utils/BrowserLogger";
import ModalCloseManager from "./ModalClose";

export default class ModalBase {
  constructor(modalName = "ModalBase", options = {}) {
    this.logger = new BrowserLogger(modalName);
    this.modalContainer = null;
    this.currentModal = null;
    this.closeManager = null;
    this.isAnimating = false;
    this.modalName = modalName;

    this.options = {
      closeOnEsc: true,
      closeOnOverlayClick: true,
      preventBodyScroll: true,
      autoFocus: true,
      animationDuration: 300,
      ...options
    };
  }

  init() {
    this.ensureModalContainer();
    this.logger.success(`${this.modalName} initialized`);
  }

  // ─── Container ──────────────────────────────────────────────

  ensureModalContainer() {
    const existing = document.querySelector(".modals-container");

    if (existing) {
      this.modalContainer = existing;
    } else {
      this.modalContainer = document.createElement("div");
      this.modalContainer.className = "modals-container";
      document.body.appendChild(this.modalContainer);
    }
  }

  // ─── Show ───────────────────────────────────────────────────

  showModal(htmlContent) {
    if (this.currentModal) {
      this._forceCleanup();
    }

    this.modalContainer.innerHTML = htmlContent;
    this.currentModal = this.modalContainer.querySelector(".modal-overlay");

    if (!this.currentModal) {
      throw new Error("Modal HTML structure not found in response");
    }

    this.closeManager = new ModalCloseManager(this.currentModal, {
      closeOnEsc: this.options.closeOnEsc,
      closeOnOverlayClick: this.options.closeOnOverlayClick,
      onClose: (source) => this.closeCurrentModal(source)
    });

    // Force reflow then activate
    void this.currentModal.offsetHeight;
    this.currentModal.classList.add("active");

    if (this.options.preventBodyScroll) {
      this.lockBodyScroll(true);
    }

    if (this.options.autoFocus) {
      setTimeout(() => this.focusFirstInteractiveElement(), 100);
    }

    this.logger.info("Modal displayed");
    return this.currentModal;
  }

  /**
   * Adopt a server-rendered modal already in the DOM.
   * Returns false if no modal found.
   */
  adoptExistingModal(selector) {
    const existing = document.querySelector(selector);
    if (!existing) return false;

    this.currentModal = existing;

    if (this.modalContainer && existing.parentNode !== this.modalContainer) {
      this.modalContainer.appendChild(existing);
    }

    this.closeManager = new ModalCloseManager(this.currentModal, {
      closeOnEsc: this.options.closeOnEsc,
      closeOnOverlayClick: this.options.closeOnOverlayClick,
      onClose: (source) => this.closeCurrentModal(source)
    });

    if (this.options.preventBodyScroll) {
      this.lockBodyScroll(true);
    }

    this.logger.info("Adopted existing modal");
    return true;
  }

  // ─── Close ──────────────────────────────────────────────────

  closeCurrentModal(source = "programmatic") {
    if (!this.currentModal || this.isAnimating) {
      return;
    }

    this.logger.debug(`Closing modal (source: ${source})`);

    this.isAnimating = true;

    // Destroy close manager FIRST to prevent double-fires
    if (this.closeManager) {
      this.closeManager.destroy();
      this.closeManager = null;
    }

    this.currentModal.classList.remove("active");

    this._awaitTransition(this.currentModal).then((modal) => {
      this._removeModal(modal);
      this.isAnimating = false;
    });
  }

  /**
   * Wait for CSS transition, with fallback timeout.
   * Resolves exactly once.
   */
  _awaitTransition(modal) {
    return new Promise((resolve) => {
      let resolved = false;

      const done = () => {
        if (resolved) return;
        resolved = true;
        modal.removeEventListener("transitionend", onEnd);
        clearTimeout(fallback);
        resolve(modal);
      };

      const onEnd = (e) => {
        if (e.target === modal) done();
      };

      modal.addEventListener("transitionend", onEnd);

      const fallback = setTimeout(done, this.options.animationDuration + 100);
    });
  }

  _removeModal(modal) {
    if (modal?.parentNode) {
      modal.remove();
    }

    this.currentModal = null;

    if (this.options.preventBodyScroll) {
      this.lockBodyScroll(false);
    }

    this.logger.info("Modal closed and removed");
  }

  /**
   * Synchronous forced cleanup — used when replacing a modal
   * without waiting for animation.
   */
  _forceCleanup() {
    if (this.closeManager) {
      this.closeManager.destroy();
      this.closeManager = null;
    }

    if (this.currentModal?.parentNode) {
      this.currentModal.remove();
    }

    this.currentModal = null;
    this.isAnimating = false;

    if (this.options.preventBodyScroll) {
      this.lockBodyScroll(false);
    }
  }

  // ─── Utilities ──────────────────────────────────────────────

  lockBodyScroll(lock) {
    document.body.classList.toggle("modal-open", lock);
  }

  focusFirstInteractiveElement() {
    const selectors = [
      "button:not([disabled])",
      "[href]",
      "input:not([disabled]):not([type='hidden'])",
      "select:not([disabled])",
      "textarea:not([disabled])",
      '[tabindex]:not([tabindex="-1"])'
    ].join(", ");

    const focusable = this.currentModal?.querySelector(selectors);
    focusable?.focus();
  }

  // ─── Destroy ────────────────────────────────────────────────

  destroy() {
    this._forceCleanup();
    this.logger.info(`${this.modalName} destroyed`);
  }
}
