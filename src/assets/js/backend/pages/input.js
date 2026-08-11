import BaseFormManager from "js/components/Managers/BaseFormManager";
class Input extends BaseFormManager {
  constructor(options = {}) {
    super({
      enableDropzone: true,
      enableCustomSelect: true, // ← Enable custom select initialization
      enableRadioOptions: true,
      enableActionBar: true,
      resetOnSuccess: options.resetOnSuccess || true,
      resetCustomSelectsOnSuccess: true, // ← Reset selects after successful submit
      notificationContainerId: options.notificationContainerId || "Input-notifications",
      channelStrategy: "flash",
      flashSelector: options.flashSelector || ".input__body",
      flashConfig: {
        durations: {
          success: 3000,
          error: 0,
          warning: 5000,
          info: 4000
        },
        containerClass: "flash-container"
      },
      notificationConfig: {
        error: { permanent: true, duration: 8000 },
        success: { permanent: false, duration: 3000 }
      },
      ...options
    });
  }

  getChannelStrategy() {
    return this.options.channelStrategy;
  }

  getFlashSelector() {
    return this.getFormSelector();
  }

  getDefaultNotificationContainerId() {
    return "Input-notifications";
  }

  getFormSelector() {
    return 'form[data-validate="true"][data-validation-rules*="Input"], form#Input-form';
  }

  getValidationRules() {
    return "InputRules";
  }

  getCustomSelectConfigs() {
    return [
      {
        selector: ".input-field.custom-select",
        dataSource: "/admin/Input-search/load-products",
        placeholder: "Search Product by name or SKU...",
        fieldName: "product_id",
        pageSize: 20,
        enableSearch: true,
        enableInfiniteScroll: true
        // NO onSelect, NO onReset - ProductCard handles it!
      }
    ];
  }

  getActionBarConfig() {
    return {
      addButtonSelector: ".btn-add-Input",
      deleteButtonSelector: ".btn-delete-Input"
    };
  }

  onEntityDeleted(entityId, result) {
    this.logger.success("Input deleted:", entityId);
  }

  onSuccess(result, context) {
    this.logger.success("Input form submitted successfully");

    if (this.options.resetOnSuccess && result.operation === "insert") {
      this.formHandler?.form?.reset();
      this.dropzoneInstances.forEach((dz) => dz.reset?.());
      // Custom selects are auto-reset if resetCustomSelectsOnSuccess = true
    }
  }

  onBeforeDelete(context) {
    this.logger.debug("Before deleting Input:", context);
    return true;
  }

  destroy() {
    super.destroy();
  }
}

const initInput = () => {
  if (!window.InputInstance) {
    window.InputInstance = new Input();
    window.InputInstance._init();
  }
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initInput);
} else {
  initInput();
}

export default Input;
