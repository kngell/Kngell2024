import BaseResponseProcessor from "./BaseResponseProcessor";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("ProductDeletionProcessor");

export default class ProductDeletionProcessor extends BaseResponseProcessor {
  constructor() {
    super();
    this.deletionHelper = null;
  }

  setDeletionHelper(helper) {
    this.deletionHelper = helper;
    return this;
  }

  canHandle(context) {
    const { form } = context;
    const isDeletionForm = form.action && form.action.includes("/product-delete/delete");
    return isDeletionForm && this.deletionHelper !== null;
  }

  handle(context) {
    const { result, form } = context;

    if (result.success === true) {
      // Prevent redirect/reload
      context.shouldRedirect = false;
      context.shouldReload = false;
      context.redirectUrl = null;
      context.preventDefault = true;

      // Extract product ID directly from the modal form
      const productId = this.extractProductIdFromModalForm(form);

      logger.debug("Extracted product ID from modal form:", productId);

      context.metadata.isProductDeletion = true;
      context.metadata.productId = productId;

      // Remove the row from the table
      if (productId && this.deletionHelper) {
        this.deletionHelper.removeProductRow(productId);
      }
    }
  }

  extractProductIdFromModalForm(form) {
    if (!form) {
      logger.warn("No form provided");
      return null;
    }
    const productIdInput = form.querySelector('input[name="product_id"]');
    if (productIdInput && productIdInput.value) {
      logger.debug("Found product_id in modal form:", productIdInput.value);
      return productIdInput.value;
    }

    logger.warn("No product_id field found in modal form");
    return null;
  }
}
