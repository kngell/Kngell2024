import BaseFormManager from "js/components/Managers/BaseFormManager";
import ProductCard from "js/components/custom-select/product-card";

class ContentBlockMain extends BaseFormManager {
  constructor(options = {}) {
    super({
      enableDropzone: true,
      enableCustomSelect: true,
      enableRadioOptions: true,
      enableActionBar: true,
      resetOnSuccess: options.resetOnSuccess || true,
      resetCustomSelectsOnSuccess: true,
      notificationContainerId: options.notificationContainerId || "content-block-notifications",
      channelStrategy: "flash",
      flashSelector: options.flashSelector || ".content-block__body",
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
    return "content-block-notifications";
  }

  getFormSelector() {
    return 'form[data-validate="true"][data-validation-rules*="contentBlock"], form#content-block-frm';
  }

  getValidationRules() {
    return "contentBlockRules";
  }

  getCustomSelectConfigs() {
    return [
      {
        selector: ".input-field.custom-select",
        dataSource: "/admin/content-block-search/load-products",
        placeholder: "Search Product by name or SKU...",
        fieldName: "product_id",
        pageSize: 20,
        enableSearch: true,
        enableInfiniteScroll: true
      }
    ];
  }

  async initSpecificComponents() {
    this.productCard = new ProductCard(".form-section.product-relationship .form-section__body", {
      customSelectSelector: ".input-field.custom-select",
      emptyTitle: "No product selected",
      emptyMessage: "Select a product to view details"
    });
  }

  getActionBarConfig() {
    return {
      addButtonSelector: ".btn-add-content-block",
      deleteButtonSelector: ".btn-delete-content-block"
    };
  }

  onEntityDeleted(entityId, result) {
    this.logger.success("Content Block deleted:", entityId);
  }

  onSuccess(result, context) {
    this.logger.success("Content Block form submitted successfully");

    if (this.options.resetOnSuccess && result.operation === "insert") {
      this.formHandler?.form?.reset();
      this.dropzoneInstances.forEach((dz) => dz.reset?.());
    }
  }

  onBeforeDelete(context) {
    this.logger.debug("Before deleting Content Block:", context);
    return true;
  }

  updateProductDetails(product) {
    const priceField = document.querySelector("#product-price");
    const skuField = document.querySelector("#product-sku");

    if (priceField) priceField.value = product.price;
    if (skuField) skuField.value = product.sku;
  }

  clearProductDetails() {
    const priceField = document.querySelector("#product-price");
    const skuField = document.querySelector("#product-sku");

    if (priceField) priceField.value = "";
    if (skuField) skuField.value = "";
  }

  destroy() {
    if (this.productCard) {
      this.productCard.destroy();
      this.productCard = null;
    }
    super.destroy();
  }
}

const initContentBlockMain = () => {
  if (!window.ContentBlockMainInstance) {
    window.ContentBlockMainInstance = new ContentBlockMain();
    window.ContentBlockMainInstance._init();
  }
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initContentBlockMain);
} else {
  initContentBlockMain();
}

export default ContentBlockMain;
