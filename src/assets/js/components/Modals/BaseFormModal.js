import BrowserLogger from "js/core/utils/BrowserLogger";
import ModalBase from "./ModalBase";
import FormHandler from "js/core/handlers/FormHandler";
import AjaxHandler from "js/core/utils/AjaxHandler";
import SilentChannel from "js/components/FeedbackChannel/SilentChannel";
import { FakeSelectionObserver } from "ckeditor5";

export default class BaseFormModal extends ModalBase {
  constructor(modalName, options = {}) {
    super(modalName, {
      closeOnEsc: true,
      closeOnOverlayClick: true,
      preventBodyScroll: true,
      autoFocus: true,
      ...options.modalOptions
    });

    this.logger = new BrowserLogger(modalName);

    // ✅ Define this.onSuccess
    this.onSuccess = options.onSuccess || null;
    this.onError = options.onError || null;

    this.ajax = new AjaxHandler();

    this.formHandler = null;
    this.isRequesting = false;
    this._isSubmitting = false;

    // Modal configuration
    this.modalDataAttr = options.modalDataAttr || "modal";
    this.modalIdentifier = options.modalIdentifier || null;
    this.formId = options.formId || null;
    this.disableRedirect = options.disableRedirect || false;
    this.closeOnSuccess = options.closeOnSuccess !== false;
    this.submitButtonSelector = options.submitButtonSelector || 'button[type="submit"]';
    this.entityEventName = options.entityEventName || "entity:saved";
    this.triggerSelector = options.triggerSelector || null;
    this.autoBindTriggers = options.autoBindTriggers !== false;
    this._isInitialized = false;
    this.enableRedirectProcessor = options.enableRedirectProcessor !== false;
    this._parentFeedbackChannel = options.feedbackChannel || null;

    this._silentChannel = new SilentChannel({
      logger: this.logger
    });

    this.notificationConfig = {
      error: {
        permanent: true,
        duration: 8000
      },
      success: {
        permanent: false,
        duration: 3000
      },
      ...options.notificationConfig
    };

    // Callbacks
    this.onModalOpened = options.onModalOpened || null;
    this.onModalClosed = options.onModalClosed || null;
    this.onEntitySaved = options.onEntitySaved || null;
    this.onEntityDeleted = options.onEntityDeleted || null;

    // Managed triggers
    this.managedTriggers = new Set();
    this._boundGlobalClickHandler = null;
  }

  init() {
    if (this._isInitialized) return this;

    super.init();

    if (this.autoBindTriggers && this.triggerSelector) {
      this.bindTriggers();
    }

    this.detectExistingModal();
    this._isInitialized = true;
    return this;
  }

  // ─── Trigger Management ─────────────────────────────────────

  registerManagedTrigger(trigger) {
    if (trigger) this.managedTriggers.add(trigger);
  }

  unregisterManagedTrigger(trigger) {
    this.managedTriggers.delete(trigger);
  }

  isManagedTrigger(element) {
    if (this.managedTriggers.has(element)) return true;
    for (const managed of this.managedTriggers) {
      if (managed.contains(element)) return true;
    }
    return false;
  }

  bindTriggers() {
    if (this._boundGlobalClickHandler) {
      document.removeEventListener("click", this._boundGlobalClickHandler);
      this._boundGlobalClickHandler = null;
    }

    this._boundGlobalClickHandler = (e) => {
      const trigger = e.target.closest(this.triggerSelector);

      if (!trigger) return;
      if (this.isManagedTrigger(trigger)) return;
      if (trigger.closest(".modal-overlay") || trigger.closest(".modals-container")) return;
      if (this.isRequesting) {
        this.logger.debug("Request already in progress — skipping");
        return;
      }

      e.preventDefault();
      e.stopPropagation();

      this.openModal(trigger);
    };

    document.addEventListener("click", this._boundGlobalClickHandler);
    this.logger.debug(`Triggers bound for ${this.modalName}`);
  }

  unbindTriggers() {
    if (this._boundGlobalClickHandler) {
      document.removeEventListener("click", this._boundGlobalClickHandler);
      this._boundGlobalClickHandler = null;
      this.logger.debug(`Triggers unbound for ${this.modalName}`);
    }
  }

  // ─── Network Error Detection ────────────────────────────────

  _isNetworkError(error) {
    const msg = error?.message || "";
    return (
      msg.includes("Failed to fetch") ||
      msg.includes("NetworkError") ||
      msg.includes("Cannot connect") ||
      msg.includes("Network request failed")
    );
  }

  // ─── Modal Opening (to be overridden) ───────────────────────

  async openModal(trigger) {
    throw new Error("openModal must be implemented by child class");
  }

  // ─── Server-rendered modal detection ────────────────────────

  detectExistingModal() {
    const selector = this.modalIdentifier
      ? `[data-${this.modalDataAttr}="${this.modalIdentifier}"].active`
      : `.modal-overlay.active`;

    const adopted = this.adoptExistingModal(selector);
    if (!adopted) return;

    this.logger.debug("Found existing modal, enhancing");
    this.initializeModalComponents();

    if (this.onModalOpened) {
      this.onModalOpened(this.currentModal);
    }
  }

  // ─── Modal Component Initialization ─────────────────────────

  initializeModalComponents() {
    if (!this.currentModal) return;

    this.enhanceCancelButtons();
    this.initializeFormHandler();
    this.enhanceSubmitButton();

    if (this.closeManager) {
      this.closeManager.rebind();
    }
  }

  enhanceCancelButtons() {
    if (!this.currentModal) return;

    const nojsForms = this.currentModal.querySelectorAll("[data-nojs-only]");

    nojsForms.forEach((noJsEl) => {
      const button = noJsEl.querySelector("button");
      if (!button) return;

      const enhanced = button.cloneNode(true);
      enhanced.type = "button";
      enhanced.removeAttribute("data-nojs-only");
      enhanced.setAttribute("data-modal-cancel", "true");

      noJsEl.replaceWith(enhanced);
      this.logger.debug("Cancel button enhanced for JS");
    });
  }

  async initializeFormHandler() {
    if (!this.currentModal) return;

    const form = this.currentModal.querySelector(`#${this.formId}`);
    if (!form) {
      this.logger.warn(`No form found with id: ${this.formId}`);
      return;
    }

    if (this.formHandler) {
      this.formHandler.destroy();
      this.formHandler = null;
    }

    try {
      const processors = this.buildProcessors();
      const feedbackChannel = this._silentChannel;

      // ✅ MASTER SWITCH - false = NO redirects ever
      this.formHandler = new FormHandler(form, {
        rulesName: form.dataset.validationRules || `${this.formId}Rules`,
        enableRealTime: true,
        submissionMode: "ajax",
        ajaxHandler: true,
        ajaxOptions: {
          timeout: 30000,
          json: true,
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json"
          }
        },
        enableRedirectProcessor: this.enableRedirectProcessor !== false,
        processors: {
          enabled: true,
          notification: {
            enabled: false
          },
          redirect: {
            enabled: false,
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
          },
          custom: processors
        },
        notificationConfig: this.notificationConfig,
        feedbackChannel: feedbackChannel,

        customDataProcessors: [
          (data, formEl) => {
            return this.processFormData(data, formEl);
          },
          ...(this.customDataProcessors || [])
        ],

        onSuccess: (result, context) => {
          this.logger.debug(`${this.modalName} submission successful`);

          if (this.onSuccess) {
            this.onSuccess(result, context);
            console.log("on success ok");
          } else {
            console.log("on success Nok/NOP");
          }

          if (this.closeOnSuccess) {
            this.closeCurrentModal("submission-success");
          }
        },

        onError: (error) => {
          this.logger.error(`${this.modalName} submission failed:`, error);

          if (this.closeOnSuccess) {
            this.closeCurrentModal("submission-error");
          }

          if (this.onError) {
            this.onError(error);
          }
        }
      });

      await this.formHandler.initialize();
      this.logger.success(`${this.modalName} form handler initialized`);
    } catch (error) {
      this.logger.error(`Failed to initialize ${this.modalName} form:`, error);
    }
  }

  // ─── Build Processors ────────────────────────────────────────

  buildProcessors() {
    const processors = [];
    const childProcessors = this.getCustomProcessors ? this.getCustomProcessors() : [];
    processors.push(...childProcessors);

    return processors;
  }

  // Hook for child classes to process form data before submission
  processFormData(data, formEl) {
    return data;
  }

  // Hook for child classes to add custom processors
  getCustomProcessors() {
    return [];
  }

  enhanceSubmitButton() {
    if (!this.currentModal) return;

    const submitBtn = this.currentModal.querySelector(this.submitButtonSelector);
    if (!submitBtn) return;

    submitBtn.type = submitBtn.dataset.jsType || "button";

    submitBtn.addEventListener("click", (e) => {
      e.preventDefault();
      this.handleSubmitClick(submitBtn);
    });

    this.logger.debug("Submit button enhanced for JS");
  }

  async handleSubmitClick(button) {
    if (!this.formHandler) {
      this.logger.error("FormHandler not initialized");
      return;
    }

    if (this._isSubmitting) {
      this.logger.debug("Already submitting — ignoring");
      return;
    }

    this._isSubmitting = true;
    this.setSubmitLoading(button, true);

    try {
      await this.formHandler.submit();
    } catch (error) {
      this.logger.debug("Submission failed:", error.message);
    } finally {
      this._isSubmitting = false;
      this.setSubmitLoading(button, false);
    }
  }

  setSubmitLoading(button, isLoading) {
    if (!button) return;

    const label = button.querySelector(".btn__label");

    if (isLoading) {
      button.classList.add("loading");
      button.setAttribute("disabled", "");

      if (label) {
        button.dataset.originalText = label.textContent;
        label.textContent = "Saving...";
      }
    } else {
      button.classList.remove("loading");
      button.removeAttribute("disabled");

      if (label && button.dataset.originalText) {
        label.textContent = button.dataset.originalText;
        delete button.dataset.originalText;
      }
    }
  }

  setLoadingState(element, isLoading) {
    if (!element) return;

    const label = element.querySelector(".btn__label");

    if (isLoading) {
      element.classList.add("loading");
      element.setAttribute("disabled", "");

      if (label) {
        element.dataset.originalText = label.textContent;
        label.textContent = "Loading...";
      }
    } else {
      element.classList.remove("loading");
      element.removeAttribute("disabled");

      if (label && element.dataset.originalText) {
        label.textContent = element.dataset.originalText;
        delete element.dataset.originalText;
      }
    }
  }

  // ─── Close Override ─────────────────────────────────────────

  closeCurrentModal(source = "programmatic") {
    const cancellationSources = ["cancel-button", "esc", "overlay", "close-button"];
    const isCancellation = cancellationSources.includes(source);

    const shouldNotify = isCancellation && !this._hasError && source !== "submission-success";

    if (shouldNotify) {
      this.notifyServerOfCancellation();
    }

    if (this.formHandler) {
      this.formHandler.destroy();
      this.formHandler = null;
    }

    if (this.onModalClosed) {
      this.onModalClosed(source);
    }

    super.closeCurrentModal(source);
  }

  // Add a flag to track errors
  _onError(error) {
    this._hasError = true;
  }

  async notifyServerOfCancellation() {
    if (!this.currentModal) return;

    const cancelUrl = this.currentModal.dataset.cancelUrl;
    if (!cancelUrl) {
      this.logger.debug("No cancel URL found");
      return;
    }

    const cancelBtn = this.currentModal.querySelector('[data-modal-cancel="true"]');
    const cancelForm = cancelBtn ? cancelBtn.closest("form") : null;

    let csrfToken = "";
    if (cancelForm) {
      const csrfInput = cancelForm.querySelector('input[name="csrfToken"]');
      if (csrfInput) csrfToken = csrfInput.value;
    }

    if (!csrfToken) {
      const fallbackInput = this.currentModal.querySelector('input[name="csrfToken"]');
      csrfToken = fallbackInput ? fallbackInput.value : "";
    }

    const formData = new FormData();
    if (csrfToken) {
      formData.append("csrfToken", csrfToken);
    }

    try {
      await this.ajax.post(cancelUrl, formData, {
        json: true,
        timeout: 5000
      });
      this.logger.debug("Server cancellation notified");
    } catch (error) {
      this.logger.warn("Failed to notify server of cancellation:", error);
    }
  }

  _findCsrfToken() {
    if (!this.currentModal) return null;
    const csrfInput = this.currentModal.querySelector('input[name="csrfToken"]');
    return csrfInput?.value || null;
  }

  _extractEntityIdFromForm(form) {
    if (!form) return null;
    const idInput = form.querySelector('input[name="id"]');
    return idInput?.value || null;
  }

  // ─── Cleanup ────────────────────────────────────────────────

  destroy() {
    this.unbindTriggers();

    if (this.formHandler) {
      this.formHandler.destroy();
      this.formHandler = null;
    }

    this.managedTriggers.clear();
    super.destroy();
  }
}
