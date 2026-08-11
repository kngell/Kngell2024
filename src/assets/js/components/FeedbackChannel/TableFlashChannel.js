import FeedbackChannel from "js/core/Contracts/FeedbackChannelInterface";
import FlashMessage from "js/components/Flash/FlashMessage";
import BrowserLogger from "js/core/utils/BrowserLogger";

/**
 * TableFlashChannel — renders flash messages inline at the top of a specific table.
 *
 * Container insertion strategy:
 *   - Inserts a <div class="table-flash-container"> as the immediate previous
 *     sibling of the table element.
 *   - Idempotent: re-uses an existing sibling container if one already exists.
 *   - aria-live region is set per message type (assertive for errors, polite otherwise).
 */
export default class TableFlashChannel extends FeedbackChannel {
  constructor(tableSelector, options = {}) {
    super();
    this.logger = new BrowserLogger("TableFlashChannel");
    this.tableSelector = tableSelector;

    this.options = {
      durations: {
        success: 4000,
        error: 0, // sticky by default — errors should not auto-dismiss
        warning: 6000,
        info: 4000
      },
      containerClass: "flash-container",
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
    this.flash = null;
    this._ensureContainer();
  }

  _ensureContainer() {
    const table = document.querySelector(this.tableSelector);
    if (!table) {
      this.logger.warn(`Table not found: ${this.tableSelector}`);
      return;
    }

    const parent = table.parentElement;
    if (!parent) {
      this.logger.warn(`Table has no parent: ${this.tableSelector}`);
      return;
    }

    let container = parent.querySelector(`:scope > .${this.options.containerClass}`);
    if (!container) {
      container = document.createElement("div");
      container.className = this.options.containerClass;
      // Generic live region; per-message role/aria-live is set on the alert itself
      container.setAttribute("aria-live", "polite");
      container.setAttribute("aria-atomic", "true");
      parent.insertBefore(container, table);
    }

    this.container = container;

    // Tear down any previous instance before re-creating (idempotent re-init)
    if (this.flash) {
      this.flash.destroy();
    }
    this.flash = new FlashMessage({ container });
  }

  _show(type, message, overrides = {}) {
    // Re-acquire container if it's been removed from the DOM (e.g., partial re-render)
    if (!this.flash || !this.container || !this.container.isConnected) {
      this._ensureContainer();
    }
    if (!this.flash) {
      this.logger.warn("Cannot show flash — container unavailable");
      return;
    }

    this.flash.show({
      type,
      message,
      duration: this.options.durations[type] ?? 0,
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
    // Note: we intentionally do NOT remove the container from the DOM,
    // because another channel instance may re-attach. Callers that need
    // hard cleanup can call container.remove() externally.
    this.container = null;
  }
}
