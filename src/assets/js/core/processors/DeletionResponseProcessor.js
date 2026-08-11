import BaseResponseProcessor from "./BaseResponseProcessor";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("DeletionResponseProcessor");

export default class DeletionResponseProcessor extends BaseResponseProcessor {
  constructor() {
    super();
    this.onEntityDeleted = null;
  }

  /**
   * Set callback for when an entity is successfully deleted.
   * @param {Function} callback - (entityId, result) => void
   * @returns {this}
   */
  setOnEntityDeleted(callback) {
    this.onEntityDeleted = callback;
    return this;
  }

  /**
   * Identify any deletion form by id or data attribute.
   */
  _isDeletionForm(form) {
    if (!form) return false;
    return (
      form.getAttribute("id") === "confirm-deletion-frm" || form.dataset?.ajaxForm !== undefined
    );
  }

  canHandle(context) {
    return this._isDeletionForm(context?.form);
  }

  // handle(context) {
  //   const { result, form } = context;

  //   // Cancel any redirect/reload baked into the response (success OR error path).
  //   // Deletion is an in-place action — never navigate away from this flow.
  //   context.shouldRedirect = false;
  //   context.shouldReload = false;
  //   context.preventDefault = true;
  //   context.redirectUrl = null;

  //   // Only run success-side logic when the deletion actually succeeded
  //   if (result?.success !== true) {
  //     return;
  //   }

  //   const entityId = this.extractEntityId(form);

  //   logger.debug("Entity deleted:", entityId);

  //   // Populate metadata for downstream consumers
  //   context.metadata = context.metadata || {};
  //   context.metadata.isDeletion = true;
  //   context.metadata.entityId = entityId;
  //   context.metadata.deletionType = result.data?.deletion_type || null;
  //   context.metadata.entityName = result.data?.name || null;
  //   context.metadata.wasSkipped = result.data?.was_skipped || false;

  //   if (entityId && this.onEntityDeleted) {
  //     this.onEntityDeleted(entityId, result);
  //   }
  // }

  handle(context) {
    const { result, form } = context;

    console.log("🔍 DeletionResponseProcessor.handle called");
    console.log("result:", result);
    console.log("result.success:", result?.success);

    // Cancel any redirect/reload
    context.shouldRedirect = false;
    context.shouldReload = false;
    context.preventDefault = true;
    context.redirectUrl = null;

    // Only run success-side logic when the deletion actually succeeded
    if (result?.success !== true) {
      console.log("❌ Deletion not successful, skipping");
      return;
    }

    const entityId = this.extractEntityId(form);
    console.log("✅ Entity ID:", entityId);

    if (entityId && this.onEntityDeleted) {
      console.log("📢 Calling onEntityDeleted callback");
      this.onEntityDeleted(entityId, result);
    } else {
      console.log("⚠️ No entityId or no callback");
    }
  }

  /**
   * Extract entity ID from the form's hidden "id" input.
   * Defensively strips "column_name <value>" prefix if present.
   */
  extractEntityId(form) {
    if (!form) {
      logger.warn("No form provided");
      return null;
    }

    const idInput = form.querySelector('input[name="id"]');
    if (idInput?.value) {
      let value = String(idInput.value).trim();

      const prefixMatch = value.match(/^[a-z_]+\s+(.+)$/i);
      if (prefixMatch) {
        logger.warn(`Stripping unexpected prefix from entity ID: "${value}"`);
        value = prefixMatch[1];
      }

      logger.debug("Found entity ID:", value);
      return value;
    }

    if (form.dataset?.entityId) {
      return form.dataset.entityId;
    }

    logger.warn("No entity ID field found in form");
    return null;
  }
}
