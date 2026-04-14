import BaseFormManager from "js/components/Managers/BaseFormManager";

class SmallBannerMain extends BaseFormManager {
  constructor(options = {}) {
    super({
      enableDropzone: true,
      enableCustomSelect: true,
      enableRadioOptions: true,
      enableToggleSwitch: true,
      resetOnSuccess: options.resetOnSuccess || true,
      notificationPosition: options.notificationPosition || "top-right",
      maxNotifications: options.maxNotifications || 3,
      notificationDuration: options.notificationDuration || 5000,
      notificationContainerId: options.notificationContainerId || "small-banner-notifications",

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
    return "small-banner-notifications";
  }

  getFormSelector() {
    return 'form[data-validate="true"][data-validation-rules*="smallBannerRules"], form#small-banner-form';
  }

  getValidationRules() {
    return "smallBannerRules";
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
