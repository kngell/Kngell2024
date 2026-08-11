import BaseResponseProcessor from "./BaseResponseProcessor";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("RedirectProcessor");

export default class RedirectProcessor extends BaseResponseProcessor {
  constructor(options = {}) {
    super();
    this.options = {
      redirectOnInsert: options.redirectOnInsert ?? true,
      redirectOnUpdate: options.redirectOnUpdate ?? false,
      redirectOnDelete: options.redirectOnDelete ?? true,
      operationDelays: {
        insert: options.operationDelays?.insert ?? 1500,
        update: options.operationDelays?.update ?? 0,
        delete: options.operationDelays?.delete ?? 1500,
        ...options.operationDelays
      },
      delays: {
        info: 2000,
        warning: 2500,
        success: 1500,
        error: 1000,
        danger: 1000,
        ...options.delays
      },
      ...options
    };

    this.logger = options.logger || console;
  }

  handle(context) {
    const { result } = context;

    // ✅ Check for redirect
    if (!result.redirect) {
      logger.debug("No redirect URL found in response");
      return;
    }

    logger.debug(`Redirect URL found: ${result.redirect}`);

    // ✅ Determine operation
    const operation = this.determineOperation(context);
    logger.debug(`Detected operation: ${operation}`);

    // ✅ Check if redirect should happen for this operation
    const shouldRedirectForOperation = this.shouldRedirectForOperation(operation);
    logger.debug(`Should redirect for ${operation}? ${shouldRedirectForOperation}`, {
      redirectOnInsert: this.options.redirectOnInsert,
      redirectOnUpdate: this.options.redirectOnUpdate,
      redirectOnDelete: this.options.redirectOnDelete
    });

    if (!shouldRedirectForOperation) {
      logger.debug(`Skipping redirect for ${operation} operation`);
      context.shouldRedirect = false;
      context.redirectSkipped = true;
      context.redirectSkipReason = `operation: ${operation} not allowed`;
      return;
    }

    // ✅ Set up redirect
    const type = result.type ? String(result.type).toLowerCase() : "";
    const isSuccess = result.success !== false;

    if (!isSuccess || type === "danger" || type === "error") {
      logger.debug(`Not redirecting - response is not success (type: ${type})`);
      context.shouldRedirect = false;
      context.redirectSkipped = true;
      context.redirectSkipReason = `not success or error type: ${type}`;
      return;
    }

    // ✅ CRITICAL: Set these properties for BaseHandler._executeRedirect
    const delay = this.getOperationDelay(operation) || this.getDelay(result.type) || 1500;

    context.shouldRedirect = true;
    context.redirectUrl = result.redirect; // ✅ This was the issue - using result.redirect not result.redirectUrl
    context.redirectDelay = delay;
    context.redirectOperation = operation;

    logger.debug(`Redirect configured for ${operation}`, {
      url: context.redirectUrl,
      delay: delay,
      shouldRedirect: context.shouldRedirect
    });
  }

  determineOperation(context) {
    const { result, form } = context;

    // ✅ Check result.operation first - it's 'INSERT' in your response
    if (result.operation) {
      const op = String(result.operation).toLowerCase();
      logger.debug(`Found operation from result: ${op}`);
      if (op === "insert" || op === "create") return "insert";
      if (op === "update" || op === "edit" || op === "modify") return "update";
      if (op === "delete" || op === "destroy" || op === "deletion") return "delete";
      return op;
    }

    // ✅ Check for deletion indicators
    if (result.data?.deletion_type || result.deletedId || result.data?.deleted) {
      return "delete";
    }

    // ✅ Check for insert/update indicators
    if (result.insertId || result.data?.insertId) return "insert";
    if (result.updatedId || result.data?.updatedId) return "update";
    if (result.Category_id || result.data?.Category_id) return "insert";

    // ✅ Check if this is a deletion form
    if (form && form.getAttribute("id") === "confirm-deletion-frm") {
      return "delete";
    }

    // ✅ Check form for ID presence (existing record = update)
    if (form) {
      const idField = form.querySelector(
        'input[name="id"], input[name="pdt_id"], input[name="category_id"]'
      );
      if (idField && idField.value && idField.value !== "0") {
        return "update";
      }
    }

    return "insert";
  }

  shouldRedirectForOperation(operation) {
    const op = (operation || "").toLowerCase();

    switch (op) {
      case "insert":
        return this.options.redirectOnInsert;
      case "update":
        return this.options.redirectOnUpdate;
      case "delete":
        return this.options.redirectOnDelete;
      default:
        return false;
    }
  }

  getOperationDelay(operation) {
    const op = (operation || "").toLowerCase();
    return this.options.operationDelays[op];
  }

  getDelay(type) {
    const t = (type || "").toLowerCase();
    return this.options.delays[t] || 1500;
  }
}
