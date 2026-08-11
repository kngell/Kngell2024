import BrowserLogger from "js/core/utils/BrowserLogger";
import NotificationProcessor from "js/core/processors/NotificationProcessor";
import RedirectProcessor from "js/core/processors/RedirectProcessor";
import { getFlashManager } from "js/components/FeedbackChannel/FlashManager";

export default class BaseHandler {
  constructor(options = {}) {
    this.logger = new BrowserLogger(options.loggerName || "BaseHandler");
    const flashManager = getFlashManager();

    if (options.feedbackChannel) {
      this.feedbackChannel = options.feedbackChannel;
      this.logger.debug("Using dedicated feedbackChannel");
    } else {
      const componentId = options.componentId || "base-handler-default";
      const flashSelector = options.flashSelector || "body";

      this.feedbackChannel = flashManager.getChannel(componentId, {
        selector: flashSelector,
        containerClass: options.containerClass || "flash-container",
        position: options.position || "prepend",
        durations: options.durations || {
          success: 5000,
          error: 0,
          warning: 5000,
          info: 4000
        },
        autoHide: options.autoHide !== false,
        dismissible: options.dismissible !== false,
        showIcon: options.showIcon !== false,
        showProgress: options.showProgress !== false,
        pauseOnHover: options.pauseOnHover !== false
      });
      this.logger.debug(`Using flash channel with selector: ${flashSelector}`);
    }

    this.options = {
      enableRedirectProcessor: options.enableRedirectProcessor !== false,

      processors: {
        enabled: true,
        notification: {
          enabled: true,
          config: {
            permanentErrors: true
          }
        },
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
      // entitySavedEvent: options.entitySavedEvent || "entity:saved",
      // entitySaveErrorEvent: options.entitySaveErrorEvent || "entity:save-error",
      // entityDeletedEvent: options.entityDeletedEvent || "entity:deleted",
      // entityDeleteErrorEvent: options.entityDeleteErrorEvent || "entity:delete-error",

      // Callbacks
      onSuccess: options.onSuccess || null,
      onError: options.onError || null,

      // Feedback channel
      feedbackChannel: this.feedbackChannel,

      ...options
    };

    // Processors array
    this.processors = [];

    // Log the redirect status
    this.logger.debug(
      `RedirectProcessor ${this.options.enableRedirectProcessor ? "ENABLED" : "DISABLED"}`
    );
  }

  buildProcessors() {
    const processorConfig = this.options.processors;

    if (!processorConfig.enabled) {
      this.logger.debug("Processors disabled");
      return [];
    }

    const processors = [];

    if (processorConfig.notification?.enabled !== false) {
      const notificationProcessor = new NotificationProcessor(
        this.options.feedbackChannel,
        processorConfig.notification?.config || {}
      );
      processors.push(notificationProcessor);
      this.logger.debug("NotificationProcessor added");
    }

    // ✅ RedirectProcessor - ONLY if master switch is true
    if (this.options.enableRedirectProcessor === true) {
      if (processorConfig.redirect?.enabled !== false) {
        const redirectProcessor = new RedirectProcessor(processorConfig.redirect?.config || {});
        processors.push(redirectProcessor);
        this.logger.debug("RedirectProcessor added", processorConfig.redirect?.config);
      }
    } else {
      this.logger.debug("RedirectProcessor NOT added - enableRedirectProcessor is false");
    }

    // ✅ Custom processors
    if (processorConfig.custom && processorConfig.custom.length) {
      processorConfig.custom.forEach((CustomProcessor) => {
        const processor =
          typeof CustomProcessor === "function" ? new CustomProcessor() : CustomProcessor;
        processors.push(processor);
        this.logger.debug(`Custom processor added: ${processor.constructor.name}`);
      });
    }

    this.logger.debug(`Total processors: ${processors.length}`);
    return processors;
  }
  _dispatchEntityEvent(eventName, detail) {
    this.logger.debug(`Dispatching ${eventName}`, detail);
    document.dispatchEvent(new CustomEvent(eventName, { detail }));
  }
  async processResponse(response, context = {}) {
    this.logger.debug("Processing response", { response, context });

    try {
      const processorContext = this._buildContext(response, context);

      // Run processors
      for (const processor of this.processors) {
        try {
          if (processor.canHandle && !processor.canHandle(processorContext)) {
            continue;
          }
          if (processor.handle) {
            await processor.handle(processorContext);
            this.logger.debug(`Processor ${processor.constructor.name} executed`);
          }
        } catch (error) {
          this.logger.error(`Processor ${processor.constructor.name} failed:`, error);
        }
      }

      // ✅ ONLY SOURCE of events - dispatch here
      this._dispatchEvents(processorContext);

      // Execute redirect if needed
      this._executeRedirect(processorContext);

      if (processorContext.isSuccess && this.options.onSuccess) {
        this.options.onSuccess(response, context);
      }

      return processorContext;
    } catch (error) {
      this.logger.error("Error processing response:", error);
      this._dispatchErrorEvent(error, context);

      if (this.options.onError) {
        this.options.onError(error, context);
      }

      throw error;
    }
  }
  _dispatchEvents(context) {
    const { isSuccess, operation, entityId, result } = context;

    if (isSuccess) {
      let eventName, eventData;

      // ✅ Try multiple sources for entityId
      const id =
        entityId || result?.data?.id || result?.id || (result?.data && result.data.id) || null;

      if (operation === "delete" || operation === "destroy") {
        eventName = "entity:deleted";
        eventData = {
          entityId: id,
          result: result,
          context: context,
          operation: operation,
          entityType: context.entityType || "unknown"
        };
      } else {
        eventName = "entity:saved";
        eventData = {
          entityId: id,
          result: result,
          context: context,
          operation: operation,
          data: result?.data || result,
          entityType: context.entityType || "unknown"
        };
      }

      this._dispatchEntityEvent(eventName, eventData);
    } else {
      const errorMessage = result?.error || result?.message || "Operation failed";

      // ✅ Dispatch via central method
      this._dispatchEntityEvent("entity:save-error", {
        error: {
          message: errorMessage,
          original: result,
          status: result?.status || null
        },
        operation: operation,
        context: context,
        result: result
      });
    }
  }
  _dispatchErrorEvent(error, context) {
    // ✅ Dispatch via central method
    this._dispatchEntityEvent("entity:save-error", {
      error: {
        message: error.message || "Processing failed",
        original: error
      },
      context: context
    });
  }

  _buildContext(response, context) {
    const operation = context.operation || this._determineOperation(response);
    const isSuccess = response?.success !== false;
    const entityId = context.entityId || response?.id || response?.data?.id || null;

    return {
      result: response,
      response: response,
      context: context,
      operation: operation,
      entityId: entityId,
      data: response?.data || response,
      isSuccess: isSuccess,
      success: isSuccess,
      error: response?.error || response?.message || null,
      form: context.form || null,
      shouldRedirect: false,
      redirectUrl: null,
      redirectDelay: 1500,
      type: isSuccess ? "success" : "error",
      message: response?.message || response?.error || null,
      metadata: context.metadata || {}
    };
  }
  _executeRedirect(context) {
    if (this.options.enableRedirectProcessor === false) {
      this.logger.debug("Redirect skipped - enableRedirectProcessor is false");
      return;
    }

    if (context.shouldRedirect && context.redirectUrl) {
      const delay = context.redirectDelay || 1500;
      this.logger.debug(`Redirecting to ${context.redirectUrl} in ${delay}ms`);
      setTimeout(() => {
        window.location.href = context.redirectUrl;
      }, delay);
    } else {
      // ✅ Add debug logging to see why redirect isn't happening
      this.logger.debug("Redirect not executed", {
        shouldRedirect: context.shouldRedirect,
        redirectUrl: context.redirectUrl,
        redirectDelay: context.redirectDelay,
        enableRedirectProcessor: this.options.enableRedirectProcessor
      });
    }
  }
  // _executeRedirect(context) {
  //   if (this.options.enableRedirectProcessor === false) {
  //     this.logger.debug("Redirect skipped - enableRedirectProcessor is false");
  //     return;
  //   }

  //   if (context.shouldRedirect && context.redirectUrl) {
  //     const delay = context.redirectDelay || 1500;
  //     this.logger.debug(`Redirecting to ${context.redirectUrl} in ${delay}ms`);
  //     setTimeout(() => {
  //       window.location.href = context.redirectUrl;
  //     }, delay);
  //   }
  // }

  _dispatchErrorEvent(error, context) {
    document.dispatchEvent(
      new CustomEvent("entity:save-error", {
        detail: {
          error: {
            message: error.message || "Processing failed",
            original: error
          },
          context: context
        }
      })
    );
    this.logger.debug(`Dispatched entity:save-error for MessageHandler`);
  }

  _determineOperation(response) {
    // ✅ Check operation from response (case insensitive)
    if (response?.operation) {
      const op = response.operation.toLowerCase();
      if (op === "delete" || op === "destroy" || op === "deletion") {
        return "delete";
      }
      if (op === "insert" || op === "create") {
        return "insert";
      }
      if (op === "update" || op === "edit" || op === "modify") {
        return "update";
      }
      return op;
    }

    // ✅ Check for deletion indicators in response
    if (response?.deletedId || response?.deletion_type || response?.deleted) {
      return "delete";
    }

    // Check for insert/update indicators
    if (response?.insertId) return "insert";
    if (response?.updatedId) return "update";

    // Check message for deletion keywords
    if (response?.message && typeof response.message === "string") {
      const msg = response.message.toLowerCase();
      if (msg.includes("deleted") || msg.includes("removed") || msg.includes("destroyed")) {
        return "delete";
      }
    }

    // Check if there's an ID and success
    if (response?.id && response?.success !== false) {
      return "insert";
    }

    return "update";
  }

  /**
   * Update processor configuration at runtime
   */
  updateProcessorConfig(processorType, config) {
    const processor = this.processors.find(
      (p) => p.constructor.name === `${processorType}Processor`
    );

    if (processor && processor.options) {
      Object.assign(processor.options, config);
      this.logger.debug(`Updated ${processorType}Processor config`, config);
    }
  }

  /**
   * Enable or disable redirects at runtime
   */
  setRedirectEnabled(enabled) {
    this.options.enableRedirectProcessor = enabled;
    this.logger.debug(`RedirectProcessor ${enabled ? "ENABLED" : "DISABLED"} at runtime`);
  }

  destroy() {
    this.processors = [];
    this.options.onSuccess = null;
    this.options.onError = null;
    this.logger.debug("BaseHandler destroyed");
  }
}
