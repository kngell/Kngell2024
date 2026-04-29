import BrowserLogger from "js/core/utils/BrowserLogger";
import ModalBase from "./ModalBase";
import FormHandler from "js/core/forms/FormHandler";
import DeletionResponseProcessor from "js/core/processors/DeletionResponseProcessor";
import RadioOptions from "js/components/Options/RadioOptions";
import AjaxHandler from "js/core/utils/AjaxHandler";

export default class DeletionModal extends ModalBase {
  constructor(options = {}) {
    super("DeletionModal", {
      closeOnEsc: true,
      closeOnOverlayClick: true,
      preventBodyScroll: true,
      autoFocus: true,
      ...options.modalOptions
    });

    this.logger = new BrowserLogger("DeletionModal");
    this.ajax = new AjaxHandler();
    this.formHandler = null;
    this.radioOptions = null;
    this.isRequesting = false;

    // Callbacks
    this.onEntityDeleted = options.onEntityDeleted || null;
    this.onModalOpened = options.onModalOpened || null;
    this.onModalClosed = options.onModalClosed || null;

    // Track buttons managed externally (by AdminActionBar)
    this.managedTriggers = new Set();

    // Notification config
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

    this.init();
  }

  init() {
    super.init();
    this.bindDeleteTriggers();
    this.detectExistingModal();
  }

  // --------------------------------------------------
  // Managed triggers: external ownership registry
  // --------------------------------------------------

  registerManagedTrigger(trigger) {
    if (trigger) {
      this.managedTriggers.add(trigger);
    }
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

  // --------------------------------------------------
  // Detection: server-rendered modal already in DOM
  // --------------------------------------------------

  detectExistingModal() {
    const adopted = this.adoptExistingModal('[data-modal="confirm-deletion"].active');

    if (!adopted) return;

    this.logger.debug("Found server-rendered deletion modal, enhancing");

    this.initializeModalComponents();

    if (this.onModalOpened) {
      this.onModalOpened(this.currentModal);
    }
  }

  // --------------------------------------------------
  // Global delegated listener for unmanaged triggers
  // --------------------------------------------------

  bindDeleteTriggers() {
    this._boundGlobalClickHandler = (e) => {
      const trigger = e.target.closest('[data-action="confirm-delete"]');

      if (!trigger) return;
      if (this.isManagedTrigger(trigger)) return;
      if (trigger.closest(".modal-overlay") || trigger.closest(".modals-container")) return;
      if (this.isRequesting) {
        this.logger.debug("Request already in progress — skipping");
        return;
      }

      e.preventDefault();
      e.stopPropagation();

      this.requestConfirmation(trigger);
    };

    document.addEventListener("click", this._boundGlobalClickHandler);
  }

  // --------------------------------------------------
  // AJAX confirmation request
  // --------------------------------------------------

  async requestConfirmation(trigger) {
    if (this.isRequesting) {
      this.logger.debug("Request already in progress — ignoring duplicate");
      return;
    }

    const form = trigger.closest("form");

    if (!form) {
      this.logger.error("No form found for delete trigger");
      return;
    }

    if (form.id === "confirm-deletion-frm") {
      this.logger.warn("Trigger is inside the confirmation form — skipping");
      return;
    }

    const url = form.getAttribute("action");

    if (!url) {
      this.logger.error("Delete form has no action URL");
      return;
    }

    this.isRequesting = true;

    try {
      this.setLoadingState(trigger, true);

      const formData = new FormData(form);

      this.logger.debug(`Requesting deletion confirmation from: ${url}`);

      const result = await this.ajax.post(url, formData, {
        json: true,
        timeout: 15000
      });

      if (result.success === false) {
        throw new Error(result.error || "Server request failed");
      }

      if (!result.confirmDeletionModal) {
        throw new Error(result.error || "Server did not return confirmation modal");
      }

      this.showModal(result.confirmDeletionModal);
      this.initializeModalComponents();

      if (this.onModalOpened) {
        this.onModalOpened(this.currentModal);
      }
    } catch (error) {
      this.logger.error("Failed to open deletion modal:", error);

      if (this._isNetworkError(error)) {
        this.logger.warn("Network error — falling back to native form submission");
        form.submit();
      }
    } finally {
      this.isRequesting = false;
      this.setLoadingState(trigger, false);
    }
  }

  _isNetworkError(error) {
    const msg = error.message || "";
    return (
      msg.includes("Failed to fetch") ||
      msg.includes("NetworkError") ||
      msg.includes("Cannot connect")
    );
  }

  // --------------------------------------------------
  // Modal component initialization
  // --------------------------------------------------

  initializeModalComponents() {
    if (!this.currentModal) return;

    this.enhanceCancelButtons();
    this.initializeRadioOptions();
    this.initializeFormHandler();
    this.enhanceSubmitButton();

    // Rebind close manager after DOM modifications
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

      // Set the data attribute that ModalCloseManager listens for
      enhanced.setAttribute("data-modal-cancel", "true");

      noJsEl.replaceWith(enhanced);

      this.logger.debug("Cancel button enhanced for JS");
    });
  }

  initializeRadioOptions() {
    if (!this.currentModal) return;

    const optionsContainer = this.currentModal.querySelector(".options");
    if (!optionsContainer) return;

    const firstRadio = optionsContainer.querySelector('input[type="radio"]');
    if (!firstRadio) return;

    const radioName = firstRadio.name;
    if (!radioName) return;

    let initialValue = null;
    const hiddenInput = this.currentModal.querySelector(
      `input[name="${radioName}"][type="hidden"]`
    );
    if (hiddenInput?.value) {
      initialValue = hiddenInput.value.toLowerCase();
    }

    this.radioOptions = new RadioOptions(optionsContainer, {
      value: initialValue,
      onChange: (event) => {
        this.logger.debug("Deletion option changed:", event.value);

        if (hiddenInput) {
          hiddenInput.value = event.value;
        }

        this.updateConfirmLabel(event.value);
      }
    });
  }

  updateConfirmLabel(deletionOption) {
    if (!this.currentModal) return;

    const checkbox = this.currentModal.querySelector('input[name="confirm_delete"]');
    if (!checkbox) return;

    const label =
      this.currentModal.querySelector(`label[for="${checkbox.id}"]`) || checkbox.closest("label");
    if (!label) return;

    // FIX: Add the correct class selector (.input-field__checkbox-label)
    const labelSpan =
      label.querySelector(".input-field__checkbox-label") ||
      label.querySelector(".input-box__label") ||
      label;

    const form = this.currentModal.querySelector("#confirm-deletion-frm");
    // Fallback to "hero section" since that's what your HTML shows
    const entityLabel = form?.dataset?.entityLabel || "hero section";
    const entityLower = entityLabel.toLowerCase();

    if (deletionOption === "permanent") {
      labelSpan.textContent = `I understand this ${entityLower} will be permanently deleted`;
    } else {
      labelSpan.textContent = `I understand this ${entityLower} will be archived`;
    }
  }

  // async initializeFormHandler() {
  //   if (!this.currentModal) return;

  //   const form = this.currentModal.querySelector("#confirm-deletion-frm");
  //   if (!form) {
  //     this.logger.warn("No deletion form found in modal");
  //     return;
  //   }

  //   if (this.formHandler) {
  //     this.formHandler.destroy();
  //     this.formHandler = null;
  //   }

  //   try {
  //     const processor = new DeletionResponseProcessor().setOnEntityDeleted((entityId, result) => {
  //       this.handleDeletionSuccess(entityId, result);
  //     });

  //     this.formHandler = new FormHandler(form, {
  //       rulesName: form.dataset.validationRules || "confirmDeletionRules",
  //       enableRealTime: false, // Don't nag before first attempt
  //       submissionMode: "ajax",
  //       ajaxHandler: true,
  //       ajaxOptions: {
  //         timeout: 30000,
  //         json: true
  //       },
  //       responseProcessors: [processor],
  //       enableRedirectProcessor: false,
  //       notificationConfig: this.notificationConfig,

  //       customDataProcessors: [
  //         (data, formEl) => {
  //           const checkbox = formEl.querySelector('input[name="confirm_delete"]');
  //           if (checkbox) {
  //             data.confirm_delete = checkbox.checked;
  //           }

  //           if (this.radioOptions) {
  //             const value = this.radioOptions.getValue();
  //             if (value) {
  //               data.delete_option = value;
  //             }
  //           }

  //           return data;
  //         }
  //       ],

  //       onSuccess: (result, context) => {
  //         this.logger.debug("Deletion form submitted successfully");
  //       },

  //       onError: (error) => {
  //         this.logger.error("Deletion failed:", error);
  //         // Enable real-time validation AFTER first failed attempt
  //         this.formHandler?.enableRealTimeValidation();
  //       }
  //     });

  //     await this.formHandler.initialize();

  //     this.logger.success("Deletion form handler initialized");
  //   } catch (error) {
  //     this.logger.error("Failed to initialize deletion form:", error);
  //   }
  // }
  async initializeFormHandler() {
    if (!this.currentModal) return;

    const form = this.currentModal.querySelector("#confirm-deletion-frm");
    if (!form) {
      this.logger.warn("No deletion form found in modal");
      return;
    }

    if (this.formHandler) {
      this.formHandler.destroy();
      this.formHandler = null;
    }

    try {
      const processor = new DeletionResponseProcessor().setOnEntityDeleted((entityId, result) => {
        this.handleDeletionSuccess(entityId, result);
      });

      this.formHandler = new FormHandler(form, {
        rulesName: form.dataset.validationRules || "confirmDeletionRules",
        enableRealTime: true, // ← Changed from false to true
        submissionMode: "ajax",
        ajaxHandler: true,
        ajaxOptions: {
          timeout: 30000,
          json: true
        },
        responseProcessors: [processor],
        enableRedirectProcessor: false,
        notificationConfig: this.notificationConfig,

        customDataProcessors: [
          (data, formEl) => {
            const checkbox = formEl.querySelector('input[name="confirm_delete"]');
            if (checkbox) {
              data.confirm_delete = checkbox.checked;
            }

            if (this.radioOptions) {
              const value = this.radioOptions.getValue();
              if (value) {
                data.delete_option = value;
              }
            }

            return data;
          }
        ],

        onSuccess: (result, context) => {
          this.logger.debug("Deletion form submitted successfully");
        },

        onError: (error) => {
          this.logger.error("Deletion failed:", error);
        }
      });

      await this.formHandler.initialize();

      this.logger.success("Deletion form handler initialized");
    } catch (error) {
      this.logger.error("Failed to initialize deletion form:", error);
    }
  }
  // --------------------------------------------------
  // Submit button enhancement
  // --------------------------------------------------

  enhanceSubmitButton() {
    if (!this.currentModal) return;

    const submitBtn = this.currentModal.querySelector('button[form="confirm-deletion-frm"]');

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
        label.textContent = "Deleting...";
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

  // --------------------------------------------------
  // Deletion result handling
  // --------------------------------------------------

  handleDeletionSuccess(entityId, result) {
    this.logger.debug("Entity deleted:", entityId);

    this.closeCurrentModal("deletion-success");

    if (this.onEntityDeleted) {
      this.onEntityDeleted(entityId, result);
    }

    if (result.redirect) {
      setTimeout(() => {
        window.location.href = result.redirect;
      }, 1500);
    }
  }

  // --------------------------------------------------
  // Loading state for trigger buttons
  // --------------------------------------------------

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

  // --------------------------------------------------
  // Close override — cleanup modal-specific components
  // --------------------------------------------------

  closeCurrentModal(source = "programmatic") {
    // 1. Identify if the modal was closed via a cancellation action
    const cancellationSources = ["cancel-button", "esc", "overlay", "close-button"];

    if (cancellationSources.includes(source)) {
      this.notifyServerOfCancellation();
    }

    // 2. Cleanup modal-specific components BEFORE base close
    if (this.radioOptions) {
      this.radioOptions.destroy();
      this.radioOptions = null;
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

  async notifyServerOfCancellation() {
    if (!this.currentModal) return;

    // Grab the cancelRoute from the HTML: data-cancel-url="/hero-section-delete/cancel"
    const cancelUrl = this.currentModal.dataset.cancelUrl;

    if (!cancelUrl) {
      this.logger.debug("No cancel URL found on modal overlay. Skipping server notification.");
      return;
    }

    // 1. Find the cancel button inside the modal
    const cancelBtn = this.currentModal.querySelector('[data-modal-cancel="true"]');

    // 2. Find the specific form that wraps the cancel button
    const cancelForm = cancelBtn ? cancelBtn.closest("form") : null;

    // 3. Extract the CSRF token strictly from the cancel form
    let csrfToken = "";
    if (cancelForm) {
      const csrfInput = cancelForm.querySelector('input[name="csrfToken"]');
      if (csrfInput) {
        csrfToken = csrfInput.value;
      }
    }

    if (!csrfToken) {
      this.logger.warn("Could not find cancel form CSRF token. Trying fallback.");
      const fallbackInput = this.currentModal.querySelector('input[name="csrfToken"]');
      csrfToken = fallbackInput ? fallbackInput.value : "";
    }

    if (!csrfToken) {
      this.logger.warn("No CSRF token found in modal. Cancellation request will likely fail.");
    }

    const formData = new FormData();
    if (csrfToken) {
      formData.append("csrfToken", csrfToken);
    }

    this.logger.debug(`Notifying server of cancellation at: ${cancelUrl}`);

    try {
      await this.ajax.post(cancelUrl, formData, {
        json: true,
        timeout: 5000
      });

      this.logger.debug("Server session cleared successfully.");
    } catch (error) {
      this.logger.warn("Failed to notify server of cancellation:", error);
    }
  }

  // --------------------------------------------------
  // Cleanup
  // --------------------------------------------------

  destroy() {
    if (this.radioOptions) {
      this.radioOptions.destroy();
      this.radioOptions = null;
    }

    if (this.formHandler) {
      this.formHandler.destroy();
      this.formHandler = null;
    }

    if (this._boundGlobalClickHandler) {
      document.removeEventListener("click", this._boundGlobalClickHandler);
      this._boundGlobalClickHandler = null;
    }

    this.managedTriggers.clear();

    super.destroy();
  }
}
