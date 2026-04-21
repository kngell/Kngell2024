import BrowserLogger from "js/core/utils/BrowserLogger";
import DropzoneFactory from "js/components/dropzone/DropzoneFactory";
import FormHandler from "js/core/forms/FormHandler";
import CustomSelect from "js/components/custom-select/custom-select";
import RadioOptions from "js/components/Options/RadioOptions";
import ToggleSwitch from "js/components/ToggleSwitch/ToggleSwitch";

export default class BaseFormManager {
  constructor(options = {}) {
    this.options = {
      enableDropzone: true,
      enableCustomSelect: false,
      enableRadioOptions: false,
      enableToggleSwitch: false,
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
    this.customSelects = []; // Store CustomSelect instances
    this.radioOptions = null;
    this.toggleSwitches = [];
    this.logger = new BrowserLogger(this.constructor.name);
  }

  /**
   * Override in child class
   */
  getDefaultNotificationContainerId() {
    return "default-notifications";
  }

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

  getCustomDataProcessors() {
    return [];
  }

  getCustomSelectConfigs() {
    return [];
  }

  /**
   * Hook for child classes to initialize their own specific components
   * (like EntitySelector, custom widgets, etc.)
   */
  initSpecificComponents() {
    // Override in child class
  }

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

  async _init() {
    this.logger.debug(`Initializing ${this.constructor.name}`);

    try {
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
        return;
      }

      this.logger.debug(`Form found, initializing components...`);

      // Initialize generic custom selects
      if (this.options.enableCustomSelect) {
        this.initCustomSelects();
      }

      // Initialize toggle switches
      if (this.options.enableToggleSwitch) {
        this.initToggleSwitches();
      }

      // Initialize radio options
      if (this.options.enableRadioOptions) {
        this.initRadioOptions();
      }

      // Initialize dropzones
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

      await this.initSpecificComponents();

      // Build custom data processors
      const customDataProcessors = [
        (data, form) => {
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

      // Initialize form handler
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
          timeout: 30000,
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

      this.logger.success(`${this.constructor.name} initialized`);
    } catch (error) {
      this.logger.error(`Failed to initialize ${this.constructor.name}`, error);
    }
  }

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

  onToggleChange(event) {
    // Hook for child classes
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
            this.logger.debug(`Custom select reset`);
            if (config.onReset) {
              config.onReset();
            }
            if (this.formHandler && config.fieldName) {
              this.formHandler.validateField(config.fieldName, null);
            }
          }
        });

        customSelect.init();
        customSelect.selector = selector; // Store selector on instance
        this.customSelects.push(customSelect);
        this.logger.debug(`Custom select initialized for ${selector}`);
      } catch (error) {
        this.logger.error(`Failed to initialize custom select`, error);
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

  // Public API methods
  getDropzones() {
    return this.dropzoneInstances;
  }

  getFormHandler() {
    return this.formHandler;
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

  destroy() {
    if (this.radioOptions) {
      this.radioOptions.destroy();
      this.radioOptions = null;
    }

    this.toggleSwitches.forEach((toggle) => toggle.destroy());
    this.toggleSwitches = [];

    this.resetAllCustomSelects();

    this.formHandler?.destroy();
    this.dropzoneInstances.forEach((d) => d.destroy?.());

    this.formHandler = null;
    this.dropzoneInstances = [];

    this.logger.debug(`${this.constructor.name} destroyed`);
  }
}
