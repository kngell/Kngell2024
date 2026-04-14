import BaseDropzone from "../BaseDropzone";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("MultipleEmptyDropzone");

export default class MultipleEmptyDropzone extends BaseDropzone {
  constructor(element) {
    super(element);
    this.isMultiple = true;

    // Setup change listener
    this.setupChangeListener();

    logger.debug("MultipleEmptyDropzone initialized");
  }

  setupChangeListener() {
    if (this.input) {
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
    this.files = files;

    // CRITICAL: Sync to file input
    this.syncFilesToInput(files);

    const uploadingEl = this.createUploadingElement();
    this.destroy();
    this.element.replaceWith(uploadingEl);

    import("./MultipleUploadingDropzone").then((module) => {
      new module.default(uploadingEl, this.files);
      setTimeout(() => {
        this._processingFiles = false;
      }, 300);
    });
  }

  createUploadingElement() {
    const rootClass = this.rootBaseClass;
    const totalSize = this.files.reduce((sum, f) => sum + f.size, 0);

    const el = document.createElement("div");
    el.className = `${rootClass} ${rootClass}--uploading`;
    el.dataset.state = "uploading";
    el.dataset.mode = "multiple";
    el.innerHTML = `
      <div class="${rootClass}__icon">
        <svg><use href="#icon-upload"></use></svg>
      </div>
      <div class="${rootClass}__text">
        <span class="${rootClass}__main-text">Uploading ${this.files.length} files...</span>
        <span class="${rootClass}__hint-text">Total: ${(totalSize / 1024 / 1024).toFixed(1)} MB</span>
      </div>
      <div class="${rootClass}__progress">
        <div class="${rootClass}__progress-fill" style="width: 0%"></div>
      </div>
      <input type="file" name="image_url[]" multiple accept="image/*">
    `;

    return el;
  }

  destroy() {
    if (this.input && this.boundChangeHandler) {
      this.input.removeEventListener("change", this.boundChangeHandler);
    }
    this._processingFiles = false;
    super.destroy();
  }
}
