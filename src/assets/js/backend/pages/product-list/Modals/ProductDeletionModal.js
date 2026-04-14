import BrowserLogger from "js/core/utils/BrowserLogger";
import ModalBase from "./ModalBase";
import FormHandler from "js/core/forms/FormHandler";
import ProductDeletionProcessor from "js/core/processors/ProductDeletionProcessor";
import RadioOptions from "js/components/Options/RadioOptions";

export default class ProductDeletionModal extends ModalBase {
  constructor(deletionHelper) {
    super("ProductDeletionModal", {
      closeOnEsc: true,
      closeOnOverlayClick: true,
      preventBodyScroll: true,
      autoFocus: true
    });

    this.logger = new BrowserLogger("ProductDeletionModal");
    this.formHandler = null;
    this.modalSubmitButton = null;
    this.deletionHelper = deletionHelper;
    this.radioOptions = null;

    // No NotificationManager here - FormHandler will handle it

    this.init();
  }

  init() {
    super.init();
    this.bindFeatureEvents();
  }

  bindFeatureEvents() {
    document.addEventListener("click", (e) => {
      const deleteBtn = e.target.closest('[data-action="open-delete-modal"]');
      if (deleteBtn) {
        e.preventDefault();
        this.openDeleteModal(deleteBtn);
      }
    });
  }

  async openDeleteModal(trigger) {
    try {
      this.setLoadingState(trigger, true);

      document.querySelectorAll('[data-action="open-delete-modal"]').forEach((btn) => {
        btn.classList.remove("active-delete-btn");
      });

      const triggerForm = trigger.closest("form");
      if (!triggerForm) {
        throw new Error("Delete form not found");
      }

      if (triggerForm) {
        const publicId = triggerForm.querySelector('input[name="public_id"]')?.value;
        if (publicId) {
          trigger.dataset.productId = publicId;
          this.logger.debug("Stored product ID on trigger button:", publicId);
        }
      }

      const existingValidator = triggerForm._formValidator;
      const ajaxHandler = existingValidator ? existingValidator.ajaxHandler : null;

      let response;
      if (ajaxHandler) {
        response = await ajaxHandler.postForm(triggerForm.action, triggerForm, {
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json"
          }
        });
      } else {
        const formData = new FormData(triggerForm);
        const fetchResponse = await fetch(triggerForm.action, {
          method: triggerForm.method || "POST",
          body: formData,
          headers: {
            "X-Requested-With": "XMLHttpRequest"
          }
        });
        response = await fetchResponse.json();
      }

      if (response.success === false || response.error) {
        throw new Error(response.error || "Failed to load deletion confirmation");
      }

      if (!response.confirmDeletionModal) {
        throw new Error("Modal content not received from server");
      }

      this.showModal(response.confirmDeletionModal);

      // Initialize radio options for deletion type
      this.initializeRadioOptions();

      // Initialize form handler - just pass configuration
      await this.initializeFormHandler(ajaxHandler);
    } catch (error) {
      this.logger.error("Failed to open delete modal:", error);
      // Show error through FormHandler? Actually, FormHandler isn't initialized yet.
      // For initialization errors, we still need a way to show errors.
      // But we can use a simple alert or let the caller handle it
      console.error("Modal open error:", error.message);
    } finally {
      this.setLoadingState(trigger, false);
    }
  }

  initializeRadioOptions() {
    if (!this.currentModal) return;

    const optionsContainer = this.currentModal.querySelector(".options");
    if (!optionsContainer) {
      this.logger.warn("No options container found in modal");
      return;
    }

    this.radioOptions = new RadioOptions(optionsContainer, {
      name: "deletion_option",
      onChange: (event) => {
        this.logger.debug("Deletion option changed:", event.value);
      }
    });
  }

  async initializeFormHandler(ajaxHandler = null) {
    if (!this.currentModal) return;

    const form = this.currentModal.querySelector('form[data-validate="true"]');
    if (!form) {
      this.logger.warn("No form with data-validate found in modal");
      return;
    }

    const confirmDeleteCheckbox = form.querySelector('input[name="confirm_delete"]');
    if (!confirmDeleteCheckbox) {
      this.logger.error("Required confirm_delete checkbox not found");
      // Use a fallback alert since FormHandler isn't initialized yet
      alert("Form configuration error: Missing confirmation checkbox");
      return;
    }

    if (this.formHandler) {
      this.formHandler.destroy();
      this.formHandler = null;
    }

    try {
      const productDeletionProcessor = new ProductDeletionProcessor().setDeletionHelper(
        this.deletionHelper
      );

      // Initialize FormHandler with configuration ONLY - it will handle notifications
      this.formHandler = new FormHandler(form, {
        rulesName: form.dataset.validationRules || "product_deletion",
        enableRealTime: true,
        submissionMode: "ajax",
        ajaxHandler: ajaxHandler || true,
        ajaxOptions: {
          timeout: 30000,
          json: true
        },
        responseProcessors: [productDeletionProcessor],
        enableRedirectProcessor: false,

        // Pass notification configuration - FormHandler handles the rest
        notificationConfig: {
          error: {
            permanent: true, // Deletion errors are critical
            duration: 8000
          },
          success: {
            permanent: false,
            duration: 3000
          }
        },

        customDataProcessors: [
          (data, form) => {
            const confirmDeleteCheckbox = form.querySelector('input[name="confirm_delete"]');
            if (confirmDeleteCheckbox) {
              data.confirm_delete = confirmDeleteCheckbox.checked;
            }

            if (this.radioOptions) {
              const deletionOption = this.radioOptions.getValue();
              if (deletionOption) {
                data.deletion_option = deletionOption;
              }
            }

            return data;
          }
        ],

        // Business logic only - no notifications
        onSuccess: (result, context) => {
          this.logger.debug("Product deletion successful");
          this.closeCurrentModal();
        },

        onError: (error) => {
          this.logger.error("Deletion failed:", error);
          if (this.modalSubmitButton) {
            this.modalSubmitButton.disabled = false;
            this.modalSubmitButton.textContent = "Try Again";
          }
        }
      });

      await this.formHandler.initialize();
      this.formHandler.disableRealTimeValidation();
      this.setupModalFooterButton();

      this.logger.success("Form handler initialized");
    } catch (error) {
      this.logger.error("Form validation setup failed:", error);
      // Let FormHandler handle the error notification
      // We don't need to show anything here
    }
  }

  setupModalFooterButton() {
    if (!this.currentModal || !this.formHandler) return;

    const modalFooter = this.currentModal.querySelector(
      '.modal-footer, .modal__footer, [class*="footer"], .btn-group'
    );

    if (!modalFooter) return;

    const submitButton = modalFooter.querySelector(
      'button[type="submit"], input[type="submit"], .btn-primary, .btn-danger'
    );

    if (!submitButton) return;

    this.modalSubmitButton = submitButton.cloneNode(true);
    submitButton.parentNode.replaceChild(this.modalSubmitButton, submitButton);

    this.modalSubmitButton.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      this.formHandler.submit();
    });
  }

  closeCurrentModal() {
    if (this.radioOptions) {
      this.radioOptions.destroy();
      this.radioOptions = null;
    }

    if (this.formHandler) {
      this.formHandler.destroy();
      this.formHandler = null;
    }
    this.modalSubmitButton = null;
    super.closeCurrentModal();
  }

  setLoadingState(element, isLoading) {
    if (!element) return;

    if (isLoading) {
      element.classList.add("loading");
      element.setAttribute("disabled", "");
      if (element.tagName === "BUTTON" || element.tagName === "INPUT") {
        element.dataset.originalText = element.textContent || element.value;
        element.textContent = "Loading...";
      }
    } else {
      element.classList.remove("loading");
      element.removeAttribute("disabled");
      if (element.dataset.originalText) {
        element.textContent = element.dataset.originalText;
        delete element.dataset.originalText;
      }
    }
  }

  destroy() {
    if (this.radioOptions) {
      this.radioOptions.destroy();
      this.radioOptions = null;
    }

    if (this.formHandler) {
      this.formHandler.destroy();
    }
    super.destroy();
  }
}
