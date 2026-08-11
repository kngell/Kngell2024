import BaseDropzone from "../BaseDropzone";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("SinglePreviewDropzone");

export default class SinglePreviewDropzone extends BaseDropzone {
  constructor(element, filesOrOptions, options = {}) {
    let files = [];
    let finalOptions = options;

    if (Array.isArray(filesOrOptions)) {
      files = filesOrOptions;
    } else if (filesOrOptions && typeof filesOrOptions === "object") {
      finalOptions = filesOrOptions;
      files = finalOptions.files || [];
    }

    super(element, finalOptions);

    this.files = files;

    // Ensure files is an array
    if (!Array.isArray(this.files)) {
      logger.warn("files is not an array, resetting to empty array");
      this.files = [];
    }

    // Sync files to input if there are any
    if (this.files.length > 0) {
      this.syncFilesToInput(this.files);
    }

    logger.debug("SinglePreviewDropzone initialized", {
      inputName: this.inputName,
      fileCount: this.files.length
    });

    this.setupPreviewListeners();
  }

  setupPreviewListeners() {
    // Remove button
    const removeBtn = this.element.querySelector(".remove");
    if (removeBtn) {
      removeBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        this.reset();
      });
    }

    // File input for replacement
    if (this.input) {
      // Remove any existing listener
      if (this.boundChangeHandler) {
        this.input.removeEventListener("change", this.boundChangeHandler);
      }

      this.boundChangeHandler = async (e) => {
        // CRITICAL: Skip if this change was triggered by our own sync
        if (this._syncing) return;

        const newFiles = Array.from(e.target.files || []);
        if (newFiles.length > 0) {
          logger.debug("New file selected for replacement");
          this.files = [newFiles[0]];

          // CRITICAL: Sync to file input
          await this.transitionToUploading();
        }
      };

      this.input.addEventListener("change", this.boundChangeHandler);
    }
  }

  // Also update handleFiles method
  handleFiles(files) {
    // Prevent multiple triggers
    if (this._processingFiles) return;
    this._processingFiles = true;

    logger.debug("Files dropped on preview", { count: files.length });

    // Take only first file for single mode
    this.files = [files[0]];

    // Skip sync here since we're transitioning
    // Go directly to uploading
    this.transitionToUploading();

    // Reset flag after transition
    setTimeout(() => {
      this._processingFiles = false;
    }, 300);
  }

  async transitionToUploading() {
    const uploadingEl = this.createUploadingElement();
    this.destroy();
    this.element.replaceWith(uploadingEl);

    import("./SingleUploadingDropzone").then((module) => {
      new module.default(uploadingEl, this.files, { inputName: this.inputName });
    });
  }

  createUploadingElement() {
    const rootClass = this.rootBaseClass;
    const file = this.files[0];

    const el = document.createElement("div");
    el.className = `${rootClass} ${rootClass}--uploading`;
    el.dataset.state = "uploading";
    el.dataset.mode = "single";
    el.innerHTML = `
    <div class="${rootClass}__icon">
      <svg><use href="#icon-upload"></use></svg>
    </div>
    <div class="${rootClass}__text">
      <span class="${rootClass}__main-text">Uploading: 0%</span>
      <span class="${rootClass}__hint-text">${file.name} • 0 MB</span>
    </div>
    <div class="${rootClass}__progress">
      <div class="${rootClass}__progress-fill" style="width: 0%"></div>
    </div>
  `;

    // Use createFileInput
    const fileInput = this.createFileInput();
    el.appendChild(fileInput);

    return el;
  }

  createEmptyElement() {
    const rootClass = this.rootBaseClass;

    const el = document.createElement("div");
    el.className = rootClass;
    el.dataset.state = "empty";
    el.dataset.mode = "single";
    el.innerHTML = `
    <div class="${rootClass}__icon">
      <svg><use href="#icon-upload"></use></svg>
    </div>
    <div class="${rootClass}__text">
      <span class="${rootClass}__main-text">Drag & drop or click to upload</span>
      <span class="${rootClass}__hint-text">PNG, JPG, GIF • Max 5MB</span>
    </div>
  `;

    // Use createFileInput
    const fileInput = this.createFileInput();
    el.appendChild(fileInput);

    return el;
  }

  async reset() {
    // CRITICAL: Clear the file input
    this.syncFilesToInput([]);

    const emptyEl = this.createEmptyElement();
    this.destroy();
    this.element.replaceWith(emptyEl);

    import("./SingleEmptyDropzone").then((module) => {
      new module.default(emptyEl, { inputName: this.inputName });
    });
  }

  destroy() {
    // Clean up child-specific resources FIRST
    if (this.input && this.boundChangeHandler) {
      this.input.removeEventListener("change", this.boundChangeHandler);
      this.boundChangeHandler = null;
    }

    // Clear child-specific flags
    this._processingFiles = false;
    this._transitioning = false;
    this._syncing = false; // Override parent if needed

    // THEN call parent destroy
    super.destroy();

    logger.debug("SinglePreviewDropzone destroyed");
  }
}
