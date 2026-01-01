import BrowserLogger from "js/utils/logger";
import ProductVariationManager from "../components/ProductVariationManager.js";
import MediaUpload from "js/backend/shared/MediaUpload";

const logger = new BrowserLogger("ProductComponentsManager");

export default class ProductComponentsManager {
  constructor() {
    this.variationManager = null;
    this.mediaUploads = [];
  }

  async initialize() {
    logger.debug("Initializing product-specific components");
    await this._initializeProductVariations();
    await this._initializeMediaUploads();
  }

  async _initializeProductVariations() {
    try {
      this.variationManager = new ProductVariationManager();
      logger.success("Product variation manager initialized");
    } catch (error) {
      logger.error("Failed to initialize product variations", error);
      throw error;
    }
  }

  async _initializeMediaUploads() {
    try {
      const mediaUploadContainers = document.querySelectorAll('[data-media-upload="true"]');
      logger.debug(`Found ${mediaUploadContainers.length} media upload containers`);

      this.mediaUploads = Array.from(mediaUploadContainers).map((container) => {
        return new MediaUpload(container);
      });

      logger.success("Media upload components initialized");
    } catch (error) {
      logger.error("Failed to initialize media uploads", error);
      throw error;
    }
  }

  getMediaUploadByField(fieldName) {
    const field = document.querySelector(`[name="${fieldName}"]`);
    if (!field) return null;

    const container = field.closest('[data-media-upload="true"]');
    if (!container) return null;

    return this.mediaUploads.find((upload) => upload.container === container);
  }
}
