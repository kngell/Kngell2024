import FeedbackChannel from "js/core/Contracts/FeedbackChannelInterface";
import NotificationHelper from "js/core/helpers/NotificationHelper";

const DEFAULT_CONFIG = {
  error: { permanent: true, duration: 8000 },
  success: { permanent: false, duration: 3000 },
  warning: { permanent: false, duration: 5000 },
  info: { permanent: false, duration: 5000 }
};

export default class NotificationChannel extends FeedbackChannel {
  constructor(options = {}) {
    super();

    this.config = {
      error: { ...DEFAULT_CONFIG.error, ...(options.error || {}) },
      success: { ...DEFAULT_CONFIG.success, ...(options.success || {}) },
      warning: { ...DEFAULT_CONFIG.warning, ...(options.warning || {}) },
      info: { ...DEFAULT_CONFIG.info, ...(options.info || {}) }
    };

    this.helper = new NotificationHelper({
      position: options.position || "top-right",
      containerId: options.containerId || "app-notifications",
      maxNotifications: options.maxNotifications || 5,
      defaultDuration: options.defaultDuration || 5000
    });

    this._lastNotification = { message: null, type: null, time: 0 };
  }

  show(message, type = "info", overrides = {}) {
    if (!message) return;

    const now = Date.now();
    if (
      this._lastNotification.message === message &&
      this._lastNotification.type === type &&
      now - this._lastNotification.time < 2000
    ) {
      return;
    }

    const config = { ...this.config[type], ...overrides };

    this.helper.show(message, type, {
      permanent: config.permanent,
      duration: config.duration,
      ...overrides
    });

    this._lastNotification = { message, type, time: now };
  }

  success(message, options = {}) {
    this.show(message, "success", options);
  }

  error(message, options = {}) {
    this.show(message, "error", options);
  }

  warning(message, options = {}) {
    this.show(message, "warning", options);
  }

  info(message, options = {}) {
    this.show(message, "info", options);
  }

  /**
   * Close all notifications
   */
  closeAll() {
    this.helper.closeAll();
  }

  /**
   * Clean up
   */
  destroy() {
    this.helper.destroy();
  }
}
