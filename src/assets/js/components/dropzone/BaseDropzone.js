import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("BaseDropzone");

export default class BaseDropzone {
  constructor(element) {
    if (!element) {
      logger.error("No element provided");
      return;
    }

    this.element = element;
    this.rootBaseClass = this.getRootBaseClass(element);
    this.mode = element.dataset.mode || "single";
    this.state = element.dataset.state || "empty";
    this.input = element.querySelector('input[type="file"]');

    // CRITICAL: Capture the original input name from DOM
    this.originalInputName = this.input ? this.input.getAttribute("name") : "file";

    this.files = [];
    this.boundHandlers = {};
    this.isDestroyed = false;
    this.globalPreventHandlers = {};

    logger.debug(`BaseDropzone initialized`, {
      rootBaseClass: this.rootBaseClass,
      mode: this.mode,
      state: this.state,
      originalInputName: this.originalInputName,
      hasInput: !!this.input
    });

    element.__dropzoneInstance = this;

    // Setup core functionality
    this.setupClickHandler();
    this.setupDragAndDrop();
    this.setupGlobalDragAndDropPrevention();
  }

  getRootBaseClass(element) {
    const classList = Array.from(element.classList);
    const rootClass = classList.find(
      (cls) => cls.startsWith("upload-") && !cls.includes("__") && !cls.includes("--")
    );
    return rootClass || (this.mode === "multiple" ? "upload-multiple" : "upload-single");
  }

  setupGlobalDragAndDropPrevention() {
    // Prevent default on window to stop browser from opening files
    const preventDefaults = (e) => {
      e.preventDefault();
      e.stopPropagation();
    };

    // Add listeners to window for all drag/drop events
    const events = ["dragenter", "dragover", "dragleave", "drop"];
    events.forEach((eventName) => {
      window.addEventListener(eventName, preventDefaults);
      this.globalPreventHandlers[eventName] = preventDefaults;
    });

    logger.debug("Global drag and drop prevention setup");
  }

  setupClickHandler() {
    this.boundHandlers.click = (e) => {
      e.preventDefault();
      e.stopPropagation();

      if (this.isDestroyed) return;

      // Don't trigger if clicking on buttons
      if (e.target.tagName === "BUTTON" || e.target.closest("button")) return;

      // For preview state, only open file selector if clicking on preview container
      if (this.state === "preview") {
        const previewContainer = e.target.closest(`.${this.rootBaseClass}__preview-container`);
        if (!previewContainer) return;
      }

      if (this.input) {
        logger.debug("Click triggered, opening file selector");
        this.input.click();
      } else {
        logger.warn("No input found to trigger click");
      }
    };
    this.element.addEventListener("click", this.boundHandlers.click);
  }

  setupDragAndDrop() {
    this.boundHandlers.dragEnter = (e) => {
      e.preventDefault();
      e.stopPropagation();
      this.element.classList.add("is-dragging");
      logger.debug("Drag enter", { state: this.state });
    };

    this.boundHandlers.dragOver = (e) => {
      e.preventDefault();
      e.stopPropagation();
      // CRITICAL: Set drop effect to copy to indicate we'll handle the drop
      if (e.dataTransfer) {
        e.dataTransfer.dropEffect = "copy";
      }
    };

    this.boundHandlers.dragLeave = (e) => {
      e.preventDefault();
      e.stopPropagation();
      this.element.classList.remove("is-dragging");
      logger.debug("Drag leave");
    };

    this.boundHandlers.drop = (e) => {
      // CRITICAL: Multiple preventDefault calls to ensure browser doesn't handle it
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      this.element.classList.remove("is-dragging");

      // Add processing flag to prevent multiple triggers
      if (this._processingDrop) return;
      this._processingDrop = true;

      logger.debug("Drop event triggered", {
        state: this.state,
        hasDataTransfer: !!e.dataTransfer,
        filesCount: e.dataTransfer?.files?.length
      });

      if (e.dataTransfer) {
        const files = Array.from(e.dataTransfer.files || []);
        if (files.length > 0) {
          logger.debug(`Files dropped on ${this.state} state`, files.length);

          const validFiles = files.filter((f) => f.type.startsWith("image/"));
          logger.debug(`Valid image files: ${validFiles.length}`);

          if (validFiles.length > 0) {
            this.handleFiles(validFiles);
          } else {
            logger.warn("No valid image files dropped");
          }
        }
      }

      // Reset flag after a short delay
      setTimeout(() => {
        this._processingDrop = false;
      }, 300);
    };

    this.element.addEventListener("dragenter", this.boundHandlers.dragEnter);
    this.element.addEventListener("dragover", this.boundHandlers.dragOver);
    this.element.addEventListener("dragleave", this.boundHandlers.dragLeave);
    this.element.addEventListener("drop", this.boundHandlers.drop);

    logger.debug("Drag and drop handlers setup on element", {
      element: this.element.className,
      state: this.state
    });
  }

  syncFilesToInput(files = this.files) {
    if (!Array.isArray(files)) {
      logger.warn("syncFilesToInput: files is not an array");
      files = [];
    }

    if (!this.input) {
      logger.warn("No file input to sync");
      return;
    }

    this._syncing = true;

    try {
      const dt = new DataTransfer();

      // ✅ Only add actual File objects (not existing server files)
      files.forEach((file) => {
        if (file instanceof File) {
          dt.items.add(file);
          logger.debug(`Added file to DataTransfer: ${file.name}`);
        }
      });

      this.input.files = dt.files;

      logger.debug(`Synced ${dt.files.length} files to input`, {
        totalFiles: files.length,
        actualFiles: dt.files.length,
        fileNames: Array.from(dt.files).map((f) => f.name)
      });

      // Dispatch change event
      if (!this._skipChangeEvent) {
        this.input.dispatchEvent(new Event("change", { bubbles: true }));
      }
    } catch (error) {
      logger.error("Failed to sync files to input:", error);
    } finally {
      setTimeout(() => {
        this._syncing = false;
      }, 100);
    }
  }

  // Helper method to create file input with the original name from DOM
  createFileInput(hidden = false) {
    const input = document.createElement("input");
    input.type = "file";
    input.name = this.originalInputName;
    input.accept = "image/*";
    if (hidden) input.hidden = true;

    logger.debug(`Created file input with name: ${this.originalInputName}`, { hidden });

    return input;
  }

  handleFiles(files) {
    logger.error("handleFiles must be implemented by child class", {
      state: this.state,
      mode: this.mode
    });
  }

  destroy() {
    this.isDestroyed = true;

    // Clean up ALL base class event listeners
    Object.keys(this.boundHandlers).forEach((key) => {
      const handler = this.boundHandlers[key];
      if (typeof handler === "function") {
        this.element.removeEventListener(key, handler);
      }
    });

    // Clean up global drag and drop prevention listeners
    if (this.globalPreventHandlers) {
      Object.keys(this.globalPreventHandlers).forEach((key) => {
        const handler = this.globalPreventHandlers[key];
        if (typeof handler === "function") {
          window.removeEventListener(key, handler);
        }
      });
      this.globalPreventHandlers = null;
    }

    // Clear any base class flags
    this._processingDrop = false;
    this._syncing = false;

    delete this.element.__dropzoneInstance;
    logger.debug("BaseDropzone destroyed");
  }
}
