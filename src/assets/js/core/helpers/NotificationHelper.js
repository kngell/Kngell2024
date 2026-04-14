import BrowserLogger from "js/core/utils/BrowserLogger";

export default class NotificationHelper {
  static instance = null;

  constructor(options = {}) {
    // If instance exists, update its container if needed but return existing instance
    if (NotificationHelper.instance) {
      this.logger = new BrowserLogger("NotificationHelper");
      this.logger.debug("Returning existing NotificationHelper instance");

      // Update container if provided
      if (options.containerId && !NotificationHelper.instance.getContainer()) {
        NotificationHelper.instance.containerId = options.containerId;
        NotificationHelper.instance.containerSelector =
          options.containerSelector || `.notification-container.${options.containerId}`;
        NotificationHelper.instance.position = options.position || "top-right";
        NotificationHelper.instance.init();
      }

      return NotificationHelper.instance;
    }

    this.logger = new BrowserLogger("NotificationHelper");

    // Default configuration
    this.containerId = options.containerId || "notification-container";
    this.containerSelector = options.containerSelector || ".notification-container";
    this.defaultDuration = options.defaultDuration || 5000;
    this.maxNotifications = options.maxNotifications || 5;
    this.position = options.position || "top-right";

    this.options = options;
    this.notifications = new Map(); // Store active notifications
    this.init();

    NotificationHelper.instance = this;
  }

  init() {
    if (!this.getContainer()) {
      this.createContainer();
    }
    this.addStyles();
  }
  getContainer() {
    const container =
      document.querySelector(this.containerSelector) || document.getElementById(this.containerId);
    console.log("🔔 getContainer result:", container);
    return container;
  }

  // getContainer() {
  //   return (
  //     document.querySelector(this.containerSelector) || document.getElementById(this.containerId)
  //   );
  // }

  /**
   * Create notification container
   */
  createContainer() {
    const container = document.createElement("div");
    container.id = this.containerId;
    container.className = `notification-container notification-container--${this.position}`;

    // Position styles
    const positionStyles = {
      "top-right": "top: 20px; right: 20px;",
      "top-left": "top: 20px; left: 20px;",
      "bottom-right": "bottom: 20px; right: 20px;",
      "bottom-left": "bottom: 20px; left: 20px;",
      "top-center": "top: 20px; left: 50%; transform: translateX(-50%);"
    };

    container.style.cssText = `
            position: fixed;
            ${positionStyles[this.position] || positionStyles["top-right"]}
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
            width: 100%;
            pointer-events: none;
        `;

    document.body.appendChild(container);
    return container;
  }

  show(message, type = "info", options = {}) {
    console.log("🔔 Showing notification:", { message, type, options });

    const {
      duration = this.defaultDuration,
      permanent = false,
      undoable = false,
      onUndo = null,
      onClose = null,
      id = `notification-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
    } = options;

    this.logger.info(`Notification [${type}]:`, message);

    // Manage notification limit
    this.enforceNotificationLimit();

    const notification = this.createNotificationElement(id, message, type, {
      permanent,
      undoable,
      onUndo,
      onClose
    });

    const container = this.getContainer();
    console.log("🔔 Container found:", container);

    if (!container) {
      console.error("🔔 No container found! Creating one...");
      this.createContainer();
      // Don't reassign container - get it fresh
      const newContainer = this.getContainer();
      if (!newContainer) {
        console.error("🔔 Still no container after creation!");
        return id;
      }
      newContainer.appendChild(notification);
    } else {
      console.log("🔔 Appending notification to container");
      container.appendChild(notification);
    }

    console.log("🔔 Container children count:", container?.children.length || 0);

    // FIX: Store timeout separately and ensure we don't close immediately
    let timeoutId = null;

    // Only set timeout for non-permanent notifications with positive duration
    if (!permanent && duration > 0) {
      timeoutId = setTimeout(() => {
        console.log(`🔔 Auto-closing notification ${id} after ${duration}ms`);
        this.close(id);
      }, duration);
    }

    // Store notification with timeout
    this.notifications.set(id, {
      element: notification,
      type,
      message,
      permanent,
      timeout: timeoutId
    });

    // Trigger enter animation
    setTimeout(() => {
      console.log("🔔 Adding show class to notification");
      notification.classList.add("show");
    }, 10);

    return id;
  }
  // show(message, type = "info", options = {}) {
  //   const {
  //     duration = this.defaultDuration,
  //     permanent = false,
  //     undoable = false,
  //     onUndo = null,
  //     onClose = null,
  //     id = `notification-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`,
  //   } = options;

  //   this.logger.info(`Notification [${type}]:`, message);

  //   // Manage notification limit
  //   this.enforceNotificationLimit();

  //   const notification = this.createNotificationElement(id, message, type, {
  //     permanent,
  //     undoable,
  //     onUndo,
  //     onClose,
  //   });

  //   const container = this.getContainer();
  //   container.appendChild(notification);

  //   // Store notification
  //   this.notifications.set(id, {
  //     element: notification,
  //     type,
  //     message,
  //     permanent,
  //     timeout:
  //       !permanent && duration
  //         ? setTimeout(() => {
  //             this.close(id);
  //           }, duration)
  //         : null,
  //   });

  //   // Trigger enter animation
  //   setTimeout(() => notification.classList.add("show"), 10);

  //   return id;
  // }

  /**
   * Show success notification
   */
  success(message, options = {}) {
    return this.show(message, "success", options);
  }

  /**
   * Show error notification
   */
  error(message, options = {}) {
    return this.show(message, "error", options);
  }

  /**
   * Show warning notification
   */
  warning(message, options = {}) {
    return this.show(message, "warning", options);
  }

  /**
   * Show info notification
   */
  info(message, options = {}) {
    return this.show(message, "info", options);
  }

  /**
   * Show permanent notification
   */
  permanent(message, type = "info", options = {}) {
    return this.show(message, type, { ...options, permanent: true });
  }

  /**
   * Show undoable success notification
   */
  successWithUndo(message, onUndo, options = {}) {
    return this.show(message, "success", {
      ...options,
      undoable: true,
      onUndo,
      permanent: options.permanent !== undefined ? options.permanent : true
    });
  }

  createNotificationElement(id, message, type, { permanent, undoable, onUndo, onClose }) {
    const notification = document.createElement("div");
    notification.id = id;
    notification.className = `notification notification--${type} ${permanent ? "permanent" : ""}`;

    const colors = {
      success: "#10b981",
      error: "#ef4444",
      warning: "#f59e0b",
      info: "#3b82f6"
    };

    // STRONGER INLINE STYLES to ensure visibility
    notification.style.cssText = `
    padding: 16px 20px;
    border-radius: 8px;
    color: white;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
    display: flex !important;
    align-items: center;
    gap: 12px;
    background-color: ${colors[type] || colors.info};
    border-left: 4px solid rgba(255,255,255,0.5);
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    pointer-events: auto;
    position: relative;
    width: 100%;
    margin-bottom: 10px;
    z-index: 10001;
  `;

    // Icon
    const icon = document.createElement("span");
    icon.textContent = this.getIcon(type);
    icon.style.cssText = `
            font-size: 18px;
            font-weight: bold;
            flex-shrink: 0;
            line-height: 1.4;
        `;
    notification.appendChild(icon);

    // Content wrapper
    const content = document.createElement("div");
    content.style.cssText = `
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        `;

    // Message
    const messageEl = document.createElement("span");
    messageEl.textContent = message;
    messageEl.style.cssText = `
            line-height: 1.5;
            word-break: break-word;
            padding-right: 20px;
        `;
    content.appendChild(messageEl);

    // Actions
    if (undoable || permanent) {
      const actions = document.createElement("div");
      actions.style.cssText = `
                display: flex;
                gap: 12px;
                margin-top: 4px;
            `;

      if (undoable) {
        const undoBtn = document.createElement("button");
        undoBtn.textContent = "Undo";
        undoBtn.className = "notification-undo-btn";
        undoBtn.style.cssText = `
                    background: rgba(255,255,255,0.2);
                    border: 1px solid rgba(255,255,255,0.3);
                    color: white;
                    padding: 6px 14px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 12px;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    transition: all 0.2s;
                `;
        undoBtn.addEventListener("mouseenter", () => {
          undoBtn.style.background = "rgba(255,255,255,0.3)";
        });
        undoBtn.addEventListener("mouseleave", () => {
          undoBtn.style.background = "rgba(255,255,255,0.2)";
        });
        undoBtn.addEventListener("click", (e) => {
          e.stopPropagation();
          if (onUndo) onUndo();
          this.close(id);
        });
        actions.appendChild(undoBtn);
      }

      content.appendChild(actions);
    }

    notification.appendChild(content);

    // Close button
    const closeBtn = document.createElement("button");
    closeBtn.innerHTML = "&times;";
    closeBtn.className = "notification-close";
    closeBtn.style.cssText = `
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0 4px;
            opacity: 0.7;
            transition: opacity 0.2s;
            flex-shrink: 0;
            line-height: 1;
            position: absolute;
            top: 12px;
            right: 12px;
        `;
    closeBtn.addEventListener("mouseenter", () => (closeBtn.style.opacity = "1"));
    closeBtn.addEventListener("mouseleave", () => (closeBtn.style.opacity = "0.7"));
    closeBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      this.close(id);
      if (onClose) onClose();
    });

    notification.appendChild(closeBtn);

    return notification;
  }

  close(id) {
    const notification = this.notifications.get(id);
    if (!notification) return;

    // Clear timeout
    if (notification.timeout) {
      clearTimeout(notification.timeout);
    }

    // Animate out
    const element = notification.element;
    element.classList.remove("show");
    element.style.transform = "translateX(100%)";
    element.style.opacity = "0";

    // Remove after animation
    setTimeout(() => {
      if (element.parentNode) {
        element.remove();
      }
      this.notifications.delete(id);
    }, 300);
  }

  /**
   * Close all notifications
   */
  closeAll() {
    this.notifications.forEach((_, id) => {
      this.close(id);
    });
  }

  /**
   * Enforce maximum notification limit
   */
  enforceNotificationLimit() {
    if (this.notifications.size >= this.maxNotifications) {
      // Remove oldest notification
      const oldestId = Array.from(this.notifications.keys())[0];
      this.close(oldestId);
    }
  }

  /**
   * Get icon based on notification type
   */
  getIcon(type) {
    const icons = {
      success: "✓",
      error: "✗",
      warning: "⚠",
      info: "ℹ"
    };
    return icons[type] || icons.info;
  }

  /**
   * Add global styles
   */
  addStyles() {
    if (document.getElementById("notification-helper-styles")) return;

    const style = document.createElement("style");
    style.id = "notification-helper-styles";
    style.textContent = `
            .notification.show {
                opacity: 1 !important;
                transform: translateX(0) !important;
            }
            
            .notification-container--top-center .notification {
                transform: translateY(-100%);
            }
            
            .notification-container--top-center .notification.show {
                transform: translateY(0) !important;
            }
            
            .notification-container--bottom-right .notification,
            .notification-container--bottom-left .notification {
                transform: translateX(100%);
            }
            
            .notification-container--bottom-right .notification.show,
            .notification-container--bottom-left .notification.show {
                transform: translateX(0) !important;
            }
            
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateX(100%);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            @keyframes slideOut {
                from {
                    opacity: 1;
                    transform: translateX(0);
                }
                to {
                    opacity: 0;
                    transform: translateX(100%);
                }
            }
            
            .notification.deleting {
                animation: slideOut 0.3s ease-in forwards;
            }
        `;
    document.head.appendChild(style);
  }

  /**
   * Destroy instance
   */
  destroy() {
    this.closeAll();
    this.logger.debug("NotificationHelper destroyed");
  }
}
