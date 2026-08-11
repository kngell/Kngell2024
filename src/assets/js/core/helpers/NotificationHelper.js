import BrowserLogger from "js/core/utils/BrowserLogger";

export default class NotificationHelper {
  constructor(options = {}) {
    this.logger = new BrowserLogger("NotificationHelper");

    this.containerId = options.containerId || "app-notifications";
    this.position = options.position || "top-right";
    this.maxNotifications = options.maxNotifications || 5;
    this.defaultDuration = options.defaultDuration || 5000;

    this.notifications = new Map();
    this.init();
  }

  init() {
    if (!this.getContainer()) {
      this.createContainer();
    }
    this.addStyles();
  }

  getContainer() {
    return document.getElementById(this.containerId);
  }

  createContainer() {
    const container = document.createElement("div");
    container.id = this.containerId;
    container.className = `notification-container notification-container--${this.position}`;

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
    const {
      duration = this.defaultDuration,
      permanent = false,
      onClose = null,
      id = `notification-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
    } = options;

    // Enforce limit
    this.enforceNotificationLimit();

    const notification = this.createNotificationElement(id, message, type, { permanent, onClose });
    const container = this.getContainer();

    if (!container) {
      this.logger.error("No container found!");
      return id;
    }

    container.appendChild(notification);
    this.notifications.set(id, { element: notification, type, message, permanent });

    // Animate in
    setTimeout(() => notification.classList.add("show"), 10);

    // Auto-close if not permanent
    if (!permanent && duration > 0) {
      setTimeout(() => this.close(id), duration);
    }

    return id;
  }

  createNotificationElement(id, message, type, { permanent, onClose }) {
    const colors = {
      success: "#10b981",
      error: "#ef4444",
      warning: "#f59e0b",
      info: "#3b82f6"
    };

    const icons = {
      success: "✓",
      error: "✗",
      warning: "⚠",
      info: "ℹ"
    };

    const notification = document.createElement("div");
    notification.id = id;
    notification.className = `notification notification--${type}`;
    notification.style.cssText = `
      padding: 16px 20px;
      border-radius: 8px;
      color: white;
      font-size: 14px;
      font-weight: 500;
      box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
      display: flex;
      align-items: center;
      gap: 12px;
      background-color: ${colors[type] || colors.info};
      opacity: 0;
      transform: translateX(100%);
      transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
      pointer-events: auto;
      width: 100%;
    `;

    // Icon
    const icon = document.createElement("span");
    icon.textContent = icons[type] || icons.info;
    icon.style.cssText = "font-size: 18px; font-weight: bold; flex-shrink: 0;";
    notification.appendChild(icon);

    // Message
    const messageEl = document.createElement("span");
    messageEl.textContent = message;
    messageEl.style.cssText = "flex: 1; line-height: 1.5; word-break: break-word;";
    notification.appendChild(messageEl);

    // Close button
    const closeBtn = document.createElement("button");
    closeBtn.innerHTML = "&times;";
    closeBtn.style.cssText = `
      background: none;
      border: none;
      color: white;
      font-size: 24px;
      cursor: pointer;
      padding: 0 4px;
      opacity: 0.7;
      flex-shrink: 0;
    `;
    closeBtn.addEventListener("click", () => {
      this.close(id);
      if (onClose) onClose();
    });
    notification.appendChild(closeBtn);

    return notification;
  }

  close(id) {
    const notification = this.notifications.get(id);
    if (!notification) return;

    const element = notification.element;
    element.classList.remove("show");

    setTimeout(() => {
      element.remove();
      this.notifications.delete(id);
    }, 300);
  }

  closeAll() {
    this.notifications.forEach((_, id) => this.close(id));
  }

  enforceNotificationLimit() {
    if (this.notifications.size >= this.maxNotifications) {
      const oldestId = Array.from(this.notifications.keys())[0];
      this.close(oldestId);
    }
  }

  addStyles() {
    if (document.getElementById("notification-styles")) return;

    const style = document.createElement("style");
    style.id = "notification-styles";
    style.textContent = `
      .notification.show {
        opacity: 1 !important;
        transform: translateX(0) !important;
      }
    `;
    document.head.appendChild(style);
  }

  destroy() {
    this.closeAll();
    const container = this.getContainer();
    if (container) container.remove();
  }
}
