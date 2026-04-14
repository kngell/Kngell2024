import BrowserLogger from "js/core/utils/BrowserLogger";
import DropzoneFactory from "js/components/dropzone/DropzoneFactory";
import FormHandler from "js/core/forms/FormHandler";
import ProductSelector from "js/components/custom-select/ProductSelector";
import RadioOptions from "js/components/Options/RadioOptions";
import ToggleSwitch from "js/components/ToggleSwitch/ToggleSwitch";

export default class BaseFormManager {
  constructor(options = {}) {
    this.options = {
      enableDropzone: true,
      enableCustomSelect: false,
      enableRadioOptions: false,
      resetOnSuccess: false,
      notificationPosition: "top-right",
      notificationContainerId: "app-notifications",
      dropzoneConfig: {},
      customSelectConfig: {},
      ...options
    };

    this.dropzoneInstances = [];
    this.formHandler = null;
    this.productSelectors = [];
    this.radioOptions = null;
    this.toggleSwitches = [];
    this.logger = new BrowserLogger(this.constructor.name);

    this.logger.debug(`${this.constructor.name} constructor called`, this.options);
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

  /**
   * Override in child class to add custom data processors
   */
  getCustomDataProcessors() {
    return [];
  }

  onSuccess(result, context) {
    this.logger.success(`${this.constructor.name} form submitted successfully`);

    if (this.options.resetOnSuccess && result.operation === "insert") {
      this.formHandler?.form?.reset();

      if (this.options.resetCustomSelectsOnSuccess) {
        this.resetCustomSelects();
      }

      this.dropzoneInstances.forEach((dz) => dz.reset?.());
    }
  }

  onError(error) {
    // Prevent duplicate error logs for the same error
    const errorKey = error.message || JSON.stringify(error);
    const now = Date.now();

    if (this._lastErrorLogKey === errorKey && now - this._lastErrorLogTime < 1000) {
      this.logger.debug("Suppressing duplicate error log");
      return;
    }

    this._lastErrorLogKey = errorKey;
    this._lastErrorLogTime = now;

    this.logger.error(`${this.constructor.name} form submission failed:`, error);

    // Business logic only - NO NOTIFICATIONS
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
      // Wait for the form to exist in DOM
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

      // Initialize custom selects if enabled
      if (this.options.enableCustomSelect) {
        this.initCustomSelects();
      }
      if (this.options.enableToggleSwitch) {
        this.initToggleSwitches();
      }
      // Initialize radio options if enabled
      if (this.options.enableRadioOptions) {
        this.initRadioOptions();
      }

      // Initialize dropzones if enabled - with multiple attempts
      if (this.options.enableDropzone) {
        // Wait a bit for dropzone elements to be rendered
        await new Promise((resolve) => setTimeout(resolve, 200));

        this.dropzoneInstances = await DropzoneFactory.initAll();
        this.logger.debug(`Initialized ${this.dropzoneInstances.length} dropzones`);

        // If no dropzones found, try again after a longer delay
        if (this.dropzoneInstances.length === 0) {
          this.logger.debug("No dropzones found on first attempt, waiting for dynamic content...");
          await new Promise((resolve) => setTimeout(resolve, 500));

          // Check if dropzone elements exist now
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

      // Build custom data processors
      const customDataProcessors = [
        // Add radio option value to form data if enabled
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
        // Add child class custom processors
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
      // Only initialize if not already done
      if (!container.hasAttribute("data-toggle-initialized")) {
        const toggle = new ToggleSwitch(container);
        this.toggleSwitches.push(toggle);
      }
    });

    if (toggleContainers.length) {
      this.logger.debug(`Initialized ${this.toggleSwitches.length} toggle switches`);
    }
  }

  /**
   * Override in child class to handle toggle changes
   */
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

    // Get the radio name directly from the DOM
    const firstRadio = optionsContainer.querySelector('input[type="radio"]');
    const radioName = firstRadio ? firstRadio.name : null;

    if (!radioName) {
      this.logger.debug("No radio name found - skipping");
      return;
    }

    // Get initial value from hidden input with the same name
    let initialValue = null;
    const hiddenInput = document.querySelector(`input[name="${radioName}"][type="hidden"]`);
    if (hiddenInput && hiddenInput.value) {
      initialValue = hiddenInput.value.toLowerCase();
      this.logger.debug(`Found initial ${radioName} value from hidden input:`, initialValue);
    }

    // Initialize RadioOptions - don't pass name, let it read from DOM
    this.radioOptions = new RadioOptions(optionsContainer, {
      value: initialValue, // Only pass the value, not the name
      onChange: (event) => {
        this.logger.debug(`${radioName} changed:`, {
          value: event.value,
          previousValue: event.previousValue
        });

        // Update hidden input
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
    const customSelectContainers = document.querySelectorAll(".input-field.custom-select");

    customSelectContainers.forEach((container, index) => {
      try {
        // Get the actual field name from the hidden input
        const hiddenInput = container.querySelector(".input-field__hidden-value");
        const actualFieldName = hiddenInput ? hiddenInput.name : null;

        // Use ProductSelector instead of direct CustomSelect
        const productSelector = new ProductSelector(`.input-field.custom-select`, {
          apiEndpoint:
            this.options.customSelectConfig?.apiEndpoint || "/small-banner-search/load-products",
          onSelect: (value, text, item) => {
            this.logger.debug(`Product selected: ${text} (${value})`);

            // Trigger validation if form handler exists
            if (this.formHandler && actualFieldName) {
              this.formHandler.validateField(actualFieldName, value);
            }
          },
          onReset: () => {
            this.logger.debug(`Product selection reset`);

            // Re-validate if needed
            if (this.formHandler && actualFieldName) {
              this.formHandler.validateField(actualFieldName, null);
            }
          }
        });

        productSelector.init();
        this.productSelectors.push(productSelector);

        this.logger.debug(`Product selector initialized for container ${index + 1}`);
      } catch (error) {
        this.logger.error(
          `Failed to initialize product selector for container ${index + 1}`,
          error
        );
      }
    });
  }

  resetCustomSelects() {
    this.productSelectors.forEach((selector) => {
      selector.destroy();
    });
    this.productSelectors = [];
    this.logger.debug("All product selectors reset");
  }

  // Public API methods
  getDropzones() {
    return this.dropzoneInstances;
  }

  getFormHandler() {
    return this.formHandler;
  }

  getCustomSelects() {
    return this.productSelectors;
  }

  getCustomSelectValue(selector) {
    const instance = this.productSelectors.find((inst) => inst.selector === selector);
    return instance ? instance.customSelect?.getValue() : null;
  }

  getThemePreference() {
    return this.radioOptions ? this.radioOptions.getValue() : null;
  }

  setThemePreference(value) {
    if (this.radioOptions) {
      this.radioOptions.setValue(value);
      this.logger.debug("Theme preference set to:", value);
    }
  }

  async validateForm() {
    return this.formHandler?.validateAll();
  }

  async submitForm() {
    await this.formHandler?.submit();
  }

  showSuccess(message, options = {}) {
    this.notificationHelper.success(message, options);
  }

  showError(message, options = {}) {
    this.notificationHelper.error(message, options);
  }

  showWarning(message, options = {}) {
    this.notificationHelper.warning(message, options);
  }

  showInfo(message, options = {}) {
    this.notificationHelper.info(message, options);
  }

  destroy() {
    if (this.radioOptions) {
      this.radioOptions.destroy();
      this.radioOptions = null;
    }
    this.toggleSwitches.forEach((toggle) => toggle.destroy());
    this.toggleSwitches = [];

    this.productSelectors.forEach((selector) => {
      selector.destroy();
    });
    this.productSelectors = [];

    this.formHandler?.destroy();
    this.dropzoneInstances.forEach((d) => d.destroy?.());

    this.formHandler = null;
    this.dropzoneInstances = [];

    this.logger.debug(`${this.constructor.name} destroyed`);
  }
}
