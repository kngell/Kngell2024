import BrowserLogger from "js/core/utils/BrowserLogger";
import DropzoneFactory from "js/components/dropzone/DropzoneFactory";
import FormHandler from "js/core/handlers/FormHandler";
import CustomSelect from "js/components/custom-select/custom-select";
import RadioOptions from "js/components/Options/RadioOptions";
import ToggleSwitch from "js/components/ToggleSwitch/ToggleSwitch";
import AdminActionBar from "js/components/AdminMainHeader/AdminActionBar";
import { getModalRegistry } from "js/components/Modals/ModalRegistry";
import { getFlashChannel } from "js/components/FeedbackChannel/FlashManager";

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
      notificationConfig: {
        error: { permanent: true, duration: 8000, position: "top-right" },
        success: { permanent: false, duration: 3000, position: "top-right" },
        warning: { permanent: false, duration: 5000, position: "top-right" },
        info: { permanent: false, duration: 5000, position: "top-right" }
      },
      dropzoneConfig: {},
      customSelectConfig: {},
      customSelectInitDelay: options.customSelectInitDelay || 50,

      // Processor configuration
      processors: {
        enabled: true,
        notification: {
          enabled: true,
          config: { permanentErrors: true }
        },
        redirect: {
          enabled: true,
          config: {
            redirectOnInsert: true,
            redirectOnUpdate: false,
            redirectOnDelete: true,
            operationDelays: {
              insert: 1500,
              update: 0,
              delete: 1500
            }
          }
        }
      },

      ...options
    };

    this.dropzoneInstances = [];
    this.formHandler = null;
    this.customSelects = [];
    this.radioOptions = null;
    this.toggleSwitches = [];
    this.actionBar = null;
    this.logger = new BrowserLogger(this.constructor.name);

    this.flash = getFlashChannel();

    // Get modal registry singleton
    this.modalRegistry = getModalRegistry();
    this._deletionModalPromise = null;
    this._isInitialized = false;
  }

  // ─── Override Hooks: Form ───────────────────────────────────

  getFormSelector() {
    return 'form[data-validate="true"]';
  }

  getValidationRules() {
    return "defaultRules";
  }

  // ─── Override Hooks: Radio Options ──────────────────────────

  getRadioOptionName() {
    return "option";
  }

  getDefaultRadioValue() {
    return null;
  }

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

  getDeletionModalConfig() {
    return {};
  }

  getActionBarConfig() {
    return {};
  }

  // ─── Entity Lifecycle Hooks ─────────────────────────────────

  onEntityDeleted(entityId, result) {
    this.logger.success("Entity deleted:", entityId);
  }

  onBeforeDelete(context) {
    // Override in child class
    return true;
  }

  onBeforeAdd(context) {
    // Override in child class
    return true;
  }

  // ─── Override Hook: Child-Specific Components ───────────────

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

  // ─── Deletion Modal Init ───────────────────────────────────

  async getDeletionModal() {
    if (this._deletionModalPromise) {
      return this._deletionModalPromise;
    }

    const feedbackChannel = this.flash;

    const baseConfig = {
      onEntityDeleted: (entityId, result) => {
        this.onEntityDeleted(entityId, result);
      },
      onModalOpened: () => this.logger.debug("Deletion modal opened"),
      onModalClosed: () => this.logger.debug("Deletion modal closed"),
      notificationConfig: {
        error: this.options.notificationConfig?.error || {
          permanent: true,
          duration: 8000
        },
        success: this.options.notificationConfig?.success || {
          permanent: false,
          duration: 3000
        }
      }
    };

    const overrides = this.getDeletionModalConfig();

    this._deletionModalPromise = this.modalRegistry.getModal("deletion", {
      ...baseConfig,
      ...overrides,
      feedbackChannel: feedbackChannel
    });

    return this._deletionModalPromise;
  }

  // ─── Action Bar Init ───────────────────────────────────────

  async initActionBar() {
    const deletionModal = await this.getDeletionModal();

    const baseConfig = {
      deletionModal: deletionModal,
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

  async _init() {
    if (this._isInitialized) {
      this.logger.debug(`${this.constructor.name} already initialized`);
      return this;
    }

    this.logger.debug(`Initializing ${this.constructor.name}`);

    try {
      if (this.options.enableActionBar) {
        await this.initActionBar();
      }

      const form = await this._waitForForm();

      if (!form) {
        this.logger.warn(`No form found for ${this.constructor.name}`);
        await this.initSpecificComponents();
        this.logger.success(`${this.constructor.name} initialized (action bar only)`);
        this._isInitialized = true;
        return this;
      }

      this.logger.debug("Form found, initializing components...");
      await this._initializeComponents(form);
      await this._initializeFormHandler(form);

      this.logger.success(`${this.constructor.name} initialized`);
      this._isInitialized = true;
      return this;
    } catch (error) {
      this.logger.error(`Failed to initialize ${this.constructor.name}`, error);
      throw error;
    }
  }

  async _waitForForm() {
    let form = document.querySelector(this.getFormSelector());
    let attempts = 0;
    const maxAttempts = 10;

    while (!form && attempts < maxAttempts) {
      await new Promise((resolve) => setTimeout(resolve, 100));
      form = document.querySelector(this.getFormSelector());
      attempts++;
    }

    return form;
  }

  async _initializeComponents(form) {
    if (this.options.enableCustomSelect) {
      this.initCustomSelects();

      if (this.options.customSelectInitDelay > 0) {
        await new Promise((resolve) => setTimeout(resolve, this.options.customSelectInitDelay));
      }
    }

    await this.initSpecificComponents();

    if (this.options.enableToggleSwitch) {
      this.initToggleSwitches();
    }

    if (this.options.enableRadioOptions) {
      this.initRadioOptions();
    }

    if (this.options.enableDropzone) {
      await this._initializeDropzones();
    }
  }

  async _initializeDropzones() {
    await new Promise((resolve) => setTimeout(resolve, 200));

    this.dropzoneInstances = await DropzoneFactory.initAll();

    if (this.dropzoneInstances.length === 0) {
      await new Promise((resolve) => setTimeout(resolve, 500));
      const dropzoneElements = document.querySelectorAll(".upload-single, .upload-multiple");

      if (dropzoneElements.length > 0) {
        this.dropzoneInstances = await DropzoneFactory.initAll(null, this.options.dropzoneConfig);
      }
    }
  }

  async _initializeFormHandler(form) {
    const customDataProcessors = [
      (data, formEl) => {
        if (this.options.enableRadioOptions && this.radioOptions) {
          const value = this.radioOptions.getValue();
          if (value) {
            data[this.getRadioOptionName()] = value;
          }
        }
        return data;
      },
      ...this.getCustomDataProcessors()
    ];

    // ✅ Use the shared flash channel from FlashManager
    const feedbackChannel = this.flash;

    this.formHandler = new FormHandler(form, {
      rulesName: form.dataset.validationRules || this.getValidationRules(),
      enableRealTime: true,
      submissionMode: "ajax",
      ajaxHandler: true,

      processors: this.options.processors,
      feedbackChannel: feedbackChannel,

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
      enableRedirectProcessor: true,
      customDataProcessors: customDataProcessors,
      onSuccess: (result, context) => {
        this.onSuccess(result, context);
      },
      onError: (error) => {
        this.onError(error);
      }
    });

    await this.formHandler.initialize();
  }

  // ─── Component Initializers ─────────────────────────────────

  initToggleSwitches() {
    const toggleContainers = document.querySelectorAll(".toggle-switch");

    toggleContainers.forEach((container) => {
      if (!container.hasAttribute("data-toggle-initialized")) {
        const toggle = new ToggleSwitch(container);
        this.toggleSwitches.push(toggle);
        container.setAttribute("data-toggle-initialized", "true");
      }
    });
  }

  initRadioOptions() {
    const optionsContainer = document.querySelector(".options");
    if (!optionsContainer) return;

    const firstRadio = optionsContainer.querySelector('input[type="radio"]');
    if (!firstRadio) return;

    const radioName = firstRadio.name;
    if (!radioName) return;

    let initialValue = null;
    const hiddenInput = document.querySelector(`input[name="${radioName}"][type="hidden"]`);
    if (hiddenInput?.value) {
      initialValue = hiddenInput.value.toLowerCase();
    }

    this.radioOptions = new RadioOptions(optionsContainer, {
      value: initialValue,
      onChange: (event) => {
        const hiddenInput = document.querySelector(`input[name="${radioName}"][type="hidden"]`);
        if (hiddenInput) {
          hiddenInput.value = event.value;
        }
        this.onRadioChange(event);
      }
    });
  }

  initCustomSelects() {
    const customSelectConfigs = this.getCustomSelectConfigs();

    if (!customSelectConfigs || customSelectConfigs.length === 0) {
      return;
    }

    customSelectConfigs.forEach((config) => {
      try {
        if (!config.selector) {
          this.logger.error("Custom select config missing 'selector'");
          return;
        }

        const element = document.querySelector(config.selector);
        if (!element) {
          this.logger.warn(`Element not found: ${config.selector}`);
          return;
        }

        const customSelect = new CustomSelect(config.selector, {
          dataSource: config.dataSource || null,
          placeholder: config.placeholder,
          emptyMessage: config.emptyMessage,
          loadingMessage: config.loadingMessage,
          enableSearch: config.enableSearch ?? true,
          enableInfiniteScroll: config.enableInfiniteScroll ?? true,
          pageSize: config.pageSize ?? 20,
          searchDebounceMs: config.searchDebounceMs ?? 300,
          name: config.fieldName || config.name,
          onSelect: config.onSelect,
          onReset: config.onReset,
          onLoad: config.onLoad
        });

        customSelect.init();
        this.customSelects.push(customSelect);
        this.logger.debug(`CustomSelect initialized: ${config.selector}`);
      } catch (error) {
        this.logger.error(`Failed to initialize custom select`, error);
      }
    });
  }

  resetAllCustomSelects() {
    this.customSelects.forEach((select) => select.destroy());
    this.customSelects = [];
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

  async getDeletionModalInstance() {
    return this.getDeletionModal();
  }

  getCustomSelects() {
    return this.customSelects;
  }

  getCustomSelect(selector) {
    return this.customSelects.find((cs) => cs.selector === selector);
  }

  async validateForm() {
    return this.formHandler?.validateAll();
  }

  async submitForm() {
    if (!this.formHandler) {
      this.logger.error("Form handler not initialized");
      return;
    }

    await this.formHandler?.submit();
  }

  async _initializeDropzones() {
    await new Promise((resolve) => setTimeout(resolve, 200));

    const dropzoneElements = document.querySelectorAll(".upload-single, .upload-multiple");
    const elementsWithFiles = Array.from(dropzoneElements).map((element) => {
      const existingFiles = this._getExistingFilesFromDropzone(element);
      return { element, existingFiles };
    });

    this.dropzoneInstances = [];

    for (const { element, existingFiles } of elementsWithFiles) {
      if (!element.__dropzoneInstance) {
        const instance = await DropzoneFactory.init(element, {
          files: existingFiles,
          ...this.options.dropzoneConfig
        });
        if (instance) {
          element.__dropzoneInstance = instance;
          this.dropzoneInstances.push(instance);
        }
      }
    }

    if (this.dropzoneInstances.length === 0 && dropzoneElements.length > 0) {
      await new Promise((resolve) => setTimeout(resolve, 500));
      for (const { element, existingFiles } of elementsWithFiles) {
        if (!element.__dropzoneInstance) {
          const instance = await DropzoneFactory.init(element, {
            files: existingFiles,
            ...this.options.dropzoneConfig
          });
          if (instance) {
            element.__dropzoneInstance = instance;
            this.dropzoneInstances.push(instance);
          }
        }
      }
    }
  }

  _getExistingFilesFromDropzone(element) {
    const files = [];
    const input = element.querySelector('input[type="hidden"]');

    if (input && input.value) {
      files.push({
        path: input.value,
        name: input.value.split("/").pop(),
        size: 0,
        existing: true
      });
    }

    const previewImg = element.querySelector(`.${element.className.split(" ")[0]}__preview img`);
    if (previewImg && previewImg.src && !files.length) {
      files.push({
        path: previewImg.src,
        name: previewImg.src.split("/").pop(),
        size: 0,
        existing: true
      });
    }

    return files;
  }

  // ─── Destroy ────────────────────────────────────────────────
  destroy() {
    this.actionBar?.destroy();
    this.actionBar = null;

    if (this.radioOptions) {
      this.radioOptions.destroy();
      this.radioOptions = null;
    }

    this.toggleSwitches.forEach((toggle) => toggle.destroy());
    this.toggleSwitches = [];

    this.customSelects.forEach((cs) => cs.destroy());
    this.customSelects = [];

    this.formHandler?.destroy();
    this.formHandler = null;

    this.dropzoneInstances.forEach((d) => d.destroy?.());
    this.dropzoneInstances = [];

    this._isInitialized = false;
    this.logger.debug(`${this.constructor.name} destroyed`);
  }
}
