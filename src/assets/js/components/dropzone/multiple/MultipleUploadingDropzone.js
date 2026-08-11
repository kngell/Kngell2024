import BaseDropzone from "../BaseDropzone";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("MultipleUploadingDropzone");

export default class MultipleUploadingDropzone extends BaseDropzone {
  constructor(element, options = {}) {
    super(element, options);

    // Get files from element or options
    this.files = element.__files || options.files || [];
    this.existingFiles = element.__existingFiles || [];
    this.completedFiles = 0;
    this.fileProgress = this.files.map(() => 0);
    this.interval = null;

    this.syncFilesToInput(this.files);

    logger.debug("MultipleUploadingDropzone initialized", {
      fileCount: this.files.length,
      existingCount: this.existingFiles.length,
      inputName: this.originalInputName
    });

    this.startUpload();
  }

  startUpload() {
    let progress = 0;
    this.interval = setInterval(() => {
      progress += 10;
      this.updateProgress(progress);

      if (progress >= 100) {
        clearInterval(this.interval);
        this.transitionToPreview();
      }
    }, 200);
  }

  updateProgress(progress) {
    const fill = this.element.querySelector(`.${this.rootBaseClass}__progress-fill`);
    if (fill) fill.style.width = `${progress}%`;

    const text = this.element.querySelector(`.${this.rootBaseClass}__main-text`);
    if (text && progress < 100) {
      text.textContent = `Uploading: ${progress}%`;
    }
  }

  // In MultipleUploadingDropzone.js - update transitionToPreview method
  transitionToPreview() {
    const previewEl = this.createPreviewElement();
    // Ensure mode is set correctly
    previewEl.dataset.mode = "multiple";
    previewEl.dataset.state = "preview";
    previewEl.__files = [...this.existingFiles, ...this.files];

    this.destroy();
    this.element.replaceWith(previewEl);

    import("../DropzoneFactory").then(({ default: DropzoneFactory }) => {
      DropzoneFactory.init(previewEl);
    });
  }

  createPreviewElement() {
    const rootClass = this.rootBaseClass;
    const allFiles = [...this.existingFiles, ...this.files];
    const totalSize = allFiles.reduce((sum, f) => sum + (f.size || 0), 0);
    const totalSizeMB = (totalSize / 1024 / 1024).toFixed(1);

    let previewItems = "";
    for (let i = 0; i < allFiles.length; i++) {
      const file = allFiles[i];
      const url = URL.createObjectURL(file);
      const sizeMB = (file.size / 1024 / 1024).toFixed(1);
      previewItems += `
        <div class="${rootClass}__preview-item" data-index="${i}">
          <div class="${rootClass}__preview">
            <img src="${url}" alt="Preview ${i + 1}">
          </div>
          <div class="${rootClass}__preview-item-actions">
            <button class="remove" data-index="${i}">×</button>
          </div>
          <div class="${rootClass}__preview-info">
            <span class="${rootClass}__filename">${this.truncateFilename(file.name)}</span>
            <span class="${rootClass}__filesize">${sizeMB} MB</span>
          </div>
        </div>
      `;
    }

    const addMoreItem = `
      <div class="${rootClass}__preview-item add-more-item">
        <div class="${rootClass}__preview add-more">
          <svg><use href="#icon-plus"></use></svg>
        </div>
        <div class="${rootClass}__preview-info">
          <span class="${rootClass}__filename">Add more</span>
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
          <span class="${rootClass}__main-text">${allFiles.length} file(s) uploaded successfully</span>
          <span class="${rootClass}__hint-text">Total: ${totalSizeMB} MB</span>
        </div>
        <div class="${rootClass}__actions">
          <button class="add-more">Add More Files</button>
        </div>
      </div>
    `;

    const hiddenInput = this.createFileInput(true);
    hiddenInput.multiple = true;
    el.appendChild(hiddenInput);

    return el;
  }

  truncateFilename(filename, maxLength = 20) {
    if (!filename) return "unknown";
    if (filename.length <= maxLength) return filename;
    const ext = filename.split(".").pop();
    const name = filename.slice(0, maxLength - ext.length - 3);
    return `${name}...${ext}`;
  }

  destroy() {
    if (this.interval) {
      clearInterval(this.interval);
      this.interval = null;
    }
    super.destroy();
  }
}
