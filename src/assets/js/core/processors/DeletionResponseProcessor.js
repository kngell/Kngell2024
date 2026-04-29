import BaseResponseProcessor from "./BaseResponseProcessor";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("DeletionResponseProcessor");

export default class DeletionResponseProcessor extends BaseResponseProcessor {
  constructor() {
    super();
    this.onEntityDeleted = null;
  }

  /**
   * Set callback for when entity is successfully deleted.
   * @param {Function} callback - (entityId, result) => void
   */
  setOnEntityDeleted(callback) {
    this.onEntityDeleted = callback;
    return this;
  }

  canHandle(context) {
    const { form } = context;

    // Match any deletion form
    return form?.id === "confirm-deletion-frm" || form?.dataset?.ajaxForm !== undefined;
  }

  handle(context) {
    const { result, form } = context;

    if (result.success !== true) {
      return;
    }

    // Prevent default redirect — we handle it
    context.shouldRedirect = false;
    context.shouldReload = false;
    context.redirectUrl = null;
    context.preventDefault = true;

    // Extract entity ID from form
    const entityId = this.extractEntityId(form);

    logger.debug("Entity deleted:", entityId);

    context.metadata.isDeletion = true;
    context.metadata.entityId = entityId;
    context.metadata.deletionType = result.data?.deletion_type || "archive";
    context.metadata.entityName = result.data?.name || null;
    context.metadata.wasSkipped = result.data?.was_skipped || false;

    // Notify caller
    if (entityId && this.onEntityDeleted) {
      this.onEntityDeleted(entityId, result);
    }
  }

  extractEntityId(form) {
    if (!form) {
      logger.warn("No form provided");
      return null;
    }

    // Generic: look for hidden input named "id"
    const idInput = form.querySelector('input[name="id"]');
    if (idInput?.value) {
      logger.debug("Found entity ID:", idInput.value);
      return idInput.value;
    }

    logger.warn("No id field found in form");
    return null;
  }
}
