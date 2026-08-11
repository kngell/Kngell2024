import BaseFormManager from "js/components/Managers/BaseFormManager";

class HeroMain extends BaseFormManager {
  constructor(options = {}) {
    super({
      enableDropzone: true,
      enableCustomSelect: false,
      enableRadioOptions: true,
      enableActionBar: true,
      resetOnSuccess: options.resetOnSuccess || true,
      notificationContainerId: options.notificationContainerId || "hero-notifications",

      // ✅ Flash configuration - HeroMain knows where its flash should go
      flashSelector: options.flashSelector || ".hero-form",
      flashContainerClass: "flash-container hero-flash",
      flashPosition: "prepend",
      flashDurations: {
        success: 3000,
        error: 0,
        warning: 5000,
        info: 4000
      },

      notificationConfig: {
        error: { permanent: true, duration: 8000 },
        success: { permanent: false, duration: 3000 }
      },

      ...options
    });
  }

  getFlashSelector() {
    return this.getFormSelector();
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

  getDeletionModalConfig() {
    return {
      onEntityDeleted: (entityId, result) => {
        this.logger.success("Hero deleted:", entityId);
        if (result.redirect) {
          window.location.href = result.redirect;
        }
      },
      notificationConfig: {
        error: { permanent: true, duration: 8000 },
        success: { permanent: false, duration: 3000 }
      }
    };
  }

  getActionBarConfig() {
    return {
      addButtonSelector: ".btn-add-hero",
      deleteButtonSelector: ".btn-delete-hero"
    };
  }

  onEntityDeleted(entityId, result) {
    this.logger.success("Hero deleted:", entityId);
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
