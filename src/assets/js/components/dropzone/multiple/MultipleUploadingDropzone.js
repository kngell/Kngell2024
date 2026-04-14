import BaseDropzone from "../BaseDropzone";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("MultipleUploadingDropzone");

export default class MultipleUploadingDropzone extends BaseDropzone {
  constructor(element, files) {
    super(element);
    this.files = files;

    // CRITICAL: Sync files to the new input
    this.syncFilesToInput(files);

    this.completedFiles = 0;
    this.fileProgress = files.map(() => 0);
    this.interval = null;
    this.isMultiple = true;
    logger.debug("MultipleUploadingDropzone initialized");

    this.startUpload();
  }

  startUpload() {
    this.interval = setInterval(() => {
      let allComplete = true;

      this.fileProgress = this.fileProgress.map((progress) => {
        if (progress < 100) {
          allComplete = false;
          return Math.min(progress + 10, 100);
        }
        return progress;
      });

      this.updateProgress();

      if (allComplete) {
        clearInterval(this.interval);
        this.transitionToPreview();
      }
    }, 200);
  }

  updateProgress() {
    const totalProgress = this.fileProgress.reduce((a, b) => a + b, 0) / this.files.length;

    const fill = this.element.querySelector(`.${this.rootBaseClass}__progress-fill`);
    if (fill) fill.style.width = `${totalProgress}%`;

    const text = this.element.querySelector(`.${this.rootBaseClass}__main-text`);
    if (text) text.textContent = `Uploading: ${Math.round(totalProgress)}%`;
  }

  async transitionToPreview() {
    const previewEl = this.createPreviewElement();
    this.destroy();
    this.element.replaceWith(previewEl);

    import("./MultiplePreviewDropzone").then((module) => {
      new module.default(previewEl, this.files);
    });
  }

  createPreviewElement() {
    const rootClass = this.rootBaseClass;

    const previewItems = this.files
      .map((file, index) => {
        const url = URL.createObjectURL(file);
        return `
        <div class="${rootClass}__preview-item" data-index="${index}">
          <div class="${rootClass}__preview">
            <img src="${url}" alt="Preview ${index + 1}">
          </div>
          <div class="${rootClass}__preview-item-actions">
            <button class="remove" data-index="${index}">×</button>
          </div>
        </div>
      `;
      })
      .join("");

    const addMoreItem = `
      <div class="${rootClass}__preview-item add-more-item">
        <div class="${rootClass}__preview add-more">
          <svg><use href="#icon-plus"></use></svg>
        </div>
      </div>
    `;

    const el = document.createElement("div");
    el.className = `${rootClass} ${rootClass}--preview`;
    el.dataset.state = "preview";
    el.dataset.mode = "multiple";
    el.innerHTML = `
      <div class="${rootClass}__previews-grid">
        ${previewItems}
        ${addMoreItem}
      </div>
      <div class="${rootClass}__content">
        <div class="${rootClass}__info">
          <span class="${rootClass}__main-text">${this.files.length} files uploaded</span>
          <span class="${rootClass}__hint-text">Click + or drag to add more</span>
        </div>
        <div class="${rootClass}__actions">
          <button class="add-more">Add More</button>
        </div>
      </div>
      <input type="file" name="image_url[]" multiple accept="image/*" hidden>
    `;

    return el;
  }

  destroy() {
    if (this.interval) clearInterval(this.interval);
    super.destroy();
  }
}
