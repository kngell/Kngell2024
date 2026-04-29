import BrowserLogger from "js/core/utils/BrowserLogger";
import ActionButton from "./ActionButton";

class AdminActionBar {
  constructor(options = {}) {
    this.options = {
      containerSelector: ".title-right",
      deletionModal: null,
      onBeforeDelete: null,
      onBeforeAdd: null,
      ...options
    };

    this.container = null;
    this.deleteButton = null;
    this.addButton = null;
    this.logger = new BrowserLogger("AdminActionBar");
    this._initialized = false;
  }

  init() {
    if (this._initialized) {
      this.logger.warn("AdminActionBar already initialized");
      return this;
    }

    this.container = document.querySelector(this.options.containerSelector);

    if (!this.container) {
      this.logger.debug(`Container not found: "${this.options.containerSelector}" — skipping`);
      return this;
    }

    this._initDeleteButton();
    this._initAddButton();

    this._initialized = true;

    const actions = [this.deleteButton ? "delete" : null, this.addButton ? "add" : null].filter(
      Boolean
    );

    this.logger.debug(`AdminActionBar initialized with actions: [${actions.join(", ")}]`);

    return this;
  }

  _initDeleteButton() {
    const form = this.container.querySelector(':scope > form[id$="_delete_form"]');

    if (!form) {
      this.logger.debug("No delete form found in action bar container");
      return;
    }

    this.deleteButton = new ActionButton(form, {
      handler: (context) => this._handleDelete(context)
    });

    // Register the button AND the form with DeletionModal
    // so its global listener skips everything in this form
    if (this.options.deletionModal && this.deleteButton?.button) {
      this.options.deletionModal.registerManagedTrigger(this.deleteButton.button);
      this.options.deletionModal.registerManagedTrigger(form);
    }
  }

  _initAddButton() {
    const form = this.container.querySelector(':scope > form[id$="_add_form"]');

    if (!form) {
      this.logger.debug("No add form found in action bar container");
      return;
    }

    this.addButton = new ActionButton(form, {
      handler: (context) => this._handleAdd(context)
    });
  }

  _handleDelete(context) {
    if (typeof this.options.onBeforeDelete === "function") {
      if (this.options.onBeforeDelete(context) === false) {
        this.logger.debug("Delete cancelled by onBeforeDelete hook");
        return;
      }
    }

    if (!this.options.deletionModal) {
      this.logger.error("No deletion modal provided to AdminActionBar");
      return;
    }

    this.logger.debug(`Delegating delete to DeletionModal (form: ${context.formId})`);

    this.options.deletionModal.requestConfirmation(context.button);
  }

  _handleAdd(context) {
    if (typeof this.options.onBeforeAdd === "function") {
      if (this.options.onBeforeAdd(context) === false) {
        this.logger.debug("Add cancelled by onBeforeAdd hook");
        return;
      }
    }

    this.logger.debug(`Navigating to: ${context.formAction}`);
    window.location.href = context.formAction;
  }

  setDeleteEnabled(enabled) {
    this.deleteButton?.setEnabled(enabled);
  }

  setAddEnabled(enabled) {
    this.addButton?.setEnabled(enabled);
  }

  setDeleteVisible(visible) {
    this.deleteButton?.setVisible(visible);
  }

  setAddVisible(visible) {
    this.addButton?.setVisible(visible);
  }

  isInitialized() {
    return this._initialized;
  }

  destroy() {
    if (this.options.deletionModal) {
      if (this.deleteButton?.button) {
        this.options.deletionModal.unregisterManagedTrigger(this.deleteButton.button);
      }
      const form = this.deleteButton?.form;
      if (form) {
        this.options.deletionModal.unregisterManagedTrigger(form);
      }
    }

    this.deleteButton?.destroy();
    this.addButton?.destroy();

    this.deleteButton = null;
    this.addButton = null;
    this.container = null;
    this._initialized = false;

    this.logger.debug("AdminActionBar destroyed");
  }
}

export default AdminActionBar;
