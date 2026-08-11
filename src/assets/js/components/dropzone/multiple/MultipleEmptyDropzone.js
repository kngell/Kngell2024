import BaseDropzone from "../BaseDropzone";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("MultipleEmptyDropzone");

export default class MultipleEmptyDropzone extends BaseDropzone {
  constructor(element, options = {}) {
    super(element, options);

    this.setupChangeListener();

    logger.debug("MultipleEmptyDropzone initialized", {
      inputName: this.originalInputName,
      multiple: this.input?.multiple
    });
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
          e.target.value = "";
        }
      };

      this.input.addEventListener("change", this.boundChangeHandler);
    }
  }

  handleFiles(files) {
    if (this._processingFiles) return;
    this._processingFiles = true;

    this.files = files;
    this.syncFilesToInput(files);

    const uploadingEl = this.createUploadingElement(files);

    uploadingEl.__files = files;

    this.destroy();
    this.element.replaceWith(uploadingEl);

    import("../DropzoneFactory").then(({ default: DropzoneFactory }) => {
      DropzoneFactory.init(uploadingEl);
      setTimeout(() => {
        this._processingFiles = false;
      }, 300);
    });
  }

  createUploadingElement(files) {
    const rootClass = this.rootBaseClass;
    const totalSize = files.reduce((sum, f) => sum + f.size, 0);
    const totalSizeMB = (totalSize / 1024 / 1024).toFixed(1);

    const el = document.createElement("div");
    el.className = `${rootClass} ${rootClass}--uploading`;
    el.dataset.state = "uploading";
    el.dataset.mode = "multiple"; // CRITICAL: Set mode to multiple
    el.innerHTML = `
    <div class="${rootClass}__icon">
      <svg><use href="#icon-upload"></use></svg>
    </div>
    <div class="${rootClass}__text">
      <span class="${rootClass}__main-text">Uploading ${files.length} file(s)...</span>
      <span class="${rootClass}__hint-text">Total: ${totalSizeMB} MB</span>
    </div>
    <div class="${rootClass}__progress">
      <div class="${rootClass}__progress-fill" style="width: 0%"></div>
    </div>
  `;

    const fileInput = this.createFileInput();
    fileInput.multiple = true;
    el.appendChild(fileInput);

    // Store files for the next state
    el.__files = files;

    return el;
  }

  destroy() {
    if (this.input && this.boundChangeHandler) {
      this.input.removeEventListener("change", this.boundChangeHandler);
      this.boundChangeHandler = null;
    }
    this._processingFiles = false;
    super.destroy();
  }
}
