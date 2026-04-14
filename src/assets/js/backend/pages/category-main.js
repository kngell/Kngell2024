import BaseFormManager from "js/components/Managers/BaseFormManager";

class CategoryMain extends BaseFormManager {
  constructor(options = {}) {
    super({
      enableDropzone: true,
      enableCustomSelect: true,
      enableRadioOptions: false,
      enableToggleSwitch: false,
      resetOnSuccess: options.resetOnSuccess || true,
      notificationPosition: options.notificationPosition || "top-right",
      maxNotifications: options.maxNotifications || 3,
      notificationDuration: options.notificationDuration || 5000,
      notificationContainerId: options.notificationContainerId || "category-notifications",

      notificationConfig: {
        error: {
          permanent: true,
          duration: 8000
        },
        success: {
          permanent: false,
          duration: 3000
        }
      },
      ...options
    });
  }
  getDefaultNotificationContainerId() {
    return "category-notifications";
  }
  getFormSelector() {
    return 'form[data-validate="true"][data-validation-rules*="categoryRules"], form#category-form';
  }
  getValidationRules() {
    return "categoryRules";
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
      this.resetCustomSelects();
      this.dropzoneInstances.forEach((dz) => dz.reset?.());
    }
  }
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    window.SmallBannerMain = new CategoryMain();
    window.SmallBannerMain._init();
  });
} else {
  window.SmallBannerMain = new CategoryMain();
  window.SmallBannerMain._init();
}

export default CategoryMain;
