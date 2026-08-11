import BaseResponseProcessor from "./BaseResponseProcessor";

export default class DeletionEventProcessor extends BaseResponseProcessor {
  constructor(options = {}) {
    super();
    this.extractEntityId = options.extractEntityId || (() => null);
    this.logger = options.logger || console;
  }

  canHandle(context) {
    const { result, form } = context;

    // Only handle successful responses
    if (result?.success !== true) return false;

    // Check if this is a deletion response
    const isDeletion =
      result.data?.deletion_type ||
      result.deletedId ||
      form?.getAttribute("id") === "confirm-deletion-frm";

    return isDeletion;
  }

  handle(context) {
    const { result, form } = context;

    const entityId = this.extractEntityId ? this.extractEntityId(form) : null;

    // Dispatch the event that TableManager listens for
    document.dispatchEvent(
      new CustomEvent("entity:deleted", {
        detail: {
          entityId: entityId,
          result: result,
          source: "DeletionModal"
        }
      })
    );

    this.logger.debug(`Dispatched entity:deleted for ${entityId}`);
  }
}
