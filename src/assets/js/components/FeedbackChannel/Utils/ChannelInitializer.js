import NotificationChannel from "js/components/FeedbackChannel/NotificationChannel";
import FlashChannel from "js/components/FeedbackChannel/FlashChannel";
import BrowserLogger from "js/core/utils/BrowserLogger";

export default class ChannelInitializer {
  constructor(options = {}) {
    this.options = {
      notificationConfig: {
        error: { permanent: true, duration: 8000, position: "top-right" },
        success: { permanent: false, duration: 3000, position: "top-right" },
        warning: { permanent: false, duration: 5000, position: "top-right" },
        info: { permanent: false, duration: 5000, position: "top-right" }
      },
      flashConfig: {
        durations: {
          success: 4000,
          error: 0,
          warning: 6000,
          info: 4000
        },
        containerClass: "flash-container",
        // ✅ Include PageFlashChannel features
        autoHide: true,
        dismissible: true,
        showIcon: true,
        showProgress: true,
        pauseOnHover: true
      },
      ...options
    };

    this.logger = new BrowserLogger("ChannelInitializer");
  }

  createNotificationChannel(overrides = {}) {
    this.logger.debug("Creating NotificationChannel");

    return new NotificationChannel({
      containerId:
        overrides.containerId || this.options.notificationContainerId || "app-notifications",
      position: overrides.position || this.options.notificationPosition || "top-right",
      config: overrides.config || this.options.notificationConfig
    });
  }

  /**
   * Create flash channel using form selector
   * ✅ Enhanced with PageFlashChannel features
   */
  createFlashChannel(formSelector, overrides = {}) {
    this.logger.debug(`Creating FlashChannel for form: ${formSelector}`);

    return new FlashChannel(formSelector, {
      durations: overrides.durations || this.options.flashConfig.durations,
      containerClass: overrides.containerClass || this.options.flashConfig.containerClass,
      // ✅ Pass PageFlashChannel features
      autoHide: overrides.autoHide ?? this.options.flashConfig.autoHide,
      dismissible: overrides.dismissible ?? this.options.flashConfig.dismissible,
      showIcon: overrides.showIcon ?? this.options.flashConfig.showIcon,
      showProgress: overrides.showProgress ?? this.options.flashConfig.showProgress,
      pauseOnHover: overrides.pauseOnHover ?? this.options.flashConfig.pauseOnHover,
      ...overrides
    });
  }

  /**
   * Create channel based on strategy
   */
  createChannel(strategy, context = {}) {
    switch (strategy) {
      case "flash":
        return this.createFlashChannel(context.formSelector, context.flashConfig);

      case "notification":
        return this.createNotificationChannel(context);

      case "auto":
      default:
        return this._createAutoChannel(context);
    }
  }

  /**
   * Auto-detect which channel to use based on form attributes
   * @private
   */
  _createAutoChannel(context) {
    const { formSelector, form } = context;

    const formElement = form || (formSelector ? document.querySelector(formSelector) : null);

    if (
      formElement &&
      formElement.hasAttribute("data-feedback-channel") &&
      formElement.getAttribute("data-feedback-channel") === "flash"
    ) {
      this.logger.debug("Auto-detected: using FlashChannel (data attribute)");
      return this.createFlashChannel(formSelector, context.flashConfig);
    }

    if (formElement && formElement.classList.contains("use-flash-channel")) {
      this.logger.debug("Auto-detected: using FlashChannel (class)");
      return this.createFlashChannel(formSelector, context.flashConfig);
    }

    this.logger.debug("Auto-detected: using NotificationChannel");
    return this.createNotificationChannel(context);
  }
}
