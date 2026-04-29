import BrowserLogger from "js/core/utils/BrowserLogger";
import DropzoneFactory from "js/components/dropzone/DropzoneFactory";
import FormHandler from "js/core/forms/FormHandler";
import CustomSelect from "js/components/custom-select/custom-select";
import RadioOptions from "js/components/Options/RadioOptions";
import ToggleSwitch from "js/components/ToggleSwitch/ToggleSwitch";
import AdminActionBar from "js/components/AdminMainHeader/AdminActionBar";
import DeletionModal from "js/components/Modals/DeletionModal";

export default class BaseFormManager {
  constructor(options = {}) {
    this.options = {
      enableDropzone: true,
      enableCustomSelect: false,
      enableRadioOptions: false,
      enableToggleSwitch: false,
      enableActionBar: true,
      resetOnSuccess: false,
      resetCustomSelectsOnSuccess: false,
      notificationPosition: "top-right",
      notificationContainerId: "app-notifications",
      dropzoneConfig: {},
      customSelectConfig: {},
      ...options
    };

    this.dropzoneInstances = [];
    this.formHandler = null;
    this.customSelects = [];
    this.radioOptions = null;
    this.toggleSwitches = [];
    this.actionBar = null;
    this.deletionModal = null;
    this.logger = new BrowserLogger(this.constructor.name);
  }

  // ─── Override Hooks: Notifications ──────────────────────────

  /**
   * Override in child class
   */
  getDefaultNotificationContainerId() {
    return "default-notifications";
  }

  // ─── Override Hooks: Form ───────────────────────────────────

  /**
   * Override in child class to provide form selector
   */
  getFormSelector() {
    return 'form[data-validate="true"]';
  }

  /**
   * Override in child class to provide validation rules
   */
  getValidationRules() {
    return "defaultRules";
  }

  // ─── Override Hooks: Radio Options ──────────────────────────

  /**
   * Override in child class to provide radio option name
   */
  getRadioOptionName() {
    return "option";
  }

  /**
   * Override in child class to provide default radio value
   */
  getDefaultRadioValue() {
    return null;
  }

  /**
   * Override in child class to handle radio change
   */
  onRadioChange(event) {
    // Hook for child classes
  }

  // ─── Override Hooks: Custom Select ──────────────────────────

  getCustomDataProcessors() {
    return [];
  }

  getCustomSelectConfigs() {
    return [];
  }

  // ─── Override Hooks: Toggle Switch ──────────────────────────

  onToggleChange(event) {
    // Hook for child classes
  }

  // ─── Override Hooks: Deletion Modal & Action Bar ────────────

  /**
   * Override in child class to provide deletion modal config overrides.
   * @returns {Object}
   */
  getDeletionModalConfig() {
    return {};
  }

  /**
   * Override in child class to provide action bar config overrides.
   * @returns {Object}
   */
  getActionBarConfig() {
    return {};
  }

  // ─── Entity Lifecycle Hooks ─────────────────────────────────

  /**
   * Called after an entity is successfully deleted via the deletion modal.
   * Override in child class for page-specific cleanup
   * (e.g., redirect, remove DOM element, refresh list).
   *
   * @param {string} entityId
   * @param {Object} result - Server response
   */
  onEntityDeleted(entityId, result) {
    this.logger.success("Entity deleted:", entityId);
  }

  /**
   * Called before the deletion modal AJAX request fires.
   * Return false to cancel the deletion flow.
   *
   * @param {Object} context - { data, formAction, button, form }
   * @returns {boolean|void}
   */
  onBeforeDelete(context) {
    // Override in child class
  }

  /**
   * Called before navigating to the add page.
   * Return false to cancel navigation.
   *
   * @param {Object} context - { data, formAction, button, form }
   * @returns {boolean|void}
   */
  onBeforeAdd(context) {
    // Override in child class
  }

  // ─── Override Hook: Child-Specific Components ───────────────

  /**
   * Hook for child classes to initialize their own specific components
   * (like EntitySelector, custom widgets, etc.)
   * Called after all standard components are initialized.
   */
  initSpecificComponents() {
    // Override in child class
  }

  // ─── Form Callbacks ─────────────────────────────────────────

  onSuccess(result, context) {
    this.logger.success(`${this.constructor.name} form submitted successfully`);

    if (this.options.resetOnSuccess && result.operation === "insert") {
      this.formHandler?.form?.reset();

      if (this.options.resetCustomSelectsOnSuccess) {
        this.resetAllCustomSelects();
      }

      this.dropzoneInstances.forEach((dz) => dz.reset?.());
    }
  }

  onError(error) {
    const errorKey = error.message || JSON.stringify(error);
    const now = Date.now();

    if (this._lastErrorLogKey === errorKey && now - this._lastErrorLogTime < 1000) {
      this.logger.debug("Suppressing duplicate error log");
      return;
    }

    this._lastErrorLogKey = errorKey;
    this._lastErrorLogTime = now;

    this.logger.error(`${this.constructor.name} form submission failed:`, error);
  }

  // ─── Notification Container ─────────────────────────────────

  ensureNotificationContainer() {
    const containerId =
      this.options.notificationContainerId || this.getDefaultNotificationContainerId();

    if (!document.getElementById(containerId)) {
      const container = document.createElement("div");
      container.id = containerId;
      container.className = "notification-container";
      document.body.appendChild(container);
      this.logger.debug(`Created notification container: ${containerId}`);
    }
  }

  // ─── Deletion Modal Init ───────────────────────────────────

  initDeletionModal() {
    const baseConfig = {
      onEntityDeleted: (entityId, result) => {
        this.onEntityDeleted(entityId, result);
      },
      onModalOpened: () => this.logger.debug("Deletion modal opened"),
      onModalClosed: () => this.logger.debug("Deletion modal closed"),
      notificationConfig: {
        error: {
          permanent: this.options.notificationConfig?.error?.permanent ?? true,
          duration: this.options.notificationConfig?.error?.duration ?? 8000
        },
        success: {
          permanent: this.options.notificationConfig?.success?.permanent ?? false,
          duration: this.options.notificationConfig?.success?.duration ?? 3000
        }
      }
    };

    const overrides = this.getDeletionModalConfig();

    this.deletionModal = new DeletionModal({
      ...baseConfig,
      ...overrides
    });
  }

  // ─── Action Bar Init ───────────────────────────────────────

  initActionBar() {
    if (!this.deletionModal) {
      this.logger.warn("Deletion modal not initialized before action bar — initializing now");
      this.initDeletionModal();
    }

    const baseConfig = {
      deletionModal: this.deletionModal,
      onBeforeDelete: (context) => this.onBeforeDelete(context),
      onBeforeAdd: (context) => this.onBeforeAdd(context)
    };

    const overrides = this.getActionBarConfig();

    this.actionBar = new AdminActionBar({
      ...baseConfig,
      ...overrides
    }).init();
  }

  // ─── Main Initialization ────────────────────────────────────
  // checkDeletionModal() {
  //   if (!this.deletionModal) {
  //     this.logger.warn("Deletion modal not initialized");
  //     return;
  //   }

  //   const deleteTriggers = document.querySelectorAll('[data-action="confirm-delete"]');
  //   this.logger.debug(`Found ${deleteTriggers.length} delete triggers`);

  //   deleteTriggers.forEach((trigger) => {
  //     this.logger.debug("Delete trigger:", trigger);
  //   });
  // }
  async _init() {
    this.logger.debug(`Initializing ${this.constructor.name}`);

    try {
      // ── Action bar + deletion modal (independent of main form) ──
      if (this.options.enableActionBar) {
        this.initDeletionModal();
        this.initActionBar();
      }

      // ── Find the main form ──
      let form = document.querySelector(this.getFormSelector());
      let attempts = 0;
      const maxAttempts = 10;

      while (!form && attempts < maxAttempts) {
        this.logger.debug(`Waiting for form... attempt ${attempts + 1}`);
        await new Promise((resolve) => setTimeout(resolve, 100));
        form = document.querySelector(this.getFormSelector());
        attempts++;
      }

      if (!form) {
        this.logger.warn(`No form found for ${this.constructor.name}`);
        // Still call child-specific init (action bar is already active)
        await this.initSpecificComponents();
        this.logger.success(`${this.constructor.name} initialized (action bar only)`);
        return;
      }

      this.logger.debug("Form found, initializing components...");

      // ── Custom selects ──
      if (this.options.enableCustomSelect) {
        this.initCustomSelects();
      }

      // ── Toggle switches ──
      if (this.options.enableToggleSwitch) {
        this.initToggleSwitches();
      }

      // ── Radio options ──
      if (this.options.enableRadioOptions) {
        this.initRadioOptions();
      }

      // ── Dropzones ──
      if (this.options.enableDropzone) {
        await new Promise((resolve) => setTimeout(resolve, 200));

        this.dropzoneInstances = await DropzoneFactory.initAll();
        this.logger.debug(`Initialized ${this.dropzoneInstances.length} dropzones`);

        if (this.dropzoneInstances.length === 0) {
          this.logger.debug("No dropzones found on first attempt, waiting for dynamic content...");
          await new Promise((resolve) => setTimeout(resolve, 500));

          const dropzoneElements = document.querySelectorAll(".upload-single, .upload-multiple");
          this.logger.debug(`Found ${dropzoneElements.length} dropzone elements after waiting`);

          if (dropzoneElements.length > 0) {
            this.dropzoneInstances = await DropzoneFactory.initAll(
              null,
              this.options.dropzoneConfig
            );
            this.logger.debug(`Retry: Initialized ${this.dropzoneInstances.length} dropzones`);
          }
        }
      }

      // ── Child-specific components ──
      await this.initSpecificComponents();

      // ── Custom data processors ──
      const customDataProcessors = [
        (data, formEl) => {
          if (this.options.enableRadioOptions && this.radioOptions) {
            const value = this.radioOptions.getValue();
            if (value) {
              data[this.getRadioOptionName()] = value;
              this.logger.debug(`Added ${this.getRadioOptionName()} to form data:`, value);
            }
          }
          return data;
        },
        ...this.getCustomDataProcessors()
      ];

      // ── Form handler ──
      this.formHandler = new FormHandler(form, {
        rulesName: form.dataset.validationRules || this.getValidationRules(),
        enableRealTime: true,
        submissionMode: "ajax",
        ajaxHandler: true,
        notificationContainerId: this.options.notificationContainerId,
        notificationPosition: this.options.notificationPosition,

        notificationConfig: {
          error: {
            permanent:
              this.options.errorPermanent !== undefined ? this.options.errorPermanent : true,
            duration: this.options.errorDuration || 8000,
            position: this.options.notificationPosition || "top-right"
          },
          success: {
            permanent: this.options.successPermanent || false,
            duration: this.options.successDuration || 3000,
            position: this.options.notificationPosition || "top-right"
          },
          ...this.options.notificationConfig
        },

        ajaxOptions: {
          timeout: 300000,
          json: false,
          contentType: false,
          processData: false,
          headers: {
            "X-Requested-With": "XMLHttpRequest",
            Accept: "application/json"
          }
        },
        customDataProcessors: customDataProcessors,
        onSuccess: (result, context) => {
          this.onSuccess(result, context);
        },
        onError: (error) => {
          this.onError(error);
        }
      });

      await this.formHandler.initialize();
      // setTimeout(() => {
      //   this.checkDeletionModal();
      // }, 500);
      this.logger.success(`${this.constructor.name} initialized`);
    } catch (error) {
      this.logger.error(`Failed to initialize ${this.constructor.name}`, error);
    }
  }

  // ─── Component Initializers ─────────────────────────────────

  initToggleSwitches() {
    const toggleContainers = document.querySelectorAll(".toggle-switch");

    toggleContainers.forEach((container) => {
      if (!container.hasAttribute("data-toggle-initialized")) {
        const toggle = new ToggleSwitch(container);
        this.toggleSwitches.push(toggle);
      }
    });

    if (toggleContainers.length) {
      this.logger.debug(`Initialized ${this.toggleSwitches.length} toggle switches`);
    }
  }

  initRadioOptions() {
    const optionsContainer = document.querySelector(".options");

    if (!optionsContainer) {
      this.logger.debug("No .options container found - skipping radio options initialization");
      return;
    }

    const hasRadios = optionsContainer.querySelector('input[type="radio"]');
    if (!hasRadios) {
      this.logger.debug(".options container found but no radio inputs - skipping");
      return;
    }

    const firstRadio = optionsContainer.querySelector('input[type="radio"]');
    const radioName = firstRadio ? firstRadio.name : null;

    if (!radioName) {
      this.logger.debug("No radio name found - skipping");
      return;
    }

    let initialValue = null;
    const hiddenInput = document.querySelector(`input[name="${radioName}"][type="hidden"]`);
    if (hiddenInput && hiddenInput.value) {
      initialValue = hiddenInput.value.toLowerCase();
      this.logger.debug(`Found initial ${radioName} value from hidden input:`, initialValue);
    }

    this.radioOptions = new RadioOptions(optionsContainer, {
      value: initialValue,
      onChange: (event) => {
        this.logger.debug(`${radioName} changed:`, {
          value: event.value,
          previousValue: event.previousValue
        });

        const hiddenInput = document.querySelector(`input[name="${radioName}"][type="hidden"]`);
        if (hiddenInput) {
          hiddenInput.value = event.value;
        }

        this.onRadioChange(event);
      }
    });

    this.logger.debug("RadioOptions initialized", {
      currentValue: this.radioOptions?.getValue()
    });
  }

  initCustomSelects() {
    const customSelects = this.getCustomSelectConfigs();

    customSelects.forEach((config) => {
      try {
        const selector = config.selector;
        const container = document.querySelector(selector);
        if (!container) {
          this.logger.debug(`Container not found for selector: ${selector}`);
          return;
        }

        let dataSource = config.dataSource;
        if (config.apiEndpoint && !dataSource) {
          dataSource = async (page, limit, search = "") => {
            const params = new URLSearchParams({ page, limit, search });
            const response = await fetch(`${config.apiEndpoint}?${params}`);
            const data = await response.json();
            return {
              items: (data.products || data.data || []).map((item) => ({
                id: item.id,
                value: item.id,
                label: item.sku ? `${item.name} (${item.sku})` : item.name,
                name: item.name,
                sku: item.sku,
                ...item
              })),
              total: data.total || 0,
              hasMore: data.hasMore || false
            };
          };
        }

        const customSelect = new CustomSelect(selector, {
          dataSource: dataSource,
          placeholder: config.placeholder,
          emptyMessage: config.emptyMessage,
          loadingMessage: config.loadingMessage,
          enableSearch: config.enableSearch ?? true,
          enableInfiniteScroll: config.enableInfiniteScroll ?? true,
          pageSize: config.pageSize ?? 20,
          itemFormatter: config.itemFormatter,
          name: config.fieldName,
          onSelect: (value, text, item) => {
            this.logger.debug(`Custom select selected: ${text} (${value})`);
            if (config.onSelect) {
              config.onSelect(value, text, item);
            }
            if (this.formHandler && config.fieldName) {
              this.formHandler.validateField(config.fieldName, value);
            }
          },
          onReset: () => {
            this.logger.debug("Custom select reset");
            if (config.onReset) {
              config.onReset();
            }
            if (this.formHandler && config.fieldName) {
              this.formHandler.validateField(config.fieldName, null);
            }
          }
        });

        customSelect.init();
        customSelect.selector = selector;
        this.customSelects.push(customSelect);
        this.logger.debug(`Custom select initialized for ${selector}`);
      } catch (error) {
        this.logger.error("Failed to initialize custom select", error);
      }
    });
  }

  resetAllCustomSelects() {
    this.customSelects.forEach((select) => {
      select.destroy();
    });
    this.customSelects = [];
    this.logger.debug("All custom selects reset");
  }

  // ─── Public API ─────────────────────────────────────────────

  getDropzones() {
    return this.dropzoneInstances;
  }

  getFormHandler() {
    return this.formHandler;
  }

  getActionBar() {
    return this.actionBar;
  }

  getDeletionModal() {
    return this.deletionModal;
  }

  getCustomSelects() {
    return this.customSelects;
  }

  getCustomSelect(selector) {
    return this.customSelects.find((cs) => cs.selector === selector);
  }

  getCustomSelectValue(selector) {
    const instance = this.customSelects.find((inst) => inst.selector === selector);
    return instance ? instance.getValue() : null;
  }

  async validateForm() {
    return this.formHandler?.validateAll();
  }

  async submitForm() {
    await this.formHandler?.submit();
  }

  // ─── Destroy ────────────────────────────────────────────────

  destroy() {
    // Action bar & deletion modal
    this.actionBar?.destroy();
    this.deletionModal?.destroy();
    this.actionBar = null;
    this.deletionModal = null;

    // Radio options
    if (this.radioOptions) {
      this.radioOptions.destroy();
      this.radioOptions = null;
    }

    // Toggle switches
    this.toggleSwitches.forEach((toggle) => toggle.destroy());
    this.toggleSwitches = [];

    // Custom selects
    this.resetAllCustomSelects();

    // Form handler
    this.formHandler?.destroy();
    this.formHandler = null;

    // Dropzones
    this.dropzoneInstances.forEach((d) => d.destroy?.());
    this.dropzoneInstances = [];

    this.logger.debug(`${this.constructor.name} destroyed`);
  }
}
