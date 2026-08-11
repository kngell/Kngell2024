import BaseFormModal from "../BaseFormModal";

export default class LoginModal extends BaseFormModal {
  constructor(options = {}) {
    super("LoginModal", {
      modalDataAttr: "modal",
      modalIdentifier: options.modalIdentifier || "login-modal",
      formId: options.formId || "login-frm",
      triggerSelector:
        options.triggerSelector || '[data-action="login"], [data-target="login-modal"]',
      closeOnSuccess: true,
      reloadOnSuccess: true,
      submitButtonSelector: 'button[form="login-frm"]',
      autoBindTriggers: true, // Self-contained
      ...options
    });

    // Login-specific state
    this.redirectUrl = options.redirectUrl || "/dashboard";
    this.rememberMe = options.rememberMe !== false;
    this._isOpening = false;

    // Initialize
    this.init();
  }

  // ─── Override: Custom trigger handling ──────────────────────

  bindTriggers() {
    // Remove parent's handler if any
    if (this._boundGlobalClickHandler) {
      document.removeEventListener("click", this._boundGlobalClickHandler);
      this._boundGlobalClickHandler = null;
    }

    // Bind our own handler with login-specific logic
    this._boundGlobalClickHandler = this._handleLoginClick.bind(this);
    document.addEventListener("click", this._boundGlobalClickHandler);

    this.logger.debug("Login triggers bound");
  }

  _handleLoginClick(event) {
    const trigger = event.target.closest(this.triggerSelector);
    if (!trigger) return;

    // Prevent double-opening
    if (this._isOpening || this.isRequesting) {
      this.logger.debug("Login modal already opening, ignoring click");
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
    this.openModal(trigger);
  }

  // ─── Override: Open modal ───────────────────────────────────

  async openModal(trigger) {
    if (this._isOpening) {
      this.logger.warn("Login modal is already opening");
      return;
    }

    // If trigger has a redirect URL, store it
    if (trigger?.dataset?.redirectUrl) {
      this.redirectUrl = trigger.dataset.redirectUrl;
    }

    // If trigger is a link, we might need to fetch the modal content
    const modalUrl = trigger?.dataset?.modalUrl || "/login/modal";

    this._isOpening = true;
    this.isRequesting = true;
    this.setLoadingState(trigger, true);

    try {
      // Fetch login modal HTML
      const result = await this.ajax.get(modalUrl, null, {
        json: true,
        timeout: 10000
      });

      if (result.success === false) {
        throw new Error(result.error || "Failed to load login form");
      }

      const modalHtml = result.modalHtml || result.html;
      if (!modalHtml) {
        throw new Error("No modal HTML returned");
      }

      this.showModal(modalHtml);
      this.initializeModalComponents();

      if (this.onModalOpened) {
        this.onModalOpened(this.currentModal);
      }
    } catch (error) {
      this.logger.error("Failed to open login modal:", error);
      this._showError(error.message || "Failed to load login form");
    } finally {
      this._isOpening = false;
      this.isRequesting = false;
      this.setLoadingState(trigger, false);
    }
  }

  // ─── Override: Process login form data ─────────────────────

  processFormData(data, formEl) {
    // Add remember me if checked
    const rememberCheckbox = formEl.querySelector('input[name="remember_me"]');
    if (rememberCheckbox) {
      data.remember_me = rememberCheckbox.checked;
    }

    // Add redirect URL
    if (this.redirectUrl) {
      data.redirect = this.redirectUrl;
    }

    return data;
  }

  // ─── Override: Success handling ────────────────────────────

  onSuccess(result, context) {
    this.logger.debug("Login successful");

    if (result.redirect) {
      this.redirectUrl = result.redirect;
    }

    // Show success message
    if (this.feedbackChannel) {
      this.feedbackChannel.success("Login successful! Redirecting...");
    }

    // Close modal and redirect
    if (this.closeOnSuccess) {
      this.closeCurrentModal("submission-success");
      setTimeout(() => {
        window.location.href = this.redirectUrl || "/dashboard";
      }, 500);
    }
  }

  // ─── Helper: Show error ────────────────────────────────────

  _showError(message) {
    if (this.feedbackChannel) {
      this.feedbackChannel.error(message);
    } else {
      alert(message);
    }
  }

  // ─── Cleanup ─────────────────────────────────────────────────

  destroy() {
    if (this._boundGlobalClickHandler) {
      document.removeEventListener("click", this._boundGlobalClickHandler);
      this._boundGlobalClickHandler = null;
    }
    this._isOpening = false;
    super.destroy();
  }
}
