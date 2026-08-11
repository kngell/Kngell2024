import FeedbackChannel from "js/core/Contracts/FeedbackChannelInterface";
import FlashMessage from "js/components/Flash/FlashMessage";
import BrowserLogger from "js/core/utils/BrowserLogger";

export default class FlashChannel extends FeedbackChannel {
  constructor(selector, options = {}) {
    super();
    this.logger = new BrowserLogger("FlashChannel");
    this.selector = selector;
    this.options = {
      durations: {
        success: 4000,
        error: 0,
        warning: 6000,
        info: 4000
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

    // ✅ Use provided container or find/create one
    this.container = options.container || null;
    this.flash = null;

    if (this.container) {
      this._initWithContainer(this.container);
    } else {
      this._ensureContainer();
    }
  }

  _ensureContainer() {
    let container = document.querySelector(`.${this.options.containerClass}`);

    if (container) {
      this.logger.debug(`Found existing flash container: .${this.options.containerClass}`);
      this.container = container;
      if (this.options.autoHide) {
        this.container.style.display = "none";
      }
      this._initFlashMessage();
      return;
    }

    const element = document.querySelector(this.selector);
    if (element) {
      const parent = element.parentElement;
      if (parent) {
        container = parent.querySelector(`:scope > .${this.options.containerClass}`);
        if (!container) {
          container = document.createElement("div");
          container.className = this.options.containerClass;
          container.setAttribute("aria-live", "polite");
          container.setAttribute("aria-atomic", "true");
          parent.insertBefore(container, element);
          this.logger.debug(`Created container in ${this.selector}`);
        }
      }
    }

    if (!container) {
      container = document.createElement("div");
      container.className = this.options.containerClass;
      container.setAttribute("aria-live", "polite");
      container.setAttribute("aria-atomic", "true");
      document.body.prepend(container);
      this.logger.warn(`Container created at body (fallback)`);
    }

    this.container = container;
    if (this.options.autoHide) {
      this.container.style.display = "none";
    }
    this._initFlashMessage();
  }

  _initWithContainer(container) {
    this.container = container;
    if (this.options.autoHide) {
      this.container.style.display = "none";
    }
    this._initFlashMessage();
    this.logger.debug(`Using provided container`);
  }

  _initFlashMessage() {
    if (this.flash) {
      this.flash.destroy();
    }
    this.flash = new FlashMessage({
      container: this.container,
      dismissible: this.options.dismissible,
      showIcon: this.options.showIcon,
      showProgress: this.options.showProgress,
      pauseOnHover: this.options.pauseOnHover
    });
  }

  _show(type, message, overrides = {}) {
    if (!this.flash || !this.container || !this.container.isConnected) {
      this._ensureContainer();
    }
    if (!this.flash) {
      this.logger.warn("Cannot show flash — container unavailable");
      return;
    }

    if (this.options.autoHide && this.container) {
      this.container.style.display = "block";
    }

    this.flash.show({
      type,
      message,
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
    this.flash?.clear();
  }

  destroy() {
    this.flash?.destroy();
    this.flash = null;
    this.container = null;
  }
}
