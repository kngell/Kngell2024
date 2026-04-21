import BaseFormManager from "js/components/Managers/BaseFormManager";
import ProductSelector from "js/components/custom-select/ProductSelector";

class SmallBannerMain extends BaseFormManager {
  constructor(options = {}) {
    super({
      enableDropzone: true,
      enableCustomSelect: true,
      enableRadioOptions: true,
      enableToggleSwitch: true,
      resetOnSuccess: options.resetOnSuccess || true,
      resetCustomSelectsOnSuccess: true,
      notificationPosition: options.notificationPosition || "top-right",
      maxNotifications: options.maxNotifications || 3,
      notificationDuration: options.notificationDuration || 5000,
      notificationContainerId: options.notificationContainerId || "small-banner-notifications",
      ...options
    });

    this.productSelectors = [];
  }

  getDefaultNotificationContainerId() {
    return "small-banner-notifications";
  }

  getFormSelector() {
    return 'form[data-validate="true"][data-validation-rules*="smallBannerRules"], form#small-banner-form';
  }

  getValidationRules() {
    return "smallBannerRules";
  }

  getCustomSelectConfigs() {
    return [
      {
        selector: ".input-field.custom-select",
        apiEndpoint: "/small-banner-search/load-products",
        placeholder: "Search Product by name or SKU...",
        fieldName: "product_id",
        itemFormatter: (item) => (item.sku ? `${item.name} (${item.sku})` : item.name),
        onSelect: (value, text, item) => {
          this.logger.debug("Product selected via CustomSelect:", { value, text });
        }
      }
    ];
  }

  async initSpecificComponents() {
    this.logger.debug("Initializing ProductSelector...");

    // Wait a tick for CustomSelect to be fully initialized
    await new Promise((resolve) => setTimeout(resolve, 50));

    this.initProductSelector();
  }

  initProductSelector() {
    const selector = ".input-field.custom-select";
    const customSelect = this.getCustomSelect(selector);

    if (!customSelect) {
      this.logger.warn(`CustomSelect not found for selector: ${selector}`);
      this.logger.debug(
        `Available custom selects:`,
        this.customSelects.map((cs) => cs.selector)
      );
      return;
    }

    this.logger.debug(`Found CustomSelect for ${selector}, initializing ProductSelector...`);

    try {
      const productSelector = new ProductSelector(selector, customSelect, {
        onSelect: (value, text, item) => {
          this.logger.debug("Product selected:", { value, text });
          if (this.formHandler) {
            this.formHandler.validateField("product_id", value);
          }
        },
        onReset: () => {
          this.logger.debug("Product selection reset");
          if (this.formHandler) {
            this.formHandler.validateField("product_id", null);
          }
        }
      });

      productSelector.init();
      this.productSelectors.push(productSelector);
      this.logger.success("ProductSelector initialized successfully");
    } catch (error) {
      this.logger.error("Failed to initialize ProductSelector", error);
    }
  }

  onRadioChange(event) {
    this.logger.debug("Theme preference changed:", event.value);
  }

  onSuccess(result, context) {
    this.logger.success("Small Banner form submitted successfully", {
      theme_preference: this.radioOptions?.getValue(),
      result
    });

    if (this.options.resetOnSuccess && result.operation === "insert") {
      this.formHandler?.form?.reset();
      this.productSelectors.forEach((selector) => selector.reset());
      this.dropzoneInstances.forEach((dz) => dz.reset?.());
    }
  }

  destroy() {
    this.productSelectors.forEach((selector) => selector.destroy());
    this.productSelectors = [];
    super.destroy();
  }
}

// Auto-initialize
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    window.SmallBannerMain = new SmallBannerMain();
    window.SmallBannerMain._init();
  });
} else {
  window.SmallBannerMain = new SmallBannerMain();
  window.SmallBannerMain._init();
}

export default SmallBannerMain;
