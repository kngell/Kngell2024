import BaseDropzone from "../BaseDropzone";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("SingleEmptyDropzone");

export default class SingleEmptyDropzone extends BaseDropzone {
  constructor(element, options = {}) {
    super(element, options);

    // Setup change listener for empty state
    this.setupChangeListener();

    logger.debug("SingleEmptyDropzone initialized", { inputName: this.inputName });
  }

  setupChangeListener() {
    if (this.input) {
      // Remove any existing listener
      if (this.boundChangeHandler) {
        this.input.removeEventListener("change", this.boundChangeHandler);
      }

      this.boundChangeHandler = (e) => {
        if (this.isDestroyed) return;
        const files = Array.from(e.target.files || []);
        if (files.length > 0) {
          this.handleFiles(files);
          e.target.value = ""; // Clear to allow re-select
        }
      };

      this.input.addEventListener("change", this.boundChangeHandler);
    }
  }

  handleFiles(files) {
    // Prevent multiple triggers
    if (this._processingFiles) return;
    this._processingFiles = true;

    logger.debug("Files selected", { count: files.length });

    // Take only first file for single mode
    const file = files[0];
    this.files = [file];

    // CRITICAL: Sync to file input
    this.syncFilesToInput([file]);

    // Create uploading element
    const uploadingEl = this.createUploadingElement(file);

    // Transition
    this.destroy();
    this.element.replaceWith(uploadingEl);

    // Pass to next state
    import("./SingleUploadingDropzone").then((module) => {
      new module.default(uploadingEl, this.files, { inputName: this.inputName });
      // Reset flag after transition
      setTimeout(() => {
        this._processingFiles = false;
      }, 300);
    });
  }

  createUploadingElement(file) {
    const rootClass = this.rootBaseClass;

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

    // Use createFileInput instead of hardcoded HTML
    const fileInput = this.createFileInput();
    el.appendChild(fileInput);

    return el;
  }
  destroy() {
    if (this.input && this.boundChangeHandler) {
      this.input.removeEventListener("change", this.boundChangeHandler);
      this.boundChangeHandler = null;
    }

    this._processingFiles = false;

    super.destroy();

    logger.debug("SingleEmptyDropzone destroyed");
  }
}
