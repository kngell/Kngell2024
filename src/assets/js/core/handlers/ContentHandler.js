import BrowserLogger from "js/core/utils/BrowserLogger";
import AjaxHandler from "js/core/utils/AjaxHandler";
import { getFlashChannel } from "js/components/FeedbackChannel/FlashManager";
import BaseHandler from "./BaseHandler";

/**
 * ContentHandler - Handles AJAX responses for content pages (lists, settings, etc.)
 *
 * FOLLOWS SAME PATTERN AS FormHandler:
 * - Gets flash channel from FlashManager
 * - Uses NotificationProcessor and RedirectProcessor
 * - Dispatches entity events (MessageHandler in FlashManager listens)
 * - Handles redirects and flash messages consistently
 *
 * enableRedirectProcessor is the MASTER SWITCH:
 * - true (default): RedirectProcessor is added, redirects may happen
 * - false: RedirectProcessor is NEVER added, no redirects ever
 */
export default class ContentHandler extends BaseHandler {
  constructor(options = {}) {
    // ✅ Pass options to BaseHandler
    super({
      ...options,
      loggerName: "ContentHandler"
    });

    // ContentHandler-specific options
    this.options = {
      // ✅ Get flash channel from FlashManager
      feedbackChannel: options.feedbackChannel || getFlashChannel(),

      // Processor configuration
      processors: {
        enabled: true,
        notification: {
          enabled: true,
          config: {
            permanentErrors: true
          }
        },
        // Redirect config only matters if enableRedirectProcessor is true
        redirect: {
          enabled: true,
          config: {
            redirectOnInsert: true,
            redirectOnUpdate: false,
            redirectOnDelete: true,
            operationDelays: {
              insert: 1500,
              update: 0,
              delete: 1500
            }
          }
        },
        custom: []
      },

      // Event names
      entitySavedEvent: options.entitySavedEvent || "entity:saved",
      entitySaveErrorEvent: options.entitySaveErrorEvent || "entity:save-error",
      entityDeletedEvent: options.entityDeletedEvent || "entity:deleted",
      entityDeleteErrorEvent: options.entityDeleteErrorEvent || "entity:delete-error",

      // Callbacks
      onSuccess: options.onSuccess || null,
      onError: options.onError || null,

      // AJAX configuration
      ajaxOptions: options.ajaxOptions || {
        timeout: 30000,
        json: true,
        headers: {}
      },

      ...this.options,
      ...options
    };

    // ─── Create AjaxHandler ──────────────────────────────────────
    this.ajaxHandler = new AjaxHandler();

    // ─── Build processors using BaseHandler method ───────────────
    this.processors = this.buildProcessors();

    this._isProcessing = false;
  }

  /**
   * Make a request and process the response
   */
  async request(url, method = "GET", data = null, context = {}) {
    if (this._isProcessing) {
      this.logger.debug("Already processing a request, skipping");
      return null;
    }

    this._isProcessing = true;

    try {
      this.logger.debug(`Making ${method} request to ${url}`, { data, context });

      let response;
      const ajaxOptions = {
        ...this.options.ajaxOptions,
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          Accept: "application/json",
          ...this.options.ajaxOptions.headers
        }
      };

      switch (method.toUpperCase()) {
        case "GET":
          response = await this.ajaxHandler.get(url, data, ajaxOptions);
          break;
        case "POST":
          response = await this.ajaxHandler.post(url, data, ajaxOptions);
          break;
        case "PUT":
          response = await this.ajaxHandler.put(url, data, ajaxOptions);
          break;
        case "DELETE":
          response = await this.ajaxHandler.delete(url, data, ajaxOptions);
          break;
        default:
          response = await this.ajaxHandler.request({
            url,
            method,
            data,
            ...ajaxOptions
          });
      }

      return await this.processResponse(response, {
        ...context,
        method: method.toUpperCase()
      });
    } catch (error) {
      this.logger.error(`Request failed:`, error);
      this._dispatchErrorEvent(error, context);

      if (this.options.onError) {
        this.options.onError(error, context);
      }

      throw error;
    } finally {
      this._isProcessing = false;
    }
  }

  async get(url, context = {}) {
    return this.request(url, "GET", null, context);
  }

  async post(url, data, context = {}) {
    return this.request(url, "POST", data, context);
  }

  async put(url, data, context = {}) {
    return this.request(url, "PUT", data, context);
  }

  async delete(url, data, context = {}) {
    return this.request(url, "DELETE", data, context);
  }

  async processDelete(response, context = {}) {
    return this.processResponse(response, {
      ...context,
      operation: "delete"
    });
  }

  async processSave(response, context = {}) {
    return this.processResponse(response, {
      ...context,
      operation: context.operation || "update"
    });
  }

  async processInsert(response, context = {}) {
    return this.processResponse(response, {
      ...context,
      operation: "insert"
    });
  }

  destroy() {
    this._isProcessing = false;
    this.ajaxHandler = null;

    // Call parent destroy
    super.destroy();

    this.logger.debug("ContentHandler destroyed");
  }
}
