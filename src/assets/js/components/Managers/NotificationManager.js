import BrowserLogger from "js/core/utils/BrowserLogger";
import NotificationHelper from "js/core/helpers/NotificationHelper";

class NotificationManager {
  constructor(options = {}) {
    this.logger = new BrowserLogger("NotificationManager");

    // Default configuration
    this.defaults = {
      error: {
        permanent: true,
        duration: 8000,
        position: "top-right"
      },
      success: {
        permanent: false,
        duration: 3000,
        position: "top-right"
      },
      warning: {
        permanent: false,
        duration: 5000,
        position: "top-right"
      },
      info: {
        permanent: false,
        duration: 5000,
        position: "top-right"
      },
      ...options
    };

    // Initialize notification helper
    this.notificationHelper = new NotificationHelper({
      position: options.position || this.defaults.error.position,
      maxNotifications: options.maxNotifications || 5,
      defaultDuration: options.defaultDuration || 5000,
      containerId: options.containerId || "app-notifications",
      ...options
    });

    // For duplicate prevention
    this._lastNotification = {
      message: null,
      type: null,
      time: 0
    };

    this.logger.debug("NotificationManager initialized", this.defaults);
  }

  /**
   * Show error notification
   */
  error(message, options = {}) {
    const config = {
      ...this.defaults.error,
      ...options
    };

    this._show(message, "error", {
      permanent: config.permanent,
      duration: config.duration,
      position: config.position,
      ...options
    });
  }

  /**
   * Show success notification
   */
  success(message, options = {}) {
    const config = {
      ...this.defaults.success,
      ...options
    };

    this._show(message, "success", {
      permanent: config.permanent,
      duration: config.duration,
      position: config.position,
      ...options
    });
  }

  /**
   * Show warning notification
   */
  warning(message, options = {}) {
    const config = {
      ...this.defaults.warning,
      ...options
    };

    this._show(message, "warning", {
      permanent: config.permanent,
      duration: config.duration,
      position: config.position,
      ...options
    });
  }

  /**
   * Show info notification
   */
  info(message, options = {}) {
    const config = {
      ...this.defaults.info,
      ...options
    };

    this._show(message, "info", {
      permanent: config.permanent,
      duration: config.duration,
      position: config.position,
      ...options
    });
  }

  /**
   * Show notification with duplicate prevention
   */
  _show(message, type, options = {}) {
    const now = Date.now();
    const { permanent = false, duration = 5000, position, ...restOptions } = options;

    // Prevent duplicate notifications (same message and type within 2 seconds)
    const isDuplicate =
      this._lastNotification.message === message &&
      this._lastNotification.type === type &&
      now - this._lastNotification.time < 2000;

    if (isDuplicate) {
      this.logger.debug(`Suppressing duplicate notification: ${message} (${type})`);
      return;
    }

    // Show notification
    if (type === "error") {
      this.notificationHelper.error(message, {
        permanent,
        duration: permanent ? undefined : duration,
        ...restOptions
      });
    } else if (type === "success") {
      this.notificationHelper.success(message, {
        permanent,
        duration: permanent ? undefined : duration,
        ...restOptions
      });
    } else if (type === "warning") {
      this.notificationHelper.warning(message, {
        permanent,
        duration: permanent ? undefined : duration,
        ...restOptions
      });
    } else {
      this.notificationHelper.info(message, {
        permanent,
        duration: permanent ? undefined : duration,
        ...restOptions
      });
    }

    // Store for duplicate prevention
    this._lastNotification = {
      message,
      type,
      time: now
    };

    this.logger.debug(`Notification shown: ${message} (${type}, permanent: ${permanent})`);
  }

  /**
   * Get the underlying notification helper (for manual use if needed)
   */
  getHelper() {
    return this.notificationHelper;
  }

  /**
   * Destroy notification manager
   */
  destroy() {
    this.notificationHelper.destroy();
    this.logger.debug("NotificationManager destroyed");
  }
}

// Singleton instance
let instance = null;

export function getNotificationManager(options = {}) {
  if (!instance) {
    instance = new NotificationManager(options);
  } else if (options) {
    // Update existing instance with new config if needed
    instance.defaults = {
      ...instance.defaults,
      ...options
    };
  }
  return instance;
}

export default NotificationManager;
