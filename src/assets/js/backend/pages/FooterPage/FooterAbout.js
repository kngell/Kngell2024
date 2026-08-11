import BrowserLogger from "js/core/utils/BrowserLogger";
import BaseFormManager from "js/components/Managers/BaseFormManager";
import FooterAboutHeader from "./FooterAboutHeader";

const logger = new BrowserLogger("FooterAbout");

class FooterAbout extends BaseFormManager {
  constructor(options = {}) {
    super({
      enableDropzone: true,
      enableCustomSelect: false,
      enableRadioOptions: true,
      enableActionBar: true,
      resetOnSuccess: options.resetOnSuccess || true,
      notificationContainerId: options.notificationContainerId || "footer-notifications",

      channelStrategy: "flash", // 'flash', 'notification', or 'auto'

      // Flash channel configuration
      flashSelector: options.flashSelector || ".footer-page__body",
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
    this.initFooterAboutHeader();
  }
  initFooterAboutHeader = () => {
    new FooterAboutHeader();
    logger.debug("FooterAboutHeader initialized");
  };
  getChannelStrategy() {
    return this.options.channelStrategy;
  }
  getFlashSelector() {
    return this.getFormSelector();
  }
  getDefaultNotificationContainerId() {
    return "footer-notifications";
  }

  getFormSelector() {
    return 'form[data-validate="true"][data-validation-rules*="footerAboutRules"], form#footer-about-frm-id';
  }

  getValidationRules() {
    return "footerAboutRules";
  }

  getDeletionModalConfig() {
    return {
      onEntityDeleted: (entityId, result) => {
        this.logger.success("Footer About deleted:", entityId);
        // Optionally redirect or remove from DOM
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

  // Override action bar config if needed
  getActionBarConfig() {
    return {
      addButtonSelector: ".btn-add-about",
      deleteButtonSelector: ".btn-delete-about"
    };
  }

  onEntityDeleted(entityId, result) {
    this.logger.success("About deleted:", entityId);
    // Additional cleanup if needed
  }

  onSuccess(result, context) {
    this.logger.success("About form submitted successfully");

    if (this.options.resetOnSuccess && result.operation === "insert") {
      this.formHandler?.form?.reset();
      this.dropzoneInstances.forEach((dz) => dz.reset?.());
    }
  }

  onBeforeDelete(context) {
    this.logger.debug("Before deleting footer about:", context);
    // Return false to cancel deletion
    return true;
  }
}

export default FooterAbout;
