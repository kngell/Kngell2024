import BaseDropzone from "../BaseDropzone";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("SingleUploadingDropzone");

export default class SingleUploadingDropzone extends BaseDropzone {
  constructor(element, files, options = {}) {
    super(element, options);
    this.files = files;

    // CRITICAL: Sync files to the new input
    this.syncFilesToInput(files);

    this.progress = 0;
    this.interval = null;
    logger.debug("SingleUploadingDropzone initialized", { inputName: this.inputName });

    this.startUpload();
  }

  startUpload() {
    this.interval = setInterval(() => {
      this.progress += 10;
      this.updateProgress();

      if (this.progress >= 100) {
        clearInterval(this.interval);
        this.transitionToPreview();
      }
    }, 200);
  }

  updateProgress() {
    const fill = this.element.querySelector(`.${this.rootBaseClass}__progress-fill`);
    if (fill) fill.style.width = `${this.progress}%`;

    const text = this.element.querySelector(`.${this.rootBaseClass}__main-text`);
    if (text) text.textContent = `Uploading: ${this.progress}%`;
  }

  transitionToPreview() {
    const previewEl = this.createPreviewElement();
    this.destroy();
    this.element.replaceWith(previewEl);

    import("./SinglePreviewDropzone").then((module) => {
      new module.default(previewEl, this.files, { inputName: this.inputName });
    });
  }
  createPreviewElement() {
    const rootClass = this.rootBaseClass;
    const file = this.files[0];
    const url = URL.createObjectURL(file);

    const el = document.createElement("div");
    el.className = `${rootClass} ${rootClass}--preview`;
    el.dataset.state = "preview";
    el.dataset.mode = "single";
    el.innerHTML = `
    <div class="${rootClass}__preview-container">
      <div class="${rootClass}__preview">
        <img src="${url}" alt="Preview">
      </div>
    </div>
    <div class="${rootClass}__content">
      <div class="${rootClass}__info">
        <span class="${rootClass}__filename">${file.name}</span>
        <span class="${rootClass}__filesize">${(file.size / 1024 / 1024).toFixed(1)} MB</span>
      </div>
      <div class="${rootClass}__actions">
        <button class="remove">Remove</button>
      </div>
    </div>
  `;

    // Use createFileInput with hidden=true
    const hiddenInput = this.createFileInput(true);
    el.appendChild(hiddenInput);

    return el;
  }

  destroy() {
    // Clean up child-specific resources FIRST
    if (this.interval) {
      clearInterval(this.interval);
      this.interval = null;
    }

    // Clear child-specific flags
    this.progress = 0;

    // THEN call parent destroy
    super.destroy();

    logger.debug("SingleUploadingDropzone destroyed");
  }
}
