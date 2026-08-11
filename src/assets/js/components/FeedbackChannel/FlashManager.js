import BrowserLogger from "js/core/utils/BrowserLogger";
import MessageHandler from "./Utils/MessageHandler";
import FlashChannel from "./FlashChannel";

export default class FlashManager {
  constructor(options = {}) {
    if (FlashManager.instance) {
      return FlashManager.instance;
    }

    this.logger = new BrowserLogger("FlashManager");

    // ─── Configuration ──────────────────────────────────────────
    this.options = {
      defaultContainerClass: options.defaultContainerClass || "flash-container",
      modalDelay: options.modalDelay || 300,
      modalSelectors: [
        ".modal-overlay",
        ".modal-overlay.active",
        ".modal",
        ".modal-container",
        ".modal-body",
        "[data-modal]",
        "[data-modal-close]",
        ".modal-header",
        ".modal-footer"
      ],
      events: {
        "entity:saved": true,
        "entity:save-error": true,
        "entity:deleted": true,
        "entity:delete-error": true
      },
      durations: {
        success: options.successDuration || 5000,
        error: options.errorDuration || 0,
        warning: options.warningDuration || 5000,
        info: options.infoDuration || 4000
      },
      autoHide: options.autoHide !== false,
      dismissible: options.dismissible !== false,
      showIcon: options.showIcon !== false,
      showProgress: options.showProgress !== false,
      pauseOnHover: options.pauseOnHover !== false,
      ...options
    };

    // ─── State ──────────────────────────────────────────────────
    this.messageHandler = null;
    this._channelMap = new Map();
    this._containerCache = new Map();

    FlashManager.instance = this;
    this.logger.debug("FlashManager initialized");
  }

  /**
   * Get or create a flash channel for a component
   *
   * @param {string} componentId - Unique ID for the component
   * @param {Object} options - Channel options
   * @param {string} options.selector - CSS selector for container placement (USER SPECIFIED)
   * @param {string} options.containerClass - CSS class for the container
   * @param {string} options.position - 'prepend', 'append', 'before', 'after', 'fixed-top'
   * @param {Object} options.durations - Duration for each message type
   * @returns {FlashChannel} The flash channel
   */
  getChannel(componentId, options = {}) {
    if (this._channelMap.has(componentId)) {
      return this._channelMap.get(componentId);
    }

    const channel = this._createChannel(componentId, options);
    this._channelMap.set(componentId, channel);

    if (!this.messageHandler) {
      this.messageHandler = new MessageHandler(channel, {
        enabled: true,
        modalDelay: this.options.modalDelay,
        modalSelectors: this.options.modalSelectors,
        events: this.options.events
      });
      this.logger.debug("MessageHandler created with first channel");
    }

    return channel;
  }

  /**
   * Create a flash channel with smart container management
   *
   * Priority order:
   * 1. User-specified selector (highest priority - developer intent)
   * 2. Server-provided container in DOM
   * 3. Create container above the form
   * 4. Create container at the top of the body (ultimate fallback)
   */
  _createChannel(componentId, options = {}) {
    const userSelector = options.selector || null;
    const containerClass = options.containerClass || this.options.defaultContainerClass;

    // ─── PRIORITY 1: User-specified selector ────────────────────
    // If the developer specified a selector, use it FIRST
    if (userSelector) {
      this.logger.debug(`[${componentId}] User specified selector: ${userSelector}`);

      // Check if container already exists at the user's selector
      let container = this._findContainerAtSelector(userSelector, containerClass);

      if (container) {
        // Container exists at user's location - use it
        this.logger.debug(
          `[${componentId}] Using existing container at user selector: ${userSelector}`
        );
        return this._createChannelWithContainer(componentId, container, options);
      }

      // No container at user's selector - create one there
      container = this._createContainerAtSelector(userSelector, containerClass, options);
      if (container) {
        this.logger.debug(`[${componentId}] Created container at user selector: ${userSelector}`);
        return this._createChannelWithContainer(componentId, container, options);
      }

      // If we couldn't create at user selector, log warning and continue
      this.logger.warn(
        `[${componentId}] Could not create container at user selector: ${userSelector}, trying fallbacks`
      );
    }

    // ─── PRIORITY 2: Server-provided container ──────────────────
    // If no user selector, or user selector failed, look for server container
    let container = this._findExistingContainer(containerClass);
    if (container) {
      this.logger.debug(`[${componentId}] Using server-provided container: .${containerClass}`);
      return this._createChannelWithContainer(componentId, container, options);
    }

    // ─── PRIORITY 3: Create container above the form ────────────
    const form = document.querySelector('form[data-validate="true"]');
    if (form) {
      container = this._createContainerAboveForm(form, containerClass, options);
      if (container) {
        this.logger.debug(`[${componentId}] Created container above form (fallback 3)`);
        return this._createChannelWithContainer(componentId, container, options);
      }
    }

    // ─── PRIORITY 4: Create container at the top of the body ────
    container = this._createContainerAtBody(containerClass, options);
    this.logger.debug(`[${componentId}] Created container at body (ultimate fallback)`);
    return this._createChannelWithContainer(componentId, container, options);
  }

  /**
   * Find an existing container at a specific selector
   */
  _findContainerAtSelector(selector, containerClass) {
    const targetElement = document.querySelector(selector);
    if (!targetElement) {
      return null;
    }

    // Look for container inside the target element
    const container = targetElement.querySelector(`.${containerClass}`);
    if (container) {
      return container;
    }

    // Look for container as a sibling (before or after)
    const siblings = targetElement.parentElement?.querySelectorAll(`.${containerClass}`);
    if (siblings) {
      for (const sibling of siblings) {
        if (sibling.parentElement === targetElement.parentElement) {
          return sibling;
        }
      }
    }

    return null;
  }

  /**
   * Find an existing flash container anywhere in the DOM
   */
  _findExistingContainer(containerClass) {
    return document.querySelector(`.${containerClass}`);
  }

  /**
   * Create a container at a specific selector
   */
  _createContainerAtSelector(selector, containerClass, options = {}) {
    const targetElement = document.querySelector(selector);
    if (!targetElement) {
      return null;
    }

    // Check if container already exists inside the target
    let container = targetElement.querySelector(`.${containerClass}`);
    if (container) {
      return container;
    }

    // Check if container exists as a sibling
    const siblings = targetElement.parentElement?.querySelectorAll(`.${containerClass}`);
    if (siblings) {
      for (const sibling of siblings) {
        if (sibling.parentElement === targetElement.parentElement) {
          // Move the container to the correct position relative to target
          const position = options.position || "prepend";
          this._placeContainer(sibling, targetElement, position);
          return sibling;
        }
      }
    }

    // Create new container
    container = this._createContainerElement(containerClass, options);
    const position = options.position || "prepend";
    this._placeContainer(container, targetElement, position);

    return container;
  }

  /**
   * Create a container above a form
   */
  _createContainerAboveForm(form, containerClass, options = {}) {
    // Check if container already exists above the form
    let container = form.parentElement?.querySelector(`:scope > .${containerClass}`);
    if (container) {
      return container;
    }

    // Create new container
    container = this._createContainerElement(containerClass, options);
    form.parentElement.insertBefore(container, form);

    return container;
  }

  /**
   * Create a container at the top of the body
   */
  _createContainerAtBody(containerClass, options = {}) {
    // Check if container already exists in body
    let container = document.querySelector(`body > .${containerClass}`);
    if (container) {
      return container;
    }

    // Create new container
    container = this._createContainerElement(containerClass, options);

    // Place at top of body
    const body = document.body;
    if (body.firstChild) {
      body.insertBefore(container, body.firstChild);
    } else {
      body.appendChild(container);
    }

    if (options.position === "fixed-top") {
      container.style.position = "fixed";
      container.style.top = "0";
      container.style.left = "0";
      container.style.right = "0";
      container.style.zIndex = "9999";
      container.style.padding = "10px 20px";
    }

    return container;
  }

  /**
   * Create a container element
   */
  _createContainerElement(containerClass, options = {}) {
    const container = document.createElement("div");
    container.className = containerClass;
    container.setAttribute("aria-live", "polite");
    container.setAttribute("aria-atomic", "true");

    const autoHide = options.autoHide !== undefined ? options.autoHide : this.options.autoHide;
    if (autoHide) {
      container.style.display = "none";
    }

    return container;
  }

  /**
   * Place a container relative to a target element
   */
  _placeContainer(container, targetElement, position) {
    switch (position) {
      case "before":
        targetElement.parentElement.insertBefore(container, targetElement);
        break;
      case "after":
        targetElement.parentElement.insertBefore(container, targetElement.nextSibling);
        break;
      case "prepend":
        targetElement.prepend(container);
        break;
      case "append":
      default:
        targetElement.appendChild(container);
        break;
    }
  }

  /**
   * Create a FlashChannel with an existing container
   */
  _createChannelWithContainer(componentId, container, options = {}) {
    const selector = options.selector || "body";
    const containerClass = options.containerClass || this.options.defaultContainerClass;

    const channel = new FlashChannel(selector, {
      durations: options.durations || this.options.durations,
      containerClass: containerClass,
      autoHide: options.autoHide !== undefined ? options.autoHide : this.options.autoHide,
      dismissible:
        options.dismissible !== undefined ? options.dismissible : this.options.dismissible,
      showIcon: options.showIcon !== undefined ? options.showIcon : this.options.showIcon,
      showProgress:
        options.showProgress !== undefined ? options.showProgress : this.options.showProgress,
      pauseOnHover:
        options.pauseOnHover !== undefined ? options.pauseOnHover : this.options.pauseOnHover,
      container: container
    });

    this._containerCache.set(componentId, container);

    return channel;
  }

  getFlashChannel(componentId) {
    return this._channelMap.get(componentId);
  }

  show(componentId, type, message, options = {}) {
    const channel = this._channelMap.get(componentId);
    if (channel) {
      channel._show(type, message, options);
    } else {
      this.logger.warn(`No channel found for component: ${componentId}`);
    }
  }

  getMessageHandler() {
    return this.messageHandler;
  }

  unregisterComponent(componentId) {
    const channel = this._channelMap.get(componentId);
    if (channel) {
      channel.destroy();
      this._channelMap.delete(componentId);
      this._containerCache.delete(componentId);
      this.logger.debug(`Component unregistered: ${componentId}`);
    }
  }

  destroy() {
    if (this.messageHandler) {
      this.messageHandler.destroy();
    }
    for (const [id, channel] of this._channelMap) {
      channel.destroy();
    }
    this._channelMap.clear();
    this._containerCache.clear();
    FlashManager.instance = null;
  }

  static getInstance(options = {}) {
    if (!FlashManager.instance) {
      FlashManager.instance = new FlashManager(options);
    }
    return FlashManager.instance;
  }
}

// ─── Convenience Exports ──────────────────────────────────────

let instance = null;

export function getFlashManager(options = {}) {
  if (!instance) {
    instance = new FlashManager(options);
  }
  return instance;
}

export function getFlashChannel(componentId) {
  const manager = getFlashManager();
  return manager.getFlashChannel(componentId);
}
