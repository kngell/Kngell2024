import BaseFormManager from "js/components/Managers/BaseFormManager";

class HeroMain extends BaseFormManager {
  constructor(options = {}) {
    super({
      enableDropzone: true,
      enableCustomSelect: false,
      enableRadioOptions: true,
      enableActionBar: true, // Make sure action bar is enabled
      resetOnSuccess: options.resetOnSuccess || true,
      notificationContainerId: options.notificationContainerId || "hero-notifications",

      notificationConfig: {
        error: { permanent: true, duration: 8000 },
        success: { permanent: false, duration: 3000 }
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

  // IMPORTANT: Override deletion modal config
  getDeletionModalConfig() {
    return {
      onEntityDeleted: (entityId, result) => {
        this.logger.success("Hero deleted:", entityId);
        // Optionally redirect or remove from DOM
        if (result.redirect) {
          window.location.href = result.redirect;
        }
      },
      // You can also add custom notification config for deletion
      notificationConfig: {
        error: { permanent: true, duration: 8000 },
        success: { permanent: false, duration: 3000 }
      }
    };
  }

  // Override action bar config if needed
  getActionBarConfig() {
    return {
      addButtonSelector: ".btn-add-hero",
      deleteButtonSelector: ".btn-delete-hero"
      // Add any custom selectors for your hero page
    };
  }

  onEntityDeleted(entityId, result) {
    this.logger.success("Hero deleted:", entityId);
    // Additional cleanup if needed
  }

  onSuccess(result, context) {
    this.logger.success("Hero form submitted successfully");

    if (this.options.resetOnSuccess && result.operation === "insert") {
      this.formHandler?.form?.reset();
      this.dropzoneInstances.forEach((dz) => dz.reset?.());
    }
  }

  onBeforeDelete(context) {
    this.logger.debug("Before deleting hero:", context);
    // Return false to cancel deletion
    return true;
  }
}

const initHeroMain = () => {
  if (!window.heroMainInstance) {
    window.heroMainInstance = new HeroMain();
    window.heroMainInstance._init();
  }
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initHeroMain);
} else {
  initHeroMain();
}

export default HeroMain;
