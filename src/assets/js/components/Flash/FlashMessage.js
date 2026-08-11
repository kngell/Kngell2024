import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("FlashMessage");

const TYPE_TO_CLASS = {
  success: "success",
  danger: "danger",
  error: "danger",
  warning: "warning",
  info: "info"
};

const TYPE_TO_ICON = {
  success: "icon-check-circle",
  danger: "icon-error",
  warning: "icon-warning",
  info: "icon-info"
};

const DEFAULT_ICON_SPRITE = "/public/assets/img/icons-sprite.svg";
const DISMISS_FALLBACK_MS = 400;

export default class FlashMessage {
  // ───────────────────────────────────────────────────────────
  // Static: Auto-init (enhance server-rendered flashes)
  // ───────────────────────────────────────────────────────────

  /**
   * Call on DOMContentLoaded to enhance server-rendered flash messages
   * with animations, auto-dismiss, and event listeners.
   */
  static init(root = document) {
    root.querySelectorAll(".flash-message-js:not([data-flash-initialized])").forEach((el) => {
      el.dataset.flashInitialized = "true";

      const duration = parseInt(el.dataset.flashDuration, 10);
      if (duration && duration > 0) {
        el.style.setProperty("--flash-duration", `${duration}ms`);
        FlashMessage._setupAutoDismiss(el, duration);
      }

      const closeBtn = el.querySelector("[data-flash-dismiss]");
      if (closeBtn) {
        closeBtn.addEventListener("click", () => FlashMessage._dismiss(el));
      }
    });
  }
  static showMany(messages, opts = {}) {
    if (!Array.isArray(messages) || messages.length === 0) return;

    const container = opts.container || FlashMessage._getOrCreateToastContainer();
    const flash = new FlashMessage({ ...opts, container });

    messages.forEach((msg) => flash.show({ ...msg, clearPrevious: false }));
  }
  static _getOrCreateToastContainer() {
    let el = document.querySelector(".flash-container--toast");
    if (!el) {
      el = document.createElement("div");
      el.className = "flash-container flash-container--toast";
      el.setAttribute("aria-live", "polite");
      el.setAttribute("aria-atomic", "true");
      document.body.appendChild(el);
    }
    return el;
  }
  // ───────────────────────────────────────────────────────────
  // Instance API (for programmatic/dynamic use)
  // ───────────────────────────────────────────────────────────

  constructor(opts = {}) {
    this.container = opts.container || null;
    this.dismissible = opts.dismissible !== false;
    this.scrollIntoView = opts.scrollIntoView !== false;
    this.toast = opts.toast === true;
    this.showIcon = opts.showIcon !== false;
    this.showProgress = opts.showProgress !== false;
    this.pauseOnHover = opts.pauseOnHover !== false;
    this.iconSprite = opts.iconSprite || DEFAULT_ICON_SPRITE;

    this._activeTimers = new Map();
    this._boundDismissHandler = (e) => this._onDismissClick(e);

    if (this.container) {
      this.container.classList.add("flash-container");
      if (this.toast) this.container.classList.add("flash-container--toast");
      this.container.addEventListener("click", this._boundDismissHandler);
    }
  }

  show(options = {}) {
    if (!this.container) {
      logger.warn("FlashMessage instance has no container");
      return null;
    }

    const {
      message,
      title = null,
      type = "success",
      duration = null,
      clearPrevious = true,
      icon = null
    } = options;

    if (!message) return null;
    if (clearPrevious) this.clear();

    const cssType = TYPE_TO_CLASS[type] || "info";
    const isError = cssType === "danger";
    const iconId = icon || TYPE_TO_ICON[type] || TYPE_TO_ICON.info;

    const alert = this._buildAlertElement({
      message,
      title,
      cssType,
      isError,
      iconId,
      duration
    });

    this.container.appendChild(alert);

    if (this.scrollIntoView) {
      requestAnimationFrame(() => {
        alert.scrollIntoView({ behavior: "smooth", block: "nearest" });
      });
    }

    if (duration && duration > 0) {
      FlashMessage._setupAutoDismiss(alert, duration, this.pauseOnHover);
    }

    return alert;
  }

  clear() {
    if (!this.container) return;
    this.container.querySelectorAll(".flash-message-js").forEach((el) => {
      FlashMessage._dismiss(el, true);
    });
  }

  destroy() {
    if (this.container && this._boundDismissHandler) {
      this.container.removeEventListener("click", this._boundDismissHandler);
    }
    this._activeTimers.forEach(({ timer }) => clearTimeout(timer));
    this._activeTimers.clear();
    this.clear();
    this.container = null;
  }

  // ───────────────────────────────────────────────────────────
  // Internal — DOM building
  // ───────────────────────────────────────────────────────────

  _buildAlertElement({ message, title, cssType, isError, iconId, duration }) {
    const alert = document.createElement("div");
    alert.className = `flash flash--${cssType} flash-message-js`;
    alert.setAttribute("role", isError ? "alert" : "status");
    alert.setAttribute("aria-live", isError ? "assertive" : "polite");
    alert.setAttribute("aria-atomic", "true");

    if (duration && duration > 0) {
      alert.dataset.flashDuration = String(duration);
      alert.style.setProperty("--flash-duration", `${duration}ms`);
    }

    // Icon container
    if (this.showIcon && iconId) {
      const iconContainer = document.createElement("div");
      iconContainer.className = "flash__icon-container";
      iconContainer.appendChild(this._buildSvgIcon("icon", iconId));
      alert.appendChild(iconContainer);
    }

    // Body
    const body = document.createElement("div");
    body.className = "flash__body";

    if (title) {
      const titleNode = document.createElement("span");
      titleNode.className = "flash__title";
      titleNode.textContent = title;
      body.appendChild(titleNode);
    }

    const textNode = document.createElement("span");
    textNode.className = "flash__text flash-message-js__text";
    textNode.textContent = message;
    body.appendChild(textNode);
    alert.appendChild(body);

    // Close
    if (this.dismissible) {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "flash__close";
      btn.setAttribute("aria-label", "Close");
      btn.setAttribute("data-flash-dismiss", "true");
      btn.appendChild(this._buildSvgIcon("icon", "icon-close"));
      alert.appendChild(btn);
    }

    // Progress
    if (this.showProgress && duration && duration > 0) {
      const progress = document.createElement("span");
      progress.className = "flash__progress";
      progress.setAttribute("aria-hidden", "true");
      alert.appendChild(progress);
    }

    return alert;
  }

  _buildSvgIcon(className, symbolId) {
    const svgNS = "http://www.w3.org/2000/svg";
    const xlinkNS = "http://www.w3.org/1999/xlink";
    const svg = document.createElementNS(svgNS, "svg");
    svg.setAttribute("class", className);
    svg.setAttribute("aria-hidden", "true");

    const use = document.createElementNS(svgNS, "use");
    const href = `${this.iconSprite}#${symbolId}`;
    use.setAttribute("href", href);
    use.setAttributeNS(xlinkNS, "xlink:href", href);
    svg.appendChild(use);
    return svg;
  }

  _onDismissClick(e) {
    const btn = e.target.closest("[data-flash-dismiss]");
    if (!btn) return;
    const alert = btn.closest(".flash-message-js");
    if (alert) FlashMessage._dismiss(alert);
  }

  // ───────────────────────────────────────────────────────────
  // Static helpers (used by both server-rendered & JS-injected)
  // ───────────────────────────────────────────────────────────

  static _setupAutoDismiss(element, duration, pauseOnHover = true) {
    const timer = setTimeout(() => FlashMessage._dismiss(element), duration);
    element._flashTimer = { timer, duration, remaining: duration, startedAt: Date.now() };

    if (pauseOnHover) {
      element.addEventListener("mouseenter", () => FlashMessage._pauseTimer(element));
      element.addEventListener("focusin", () => FlashMessage._pauseTimer(element));
      element.addEventListener("mouseleave", () => FlashMessage._resumeTimer(element));
      element.addEventListener("focusout", () => FlashMessage._resumeTimer(element));
    }
  }

  static _pauseTimer(element) {
    const entry = element._flashTimer;
    if (!entry || entry.paused) return;
    clearTimeout(entry.timer);
    const elapsed = Date.now() - entry.startedAt;
    entry.remaining = Math.max(0, entry.remaining - elapsed);
    entry.paused = true;
  }

  static _resumeTimer(element) {
    const entry = element._flashTimer;
    if (!entry || !entry.paused) return;
    entry.startedAt = Date.now();
    entry.paused = false;
    entry.timer = setTimeout(() => FlashMessage._dismiss(element), entry.remaining);
  }

  static _dismiss(element, immediate = false) {
    if (!element || !element.parentNode) return;

    if (element._flashTimer) {
      clearTimeout(element._flashTimer.timer);
      delete element._flashTimer;
    }

    if (immediate) {
      element.remove();
      return;
    }

    element.classList.add("flash--dismissing");

    let removed = false;
    const cleanup = () => {
      if (removed) return;
      removed = true;
      element.removeEventListener("animationend", cleanup);
      if (element.parentNode) element.remove();
    };

    element.addEventListener("animationend", cleanup, { once: true });
    setTimeout(cleanup, DISMISS_FALLBACK_MS);
  }

  // ───────────────────────────────────────────────────────────
  // Legacy static API (kept for backward compat)
  // ───────────────────────────────────────────────────────────

  static show(options = {}) {
    const { message, type = "success", target = null, clearPrevious = true } = options;
    let { position = "beforebegin" } = options;

    if (!message) return;

    let anchorElement = target;
    if (typeof target === "string") {
      anchorElement = document.querySelector(target);
    }

    if (!anchorElement) {
      anchorElement =
        document.querySelector("#flash-container") ||
        document.querySelector("main") ||
        document.body;
      if (!target) position = "afterbegin";
    }

    if (clearPrevious) {
      FlashMessage.clear(anchorElement, position);
    }

    const cssType = TYPE_TO_CLASS[type] || "info";
    const isError = cssType === "danger";

    const alertHtml = `
      <div class="flash flash--${cssType} flash-message-js"
           role="${isError ? "alert" : "status"}"
           aria-live="${isError ? "assertive" : "polite"}"
           aria-atomic="true">
        <div class="flash__body">
          <span class="flash__text flash-message-js__text">${FlashMessage._escapeHtml(message)}</span>
        </div>
        <button type="button" class="flash__close" data-flash-dismiss="true" aria-label="Close">
          <svg class="icon" aria-hidden="true">
            <use href="${DEFAULT_ICON_SPRITE}#icon-close"></use>
          </svg>
        </button>
      </div>
    `;

    anchorElement.insertAdjacentHTML(position, alertHtml);
  }

  static clear(anchorElement, position = "beforebegin") {
    if (!anchorElement) return;

    const searchArea =
      position === "beforebegin" || position === "afterend"
        ? anchorElement.parentElement
        : anchorElement;

    if (searchArea) {
      searchArea.querySelectorAll(".flash-message-js").forEach((el) => el.remove());
    }
  }

  static _escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = String(str);
    return div.innerHTML;
  }
}

// Auto-enhance server-rendered flashes on load
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => FlashMessage.init());
} else {
  FlashMessage.init();
}
