import BrowserLogger from "js/core/utils/BrowserLogger";
import BaseFormModal from "../BaseFormModal";
import RadioOptions from "js/components/Options/RadioOptions";

export default class DeletionModal extends BaseFormModal {
  constructor(options = {}) {
    super("DeletionModal", {
      modalDataAttr: "modal",
      modalIdentifier: "confirm-deletion",
      formId: "confirm-deletion-frm",
      triggerSelector:
        '[data-action="confirm-delete"], [data-action="delete-link"], [data-action="delete-social"]',
      closeOnSuccess: true,
      enableRedirectProcessor: false,
      submitButtonSelector: 'button[form="confirm-deletion-frm"]',
      autoBindTriggers: false,
      ...options
    });

    this.radioOptions = null;
    this._isOpening = false;
    this._boundDeletionHandler = null;
    this._triggersBound = false;

    // ✅ Initialize these properties
    this._entityData = null;
    this._entityId = null;
    this._deleteUrl = null;
    this._deleteForm = null;

    this.init();
  }

  init() {
    super.init();

    if (!this._triggersBound) {
      this._bindDeletionTriggers();
      this._triggersBound = true;
    }

    return this;
  }

  _bindDeletionTriggers() {
    if (this._boundDeletionHandler) {
      document.removeEventListener("click", this._boundDeletionHandler);
      this._boundDeletionHandler = null;
    }

    this._boundDeletionHandler = this._handleDeletionClick.bind(this);
    document.addEventListener("click", this._boundDeletionHandler);

    this.logger.debug("Deletion triggers bound");
  }

  _handleDeletionClick(event) {
    const trigger = event.target.closest(this.triggerSelector);
    if (!trigger) return;
    console.log("Deletion trigger clicked:", trigger);
    if (this._isOpening || this.isRequesting) {
      this.logger.debug("Deletion modal already opening, ignoring click");
      event.preventDefault();
      event.stopPropagation();
      return;
    }

    if (this.currentModal && this.currentModal.contains(trigger)) {
      this.logger.debug("Trigger is inside modal, ignoring");
      return;
    }

    event.preventDefault();
    event.stopPropagation();

    this.openModal(trigger);
  }

  // ─── Backward compatibility ───
  async requestConfirmation(trigger) {
    return this.openModal(trigger);
  }

  // ─── Override: Open Modal with deletion-specific logic ───
  async openModal(trigger) {
    if (this._isOpening) {
      this.logger.warn("Deletion modal is already opening");
      return;
    }

    if (!trigger) {
      this.logger.error("No trigger provided");
      this._showError("Unable to open deletion confirmation. Please refresh and try again.");
      return;
    }

    const form = trigger.closest("form");
    if (!form) {
      this.logger.error("No form found for delete trigger");
      this._showError("Unable to find the form to delete. Please refresh and try again.");
      return;
    }

    if (form.getAttribute("id") === this.formId) {
      this.logger.warn("Trigger is inside the confirmation form — skipping");
      return;
    }

    const url = form.getAttribute("action");
    if (!url) {
      this.logger.error("Delete form has no action URL");
      this._showError("Unable to process deletion. Please refresh and try again.");
      return;
    }

    // ✅ Store form data for later use
    this._deleteForm = form;
    this._deleteUrl = url;
    this._entityId = this._extractEntityIdFromForm(form);

    this._isOpening = true;
    this.isRequesting = true;
    this.setLoadingState(trigger, true);

    try {
      const formData = new FormData(form);
      this.logger.debug(`Requesting deletion confirmation from: ${url}`);

      const result = await this.ajax.post(url, formData, {
        json: true,
        timeout: 15000
      });

      if (result.success === false) {
        this.logger.warn("Confirmation rejected by server:", result.error);
        const errorMessage =
          result.error || "Deletion was not allowed. Please check and try again.";
        this._showError(errorMessage);
        return;
      }

      if (!result.confirmDeletionModal) {
        this.logger.error("Server did not return confirmation modal:", result);
        this._showError("Unable to load the deletion confirmation. Please refresh and try again.");
        return;
      }

      // ✅ Store entity data from result
      if (result.entity) {
        this._entityData = result.entity;
      }

      this.showModal(result.confirmDeletionModal);
      this.initializeModalComponents();

      // ✅ Initialize deletion-specific components AFTER modal is shown
      this._initializeDeletionComponents();

      if (this.onModalOpened) {
        this.onModalOpened(this.currentModal);
      }
    } catch (error) {
      this.logger.error("Failed to open deletion modal:", error);

      if (this._isNetworkError(error)) {
        this.logger.warn("Network error — falling back to native form submission");
        this._showError("Network issue detected. Redirecting to normal form submission...");
        form.submit();
      } else {
        const errorMessage =
          error.message || "Unable to open deletion confirmation. Please refresh and try again.";
        this._showError(errorMessage);
      }
    } finally {
      this._isOpening = false;
      this.isRequesting = false;
      this.setLoadingState(trigger, false);
    }
  }

  /**
   * Initialize deletion-specific components after modal is shown
   */
  _initializeDeletionComponents() {
    if (!this.currentModal) return;

    // Initialize radio options for deletion type
    this._initializeRadioOptions();

    // Initialize entity summary (if available)
    this._initializeEntitySummary();

    // Initialize deletion impacts
    this._initializeDeletionImpacts();

    // Enhance the checkbox label
    this._initializeConfirmCheckbox();
  }

  /**
   * Initialize radio options (permanent vs archive)
   */
  _initializeRadioOptions() {
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
      value: initialValue || "archive",
      onChange: (event) => {
        this.logger.debug("Deletion option changed:", event.value);

        if (hiddenInput) {
          hiddenInput.value = event.value;
        }

        this._updateConfirmLabel(event.value);
      }
    });
  }

  _initializeEntitySummary() {
    if (!this.currentModal || !this._entityData) return;

    const summaryContainer = this.currentModal.querySelector(".entity-summary");
    if (!summaryContainer) return;

    if (this._entityData.title) {
      const titleEl = summaryContainer.querySelector(".entity-title");
      if (titleEl) titleEl.textContent = this._entityData.title;
    }

    if (this._entityData.description) {
      const descEl = summaryContainer.querySelector(".entity-description");
      if (descEl) descEl.textContent = this._entityData.description;
    }

    if (this._entityData.metadata) {
      const metaEl = summaryContainer.querySelector(".entity-metadata");
      if (metaEl) {
        metaEl.innerHTML = Object.entries(this._entityData.metadata)
          .map(([key, value]) => `<span class="meta-item"><strong>${key}:</strong> ${value}</span>`)
          .join("");
      }
    }
  }

  _initializeDeletionImpacts() {
    if (!this.currentModal) return;

    const impactsContainer = this.currentModal.querySelector(".deletion-impacts");
    if (!impactsContainer) return;

    const impacts = this._entityData?.impacts || this.currentModal.dataset.impacts || [];

    if (Array.isArray(impacts) && impacts.length > 0) {
      const list = impactsContainer.querySelector(".impact-list") || document.createElement("ul");
      list.className = "impact-list";

      impacts.forEach((impact) => {
        const item = document.createElement("li");
        item.textContent = impact;
        list.appendChild(item);
      });

      impactsContainer.appendChild(list);
    }
  }

  _initializeConfirmCheckbox() {
    if (!this.currentModal) return;

    const checkbox = this.currentModal.querySelector('input[name="confirm_delete"]');
    if (!checkbox) return;

    const label = checkbox.closest("label");
    if (!label) return;

    const entityLabel = this._deleteForm?.dataset?.entityLabel || this._entityData?.label || "item";
    const entityLower = entityLabel.toLowerCase();

    const labelSpan =
      label.querySelector(".input-field__checkbox-label") ||
      label.querySelector(".input-box__label") ||
      label;

    const selectedRadio = this.currentModal.querySelector('input[name="delete_option"]:checked');
    const deletionOption = selectedRadio?.value || "archive";

    if (deletionOption === "permanent") {
      labelSpan.textContent = `I understand this ${entityLower} will be permanently deleted`;
    } else {
      labelSpan.textContent = `I understand this ${entityLower} will be archived`;
    }
  }

  _updateConfirmLabel(deletionOption) {
    if (!this.currentModal) return;

    const checkbox = this.currentModal.querySelector('input[name="confirm_delete"]');
    if (!checkbox) return;

    const label = checkbox.closest("label");
    if (!label) return;

    const labelSpan =
      label.querySelector(".input-field__checkbox-label") ||
      label.querySelector(".input-box__label") ||
      label;

    const entityLabel = this._deleteForm?.dataset?.entityLabel || this._entityData?.label || "item";
    const entityLower = entityLabel.toLowerCase();

    if (deletionOption === "permanent") {
      labelSpan.textContent = `I understand this ${entityLower} will be permanently deleted`;
    } else {
      labelSpan.textContent = `I understand this ${entityLower} will be archived`;
    }
  }

  // ─── Override: Process form data before submission ─────────────────────

  processFormData(data, formEl) {
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

    if (this._entityId) {
      data.entity_id = this._entityId;
    }

    return data;
  }

  // ─── Custom processors for deletion modal ─────────────────────────────

  getCustomProcessors() {
    const deletionEventProcessor = {
      handle: (context) => {
        const { result } = context;
        if (result?.success === true && result?.data) {
          const form = this.currentModal?.querySelector("#confirm-deletion-frm");
          const entityId = this._extractEntityIdFromForm(form) || this._entityId;

          // ✅ Store entityId in context for BaseHandler
          context.entityId = entityId;
          context.entityType = this.entityType || "column";

          if (this.onEntityDeleted) {
            this.onEntityDeleted(entityId, result);
          }
        }
      }
    };

    return [deletionEventProcessor];
  }

  // ─── Override: Custom success handling ────────────────────────────────

  onSuccess(result, context) {
    this.logger.debug("Deletion successful");
  }

  // ─── Override: Close modal with cleanup ───

  closeModal() {
    super.closeModal();
    this._isOpening = false;
    this.isRequesting = false;
  }

  // ─── Helper: Show error through channel ────────────────────────────────

  _showError(message) {
    this.logger.error(message);

    if (this.feedbackChannel && this.feedbackChannel.error) {
      this.feedbackChannel.error(message);
    } else {
      document.dispatchEvent(
        new CustomEvent("entity:save-error", {
          detail: {
            error: {
              message: message,
              source: "DeletionModal"
            }
          }
        })
      );
    }
  }

  // ─── Helper: Extract entity ID from form ──────────────────────────────

  _extractEntityIdFromForm(form) {
    if (!form) return null;

    const idInput = form.querySelector('input[name="id"]');
    if (idInput?.value) return idInput.value;

    const entityIdInput = form.querySelector('input[name="entity_id"]');
    if (entityIdInput?.value) return entityIdInput.value;

    if (form.dataset.entityId) return form.dataset.entityId;

    const action = form.getAttribute("action");
    if (action) {
      const match = action.match(/\/(\d+)(?:\/|$)/);
      if (match) return match[1];
    }

    return null;
  }

  // ─── Network error detection ──────────────────────────────────────────

  _isNetworkError(error) {
    return (
      error.name === "NetworkError" ||
      error.name === "TypeError" ||
      error.message?.includes("network") ||
      error.message?.includes("fetch") ||
      error.message?.includes("Failed to fetch")
    );
  }

  // ─── Cleanup ─────────────────────────────────────────────────────────

  destroy() {
    if (this._boundDeletionHandler) {
      document.removeEventListener("click", this._boundDeletionHandler);
      this._boundDeletionHandler = null;
    }

    this._triggersBound = false;
    this._isOpening = false;
    this._entityData = null;
    this._entityId = null;
    this._deleteUrl = null;
    this._deleteForm = null;

    if (this.radioOptions) {
      this.radioOptions.destroy();
      this.radioOptions = null;
    }

    super.destroy();

    this.logger.debug("DeletionModal destroyed");
  }
}
