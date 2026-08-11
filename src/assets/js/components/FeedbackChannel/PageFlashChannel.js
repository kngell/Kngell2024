import FeedbackChannel from "js/core/Contracts/FeedbackChannelInterface";
import FlashMessage from "js/components/Flash/FlashMessage";
import BrowserLogger from "js/core/utils/BrowserLogger";

/**
 * PageFlashChannel — renders flash messages at the top of a page container.
 *
 * Uses FlashMessage component for consistent rendering across all channels.
 * Container auto-hides when no messages are present.
 */
export default class PageFlashChannel extends FeedbackChannel {
  constructor(selector, options = {}) {
    super();
    this.logger = new BrowserLogger("PageFlashChannel");
    this.selector = selector;
    this.options = {
      durations: {
        success: 8000,
        error: 0,
        warning: 8000,
        info: 8000
      },
      containerClass: "flash-container",
      autoHide: true,
      dismissible: true,
      showIcon: true,
      showProgress: true,
      pauseOnHover: true,
      ...options,
      durations: {
        success: 4000,
        error: 0,
        warning: 6000,
        info: 4000,
        ...(options.durations || {})
      }
    };

    this.container = null;
    this.flashMessage = null;
    this._messageCount = 0;
    this._ensureContainer();
  }

  _ensureContainer() {
    // Check if a flash container already exists
    let container = document.querySelector(`.${this.options.containerClass}`);

    if (container) {
      this.logger.debug(`Found existing flash container: .${this.options.containerClass}`);
      this.container = container;

      // Hide container initially if autoHide is enabled
      if (this.options.autoHide) {
        this.container.style.display = "none";
      }

      // Create FlashMessage instance with the container
      if (this.flashMessage) {
        this.flashMessage.destroy();
      }
      this.flashMessage = new FlashMessage({
        container: this.container,
        dismissible: this.options.dismissible,
        showIcon: this.options.showIcon,
        showProgress: this.options.showProgress,
        pauseOnHover: this.options.pauseOnHover
      });
      return;
    }

    // If no container exists, try to create one
    const element = document.querySelector(this.selector);
    if (!element) {
      this.logger.warn(`Element not found: ${this.selector}`);
      return;
    }

    const parent = element.parentElement;
    if (!parent) {
      this.logger.warn(`Element has no parent: ${this.selector}`);
      return;
    }

    container = document.createElement("div");
    container.className = this.options.containerClass;
    container.setAttribute("aria-live", "polite");
    container.setAttribute("aria-atomic", "true");

    if (this.options.autoHide) {
      container.style.display = "none";
    }

    parent.insertBefore(container, element);

    this.container = container;
    if (this.flashMessage) {
      this.flashMessage.destroy();
    }
    this.flashMessage = new FlashMessage({
      container: this.container,
      dismissible: this.options.dismissible,
      showIcon: this.options.showIcon,
      showProgress: this.options.showProgress,
      pauseOnHover: this.options.pauseOnHover
    });

    this.logger.debug(`Created new flash container: .${this.options.containerClass}`);
  }

  _show(type, message, overrides = {}) {
    // Re-acquire container if it's been removed from the DOM
    if (!this.flashMessage || !this.container || !this.container.isConnected) {
      this._ensureContainer();
    }
    if (!this.flashMessage) {
      this.logger.warn("Cannot show flash — container unavailable");
      return;
    }

    // Show container before adding message
    if (this.options.autoHide && this.container) {
      this.container.style.display = "block";
    }

    this._messageCount++;

    // Use FlashMessage to show the message
    this.flashMessage.show({
      message: message,
      type: type,
      duration: this.options.durations[type] ?? 0,
      clearPrevious: false,
      ...overrides
    });
  }

  success(message, options) {
    this._show("success", message, options);
  }

  error(message, options) {
    this._show("error", message, options);
  }

  warning(message, options) {
    this._show("warning", message, options);
  }

  info(message, options) {
    this._show("info", message, options);
  }

  clear() {
    if (this.flashMessage) {
      this.flashMessage.clear();
    }
    this._messageCount = 0;
    if (this.options.autoHide && this.container) {
      this.container.style.display = "none";
    }
  }

  destroy() {
    if (this.flashMessage) {
      this.flashMessage.destroy();
      this.flashMessage = null;
    }
    this.container = null;
    this._messageCount = 0;
  }
}
