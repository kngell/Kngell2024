import BrowserLogger from "js/core/utils/logger";
import ProductVariationManager from "js/backend/pages/products-save/components/ProductVariationManager";
import MediaUpload from "js/backend/pages/products-save/components/MediaUpload";

const logger = new BrowserLogger("ProductComponentsManager");

export default class ProductComponentsManager {
  constructor() {
    this.components = new Map();
    this.isInitialized = false;
  }

  async initialize() {
    if (this.isInitialized) {
      logger.warn("ProductComponentsManager already initialized");
      return;
    }

    try {
      logger.info("Initializing product components");

      // Initialize all components
      await this.initializeVariationManager();
      await this.initializeMediaUploads();

      // Future components will be added here
      // await this.initializeInventoryManager();
      // await this.initializePriceCalculator();

      this.isInitialized = true;
      logger.success("Product components initialized");
    } catch (error) {
      logger.error("Failed to initialize product components", error);
      throw error;
    }
  }

  async initializeVariationManager() {
    try {
      const variationContainer = document.querySelector('[data-variations="true"]');
      if (!variationContainer) {
        logger.debug("No variations container found");
        return;
      }

      const variationManager = new ProductVariationManager(variationContainer);
      await variationManager.initialize();

      this.components.set("variations", variationManager);
      logger.success("Product variation manager initialized");
    } catch (error) {
      logger.error("Failed to initialize variations", error);
      throw error;
    }
  }

  async initializeMediaUploads() {
    try {
      const mediaUploadContainers = document.querySelectorAll('[data-media-upload="true"]');
      logger.debug(`Found ${mediaUploadContainers.length} media upload containers`);

      const mediaUploads = [];

      Array.from(mediaUploadContainers).forEach((container) => {
        try {
          const mediaUpload = new MediaUpload(container);
          mediaUploads.push(mediaUpload);
          logger.debug(`Media upload initialized for: ${container.dataset.field || "unknown"}`);
        } catch (error) {
          logger.warn(`Failed to initialize media upload for container:`, error);
        }
      });

      this.components.set("mediaUploads", mediaUploads);
      logger.success("Media upload components initialized");
    } catch (error) {
      logger.error("Failed to initialize media uploads", error);
      throw error;
    }
  }

  // ============ COMPONENT ACCESS METHODS ============

  getVariationManager() {
    return this.components.get("variations");
  }

  getMediaUploads() {
    return this.components.get("mediaUploads") || [];
  }

  getMediaUploadByField(fieldName) {
    const field = document.querySelector(`[name="${fieldName}"]`);
    if (!field) return null;

    const container = field.closest('[data-media-upload="true"]');
    if (!container) return null;

    const mediaUploads = this.getMediaUploads();
    return mediaUploads.find((upload) => upload.container === container);
  }

  getComponent(name) {
    return this.components.get(name);
  }

  setComponent(name, component) {
    this.components.set(name, component);
  }

  // ============ VALIDATION ============

  validateComponents() {
    const validations = [];

    // Validate each component
    this.components.forEach((component, name) => {
      if (Array.isArray(component)) {
        // Handle array of components (like mediaUploads)
        component.forEach((item, index) => {
          if (typeof item.validate === "function") {
            try {
              const validation = item.validate();
              validations.push({
                type: name,
                index,
                ...validation,
              });
            } catch (error) {
              logger.warn(`Validation failed for ${name}[${index}]:`, error);
            }
          }
        });
      } else if (typeof component.validate === "function") {
        // Handle single component (like variations)
        try {
          const validation = component.validate();
          validations.push({
            type: name,
            ...validation,
          });
        } catch (error) {
          logger.warn(`Validation failed for ${name}:`, error);
        }
      }
    });

    return validations;
  }

  areComponentsValid() {
    const validations = this.validateComponents();
    return validations.every((v) => v.isValid);
  }

  // ============ DATA COLLECTION ============

  getFormDataForComponents(formData = {}) {
    const enhancedData = { ...formData };

    // Get data from each component
    this.components.forEach((component, name) => {
      if (name === "mediaUploads") {
        // Process media uploads
        component.forEach((upload) => {
          if (typeof upload.getFiles === "function") {
            try {
              const fieldName =
                upload.container.dataset.field ||
                upload.container.querySelector('input[type="file"]')?.name;
              if (fieldName) {
                const files = upload.getFiles();
                if (files && files.length > 0) {
                  enhancedData[fieldName] = files;
                }
              }
            } catch (error) {
              logger.warn(`Failed to get files from media upload:`, error);
            }
          }
        });
      } else if (name === "variations" && typeof component.getData === "function") {
        // Get variation data
        try {
          const variationData = component.getData();
          if (variationData && variationData.length > 0) {
            enhancedData.variations = variationData;
          }
        } catch (error) {
          logger.warn("Failed to get variation data:", error);
        }
      }
      // Add more components as needed
    });

    return enhancedData;
  }

  // ============ RESET & DESTROY ============

  resetComponents() {
    this.components.forEach((component, name) => {
      if (Array.isArray(component)) {
        component.forEach((item) => {
          if (typeof item.reset === "function") {
            item.reset();
          }
        });
      } else if (typeof component.reset === "function") {
        component.reset();
      }
    });

    logger.debug("All components reset");
  }

  destroy() {
    // Destroy each component
    this.components.forEach((component, name) => {
      if (Array.isArray(component)) {
        component.forEach((item) => {
          if (typeof item.destroy === "function") {
            try {
              item.destroy();
            } catch (error) {
              logger.warn(`Error destroying ${name} component:`, error);
            }
          }
        });
      } else if (typeof component.destroy === "function") {
        try {
          component.destroy();
        } catch (error) {
          logger.warn(`Error destroying ${name}:`, error);
        }
      }
    });

    this.components.clear();
    this.isInitialized = false;
    logger.debug("ProductComponentsManager destroyed");
  }

  // ============ UTILITY METHODS ============

  getStatus() {
    const status = {
      isInitialized: this.isInitialized,
      componentCount: this.components.size,
      components: {},
    };

    this.components.forEach((component, name) => {
      if (Array.isArray(component)) {
        status.components[name] = {
          type: "array",
          count: component.length,
          items: component.map((item) => ({
            hasValidate: typeof item.validate === "function",
            hasReset: typeof item.reset === "function",
            hasDestroy: typeof item.destroy === "function",
            ...(typeof item.getStatus === "function" ? item.getStatus() : {}),
          })),
        };
      } else {
        status.components[name] = {
          type: "single",
          hasValidate: typeof component.validate === "function",
          hasReset: typeof component.reset === "function",
          hasDestroy: typeof component.destroy === "function",
          ...(typeof component.getStatus === "function" ? component.getStatus() : {}),
        };
      }
    });

    return status;
  }

  // ============ EVENT HANDLING ============

  onComponentEvent(componentName, eventName, callback) {
    const component = this.getComponent(componentName);
    if (component && component.container) {
      component.container.addEventListener(eventName, callback);
      return () => component.container.removeEventListener(eventName, callback);
    }
    return null;
  }

  // ============ FUTURE COMPONENTS ============

  // async initializeInventoryManager() {
  //   // To be implemented
  // }

  // async initializePriceCalculator() {
  //   // To be implemented
  // }
}
