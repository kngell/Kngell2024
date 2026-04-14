import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("DropzoneFactory");

export default class DropzoneFactory {
  static async init(element, config = {}) {
    if (!element) return null;

    const state = element.dataset.state || "empty";
    const mode = element.dataset.mode || "single";

    const files = element.__files || [];
    delete element.__files;

    logger.debug(`Initializing: ${mode} mode, ${state} state`, {
      elementClass: element.className,
      hasInput: !!element.querySelector('input[type="file"]')
    });

    try {
      if (mode === "single") {
        switch (state) {
          case "empty":
            const { default: SingleEmpty } = await import("./single/SingleEmptyDropzone");
            return new SingleEmpty(element);
          case "uploading":
            const { default: SingleUploading } = await import("./single/SingleUploadingDropzone");
            return new SingleUploading(element, files);
          case "preview":
            const { default: SinglePreview } = await import("./single/SinglePreviewDropzone");
            return new SinglePreview(element, files);
          default:
            logger.warn(`Unknown state: ${state} for single mode`);
            return null;
        }
      } else {
        switch (state) {
          case "empty":
            const { default: MultipleEmpty } = await import("./multiple/MultipleEmptyDropzone");
            return new MultipleEmpty(element);
          case "uploading":
            const { default: MultipleUploading } =
              await import("./multiple/MultipleUploadingDropzone");
            return new MultipleUploading(element, files);
          case "preview":
            const { default: MultiplePreview } = await import("./multiple/MultiplePreviewDropzone");
            return new MultiplePreview(element, files);
          default:
            logger.warn(`Unknown state: ${state} for multiple mode`);
            return null;
        }
      }
    } catch (error) {
      logger.error(`Failed to initialize: ${error.message}`);
      return null;
    }
  }

  static async initAll(selector = ".upload-single, .upload-multiple", config = {}) {
    const elements = document.querySelectorAll(selector);

    logger.debug(`initAll called`, {
      selector,
      foundCount: elements.length,
      readyState: document.readyState,
      timestamp: new Date().toISOString()
    });

    if (elements.length === 0) {
      logger.debug("No dropzone elements found");
      return [];
    }

    // Log each element found
    elements.forEach((el, index) => {
      logger.debug(`Found dropzone element ${index + 1}:`, {
        className: el.className,
        state: el.dataset.state,
        mode: el.dataset.mode,
        hasInput: !!el.querySelector('input[type="file"]')
      });
    });

    const instances = [];

    for (const el of elements) {
      if (!el.__dropzoneInstance) {
        const instance = await DropzoneFactory.init(el, config);
        if (instance) {
          el.__dropzoneInstance = instance;
          instances.push(instance);
        }
      }
    }

    logger.debug(`Initialized ${instances.length} dropzones`);
    return instances;
  }

  static async transition(element, newState, files = [], config = {}) {
    if (!element) return null;

    // Destroy existing instance
    if (element.__dropzoneInstance) {
      element.__dropzoneInstance.destroy();
    }

    // Store files for next instance
    if (files.length > 0) {
      element.__files = files;
    }

    // Update state attribute
    element.dataset.state = newState;

    // Create new instance
    return await DropzoneFactory.init(element, config);
  }
}
