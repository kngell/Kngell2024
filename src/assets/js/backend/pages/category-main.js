import BaseFormManager from "js/components/Managers/BaseFormManager";
import PriceRangeBrackets from "js/components/PriceRange/priceRangeBrackets";

class CategoryMain extends BaseFormManager {
  constructor(options = {}) {
    super({
      enableDropzone: true,
      enableCustomSelect: true,
      enableRadioOptions: false,
      enableToggleSwitch: false,
      resetOnSuccess: options.resetOnSuccess || true,
      resetCustomSelectsOnSuccess: true,
      notificationPosition: options.notificationPosition || "top-right",
      maxNotifications: options.maxNotifications || 3,
      notificationDuration: options.notificationDuration || 5000,
      notificationContainerId: options.notificationContainerId || "category-notifications",

      channelStrategy: "flash",
      flashSelector: options.flashSelector || ".category__body",
      flashConfig: {
        durations: {
          success: 3000,
          error: 0, // Product errors persist
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

    this.priceRangeBrackets = null;
  }

  getDefaultNotificationContainerId() {
    return "category-notifications";
  }

  getFormSelector() {
    return 'form[data-validate="true"][data-validation-rules*="categoryRules"], form#category-frm';
  }

  getValidationRules() {
    return "categoryRules";
  }

  getCustomSelectConfigs() {
    // For static HTML options, don't provide apiEndpoint
    // CustomSelect will use the existing options from the DOM
    return [
      {
        selector: ".input-field.custom-select",
        placeholder: "Select parent category...",
        fieldName: "parent_id",
        // No apiEndpoint - will use static HTML options
        onSelect: (value, text, item) => {
          this.logger.debug("Parent category selected:", { value, text });
          if (this.formHandler) {
            this.formHandler.validateField("parent_id", value);
          }
        }
      }
    ];
  }

  onSuccess(result, context) {
    this.logger.success("Category form submitted successfully", result);

    if (this.options.resetOnSuccess && result.operation === "insert") {
      this.formHandler?.form?.reset();
      this.resetAllCustomSelects();
      this.dropzoneInstances.forEach((dz) => dz.reset?.());
    }
  }

  async _init() {
    await super._init();
    this.initPriceRangeBrackets();
  }

  initPriceRangeBrackets() {
    try {
      this.priceRangeBrackets = new PriceRangeBrackets();
      this.logger.debug("PriceRangeBrackets initialized successfully");
    } catch (error) {
      this.logger.warn("Failed to initialize PriceRangeBrackets:", error);
    }
  }

  destroy() {
    if (this.priceRangeBrackets) {
      this.priceRangeBrackets.destroy();
      this.priceRangeBrackets = null;
    }
    super.destroy();
  }
}

// Initialize only for Category form
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    const categoryForm = document.querySelector(
      'form[data-validation-rules*="categoryRules"], form#category-frm'
    );
    if (categoryForm) {
      window.CategoryMain = new CategoryMain();
      window.CategoryMain._init();
    }
  });
} else {
  const categoryForm = document.querySelector(
    'form[data-validation-rules*="categoryRules"], form#category-frm'
  );
  if (categoryForm) {
    window.CategoryMain = new CategoryMain();
    window.CategoryMain._init();
  }
}

export default CategoryMain;
