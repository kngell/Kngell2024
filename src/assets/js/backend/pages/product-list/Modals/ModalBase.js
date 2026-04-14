import BrowserLogger from "js/core/utils/BrowserLogger";

export default class ModalBase {
  constructor(modalName = "ModalBase", options = {}) {
    this.logger = new BrowserLogger(modalName);
    this.modalContainer = null;
    this.currentModal = null;
    this.isAnimating = false;
    this.modalName = modalName;

    this.options = {
      closeOnEsc: true,
      closeOnOverlayClick: true,
      preventBodyScroll: true,
      autoFocus: true,
      animationDuration: 300,
      ...options,
    };
  }

  init() {
    this.createModalContainer();
    this.bindGlobalEvents();
    this.logger.success(`${this.modalName} initialized`);
  }

  createModalContainer() {
    if (!document.querySelector(".modals-container")) {
      this.modalContainer = document.createElement("div");
      this.modalContainer.className = "modals-container";
      document.body.appendChild(this.modalContainer);
    } else {
      this.modalContainer = document.querySelector(".modals-container");
    }
  }

  bindGlobalEvents() {
    // Close on ESC key
    if (this.options.closeOnEsc) {
      document.addEventListener("keydown", this.handleEscapeKey.bind(this));
    }
  }

  handleEscapeKey(e) {
    if (e.key === "Escape" && this.currentModal) {
      this.closeCurrentModal();
    }
  }

  showModal(htmlContent) {
    this.closeCurrentModal();

    this.modalContainer.innerHTML = htmlContent;
    this.currentModal = this.modalContainer.querySelector(".modal-overlay");

    if (!this.currentModal) {
      console.log(htmlContent);
      throw new Error("Modal HTML structure not found in response");
    }

    // Force reflow
    this.currentModal.offsetHeight;

    // Show modal
    this.currentModal.classList.add("active");

    if (this.options.preventBodyScroll) {
      this.lockBodyScroll(true);
    }

    this.bindModalCloseEvents();

    this.logger.info("Modal displayed");

    // Focus management
    if (this.options.autoFocus) {
      setTimeout(() => this.focusFirstInteractiveElement(), 100);
    }

    return this.currentModal;
  }

  bindModalCloseEvents() {
    if (!this.currentModal) return;

    // Overlay click
    if (this.options.closeOnOverlayClick) {
      this.currentModal.addEventListener("click", (e) => {
        if (e.target === this.currentModal && !this.isAnimating) {
          this.closeCurrentModal();
        }
      });
    }

    // Close button
    const closeBtn = this.currentModal.querySelector(".modal-close-btn, [data-modal-close]");
    if (closeBtn) {
      closeBtn.addEventListener("click", (e) => {
        e.preventDefault();
        this.closeCurrentModal();
      });
    }
  }

  closeCurrentModal() {
    if (this.currentModal && !this.isAnimating) {
      this.closeModal(this.currentModal);
    }
  }

  closeModal(modal) {
    this.isAnimating = true;
    modal.classList.remove("active");

    const onTransitionEnd = () => {
      if (modal && modal.parentNode) {
        modal.remove();
      }
      this.currentModal = null;

      if (this.options.preventBodyScroll) {
        this.lockBodyScroll(false);
      }

      this.isAnimating = false;
      this.logger.info("Modal closed");

      // Clean up
      modal.removeEventListener("transitionend", onTransitionEnd);
    };

    modal.addEventListener("transitionend", onTransitionEnd);

    // Fallback
    setTimeout(() => {
      if (this.currentModal === modal) {
        onTransitionEnd();
      }
    }, this.options.animationDuration + 100);
  }

  lockBodyScroll(lock) {
    if (lock) {
      document.body.classList.add("modal-open");
    } else {
      document.body.classList.remove("modal-open");
    }
  }

  focusFirstInteractiveElement() {
    const focusableSelectors = [
      "button:not([disabled])",
      "[href]",
      "input:not([disabled])",
      "select:not([disabled])",
      "textarea:not([disabled])",
      '[tabindex]:not([tabindex="-1"])',
    ].join(", ");

    const focusable = this.currentModal?.querySelector(focusableSelectors);
    if (focusable) {
      focusable.focus();
      focusable.classList.add("initial-focus");
      setTimeout(() => focusable.classList.remove("initial-focus"), 300);
    }
  }

  showNotification(message, type = "success", duration = 3000) {
    const notification = document.createElement("div");
    notification.className = type === "success" ? "success-notification" : "modal-error";
    notification.innerHTML = `
      <svg class="icon" viewBox="0 0 24 24">
        <path d="${type === "success" ? "M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" : "M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"}"/>
      </svg>
      <span>${message}</span>
    `;

    if (!document.querySelector(".success-notification")) {
      notification.style.cssText =
        type === "success"
          ? "background: #efffee; color: #0a0; padding: 12px 20px; border-radius: 6px; border: 1px solid #cfc;"
          : "background: #fee; color: #c00; padding: 12px 20px; border-radius: 6px; border: 1px solid #fcc;";
    }

    document.body.appendChild(notification);

    setTimeout(() => {
      notification.classList.add("slide-out");
      setTimeout(() => notification.remove(), 300);
    }, duration);
  }

  destroy() {
    this.closeCurrentModal();

    // Remove event listeners
    if (this.options.closeOnEsc) {
      document.removeEventListener("keydown", this.handleEscapeKey);
    }

    this.logger.info(`${this.modalName} destroyed`);
  }
}
