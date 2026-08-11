import BrowserLogger from "js/core/utils/BrowserLogger";

let instance = null;

export default class MessageHandler {
  constructor(feedbackChannel, options = {}) {
    if (instance) {
      instance.feedbackChannel = feedbackChannel || instance.feedbackChannel;
      return instance;
    }

    this.feedbackChannel = feedbackChannel;
    this.options = {
      enabled: true,
      events: {
        "entity:saved": true,
        "entity:save-error": true,
        "entity:deleted": true,
        "entity:delete-error": true
      },
      modalDelay: options.modalDelay || 300,
      modalSelectors: options.modalSelectors || [
        ".modal-overlay",
        ".modal-overlay.active",
        ".modal",
        ".modal-container",
        ".modal-body",
        "[data-modal]",
        "[data-modal-close]"
      ],
      ...options
    };

    this.logger = new BrowserLogger("MessageHandler");
    this._boundHandlers = new Map();

    if (this.options.enabled) {
      this.start();
    }

    instance = this;
  }

  start() {
    const events = Object.keys(this.options.events).filter((event) => this.options.events[event]);

    events.forEach((eventName) => {
      const handler = (event) => this._handleEvent(eventName, event);
      this._boundHandlers.set(eventName, handler);
      document.addEventListener(eventName, handler);
    });

    this.logger.debug(`MessageHandler listening to: ${events.join(", ")}`);
  }

  stop() {
    for (const [eventName, handler] of this._boundHandlers) {
      document.removeEventListener(eventName, handler);
    }
    this._boundHandlers.clear();
  }

  _handleEvent(eventName, event) {
    const { detail } = event;
    let message, type;

    switch (eventName) {
      case "entity:saved":
        message = this._getSaveSuccessMessage(detail);
        type = detail.result?.flash?.type || "success";
        break;
      case "entity:save-error":
        message = this._getSaveErrorMessage(detail);
        type = "error";
        break;
      case "entity:deleted":
        message = detail.result?.message || "Item deleted successfully";
        type = detail.result?.flash?.type || "success";
        break;
      case "entity:delete-error":
        message = detail.message || detail.error?.message || "Failed to delete item";
        type = "error";
        break;
      default:
        return;
    }

    if (message && this.feedbackChannel) {
      const flashType = type || "info";
      const isModalOperation = this._isModalOperation();
      const delay = isModalOperation ? this.options.modalDelay : 0;

      this.logger.debug(
        `[${eventName}] ${flashType}: ${message} (modal: ${isModalOperation}, delay: ${delay}ms)`
      );

      setTimeout(() => {
        this._ensureContainerVisible();
        switch (flashType) {
          case "success":
            this.feedbackChannel.success(message);
            break;
          case "error":
          case "danger":
            this.feedbackChannel.error(message);
            break;
          case "warning":
            this.feedbackChannel.warning(message);
            break;
          case "info":
          default:
            this.feedbackChannel.info(message);
            break;
        }
        this.logger.debug(`[${eventName}] ${flashType}: ${message} (shown)`);
      }, delay);
    } else {
      this.logger.warn(`No feedback channel to show message: ${type} - ${message}`);
    }
  }

  /**
   * ✅ Detect if a modal operation is in progress by checking the DOM
   * Uses the same CSS classes that AbstractModalFormBuilder provides
   */
  _isModalOperation() {
    // Check for any modal overlay that is active
    for (const selector of this.options.modalSelectors) {
      const element = document.querySelector(selector);
      if (element) {
        // Check if the modal is visible/active
        const isVisible =
          element.offsetParent !== null ||
          element.classList.contains("active") ||
          element.style.display !== "none";

        if (isVisible) {
          this.logger.debug(`Modal detected via selector: ${selector}`);
          return true;
        }
      }
    }

    // Also check for modal classes in the body (some modals are at body level)
    const bodyHasModal =
      document.body.classList.contains("modal-open") ||
      document.body.querySelector(".modal-overlay, .modal, [data-modal]") !== null;

    if (bodyHasModal) {
      // Check if any modal element is visible
      const modalElements = document.querySelectorAll(".modal-overlay, .modal, [data-modal]");
      for (const el of modalElements) {
        const isVisible =
          el.offsetParent !== null ||
          el.classList.contains("active") ||
          el.style.display !== "none";
        if (isVisible) {
          this.logger.debug("Modal detected via body check");
          return true;
        }
      }
    }

    return false;
  }

  /**
   * Ensure the flash container is visible before showing a message
   */
  _ensureContainerVisible() {
    if (!this.feedbackChannel) return;

    // Try to get the container from the channel
    const container =
      this.feedbackChannel.container ||
      this.feedbackChannel._container ||
      document.querySelector(".flash-container");

    if (container) {
      // Show the container
      container.style.display = "block";
      container.style.visibility = "visible";
      container.style.opacity = "1";
      this.logger.debug("Flash container made visible");
    }
  }

  _getSaveSuccessMessage(detail) {
    const { result, operation } = detail;

    if (result?.message) {
      return result.message;
    }

    if (result?.was_skipped) {
      return "No changes were made";
    }

    if (operation === "insert") {
      return "Item created successfully";
    }

    if (operation === "update") {
      return "Item updated successfully";
    }

    return "Item saved successfully";
  }

  _getSaveErrorMessage(detail) {
    const { error, result, operation } = detail;

    if (result?.error) {
      return result.error;
    }

    if (error?.message) {
      return error.message;
    }

    return `Failed to ${operation || "save"} item`;
  }

  setFeedbackChannel(channel) {
    this.feedbackChannel = channel;
  }

  destroy() {
    this.stop();
    this.feedbackChannel = null;
    instance = null;
  }

  static getInstance() {
    return instance;
  }
}
