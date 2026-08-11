import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("DropzoneFactory");

export default class DropzoneFactory {
  static async init(element, config = {}) {
    if (!element) return null;

    const state = element.dataset.state || "empty";
    let mode = element.dataset.mode;

    // Auto-detect mode if not set
    if (!mode) {
      const input = element.querySelector('input[type="file"]');
      if (input && (input.hasAttribute("multiple") || input.getAttribute("name")?.includes("[]"))) {
        mode = "multiple";
        element.dataset.mode = mode;
      } else {
        mode = "single";
      }
    }
    // Use passed files or try to get from element
    let files = config.files || element.__files || [];
    delete element.__files;

    logger.debug(`Initializing: ${mode} mode, ${state} state`, {
      elementClass: element.className,
      fileCount: files.length,
      hasConfigFiles: !!config.files
    });

    try {
      if (mode === "single") {
        switch (state) {
          case "empty":
            const { default: SingleEmpty } = await import("./single/SingleEmptyDropzone");
            return new SingleEmpty(element, { files, ...config });
          case "uploading":
            const { default: SingleUploading } = await import("./single/SingleUploadingDropzone");
            return new SingleUploading(element, { files, ...config });
          case "preview":
            const { default: SinglePreview } = await import("./single/SinglePreviewDropzone");
            return new SinglePreview(element, { files, ...config });
          default:
            logger.warn(`Unknown state: ${state} for single mode`);
            return null;
        }
      } else {
        // MULTIPLE MODE - FIXED
        switch (state) {
          case "empty":
            const { default: MultipleEmpty } = await import("./multiple/MultipleEmptyDropzone");
            return new MultipleEmpty(element, { files, ...config });
          case "uploading":
            const { default: MultipleUploading } =
              await import("./multiple/MultipleUploadingDropzone");
            return new MultipleUploading(element, { files, ...config });
          case "preview":
            // FIX: Use MultiplePreviewDropzone, NOT SinglePreviewDropzone
            const { default: MultiplePreview } = await import("./multiple/MultiplePreviewDropzone");
            return new MultiplePreview(element, { files, ...config });
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
      readyState: document.readyState
    });

    if (elements.length === 0) {
      logger.debug("No dropzone elements found");
      return [];
    }

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

    if (element.__dropzoneInstance) {
      element.__dropzoneInstance.destroy();
    }

    if (files.length > 0) {
      element.__files = files;
    }

    element.dataset.state = newState;

    return await DropzoneFactory.init(element, config);
  }
}
