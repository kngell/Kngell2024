import BaseFormManager from "js/components/Managers/BaseFormManager";

class HeroMain extends BaseFormManager {
  constructor(options = {}) {
    super({
      enableDropzone: true,
      enableCustomSelect: false,
      enableRadioOptions: false,
      resetOnSuccess: options.resetOnSuccess || true,
      notificationPosition: options.notificationPosition || "top-right",
      maxNotifications: options.maxNotifications || 3,
      notificationDuration: options.notificationDuration || 5000,
      notificationContainerId: options.notificationContainerId || "hero-notifications",

      // Just pass configuration - FormHandler handles the rest
      notificationConfig: {
        error: {
          permanent: true, // Hero form errors are permanent
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
    return "hero-notifications";
  }

  getFormSelector() {
    return 'form[data-validate="true"][data-validation-rules*="hero"], form#hero-form';
  }

  getValidationRules() {
    return "heroRules";
  }

  onSuccess(result, context) {
    this.logger.success("Hero form submitted successfully");

    if (this.options.resetOnSuccess && result.operation === "insert") {
      this.formHandler?.form?.reset();
      this.dropzoneInstances.forEach((dz) => dz.reset?.());
    }
  }

  // No onError needed - FormHandler handles notifications
}

// Auto-initialize
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    window.HeroMain = new HeroMain();
    window.HeroMain._init();
  });
} else {
  window.HeroMain = new HeroMain();
  window.HeroMain._init();
}

export default HeroMain;
