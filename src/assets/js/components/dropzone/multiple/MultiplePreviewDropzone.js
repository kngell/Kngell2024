import BaseDropzone from "../BaseDropzone";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("MultiplePreviewDropzone");

export default class MultiplePreviewDropzone extends BaseDropzone {
  constructor(element, options = {}) {
    super(element, options);

    this.files = element.__files || options.files || [];

    // Ensure we're not losing existing files
    if (options.existingFiles) {
      this.files = [...options.existingFiles, ...this.files];
    }

    this.syncFilesToInput(this.files);
    this.setupPreviewListeners();

    logger.debug("MultiplePreviewDropzone initialized", {
      fileCount: this.files.length,
      inputName: this.originalInputName
    });
  }

  setupPreviewListeners() {
    // Setup remove buttons
    const removeBtns = this.element.querySelectorAll(".remove");
    removeBtns.forEach((btn) => {
      if (btn._removeHandler) {
        btn.removeEventListener("click", btn._removeHandler);
      }

      btn._removeHandler = (e) => {
        e.stopPropagation();
        const index = parseInt(btn.dataset.index);
        if (!isNaN(index)) {
          this.removeFile(index);
        }
      };

      btn.addEventListener("click", btn._removeHandler);
    });

    // Setup add more button
    const addMoreBtn = this.element.querySelector(".add-more");
    if (addMoreBtn) {
      if (addMoreBtn._clickHandler) {
        addMoreBtn.removeEventListener("click", addMoreBtn._clickHandler);
      }

      addMoreBtn._clickHandler = (e) => {
        e.stopPropagation();
        this.openFileSelector();
      };

      addMoreBtn.addEventListener("click", addMoreBtn._clickHandler);
    }

    // Setup add more item (the plus box)
    const addMoreItem = this.element.querySelector(".add-more-item");
    if (addMoreItem) {
      if (addMoreItem._clickHandler) {
        addMoreItem.removeEventListener("click", addMoreItem._clickHandler);
      }

      addMoreItem._clickHandler = (e) => {
        e.stopPropagation();
        this.openFileSelector();
      };

      addMoreItem.addEventListener("click", addMoreItem._clickHandler);
    }

    // Setup file input change listener
    if (this.input) {
      if (this.boundChangeHandler) {
        this.input.removeEventListener("change", this.boundChangeHandler);
      }

      this.boundChangeHandler = async (e) => {
        if (this._syncing) return;

        const newFiles = Array.from(e.target.files || []);
        if (newFiles.length > 0) {
          logger.debug("New files selected via file browser", { count: newFiles.length });
          await this.addFiles(newFiles);
          e.target.value = ""; // Clear input to allow selecting same files again
        }
      };

      this.input.addEventListener("change", this.boundChangeHandler);
    }
  }

  openFileSelector() {
    if (this.input) {
      logger.debug("Opening file selector");
      this.input.click();
    } else {
      logger.warn("No input found to trigger file selection");
      // Create temporary input if needed
      const tempInput = document.createElement("input");
      tempInput.type = "file";
      tempInput.multiple = true;
      tempInput.accept = "image/*";
      tempInput.addEventListener("change", (e) => {
        const files = Array.from(e.target.files || []);
        if (files.length > 0) {
          this.addFiles(files);
        }
      });
      tempInput.click();
    }
  }

  handleFiles(files) {
    if (this._processingFiles) return;
    this._processingFiles = true;

    logger.debug("Files dropped on preview", { count: files.length });
    this.addFiles(files);

    setTimeout(() => {
      this._processingFiles = false;
    }, 300);
  }

  async addFiles(newFiles) {
    // Filter out duplicates
    const existingFileKeys = new Set(
      this.files.map((f) => `${f.name}-${f.size}-${f.lastModified}`)
    );

    const uniqueFiles = [];
    newFiles.forEach((file) => {
      const key = `${file.name}-${file.size}-${file.lastModified}`;
      if (!existingFileKeys.has(key)) {
        uniqueFiles.push(file);
        existingFileKeys.add(key);
      }
    });

    if (uniqueFiles.length === 0) {
      logger.debug("No unique files to add");
      return;
    }

    // If we're adding files while in preview mode, just refresh the preview
    // instead of going through uploading state
    if (this.state === "preview") {
      this.files = [...this.files, ...uniqueFiles];
      this.refreshPreview();
    } else {
      // Go through uploading state
      const uploadingEl = this.createUploadingElement(uniqueFiles);
      uploadingEl.__files = this.files;
      uploadingEl.__existingFiles = this.files;

      this.destroy();
      this.element.replaceWith(uploadingEl);

      const { default: DropzoneFactory } = await import("../DropzoneFactory");
      DropzoneFactory.init(uploadingEl);
    }
  }

  createUploadingElement(newFiles) {
    const rootClass = this.rootBaseClass;
    const allFiles = [...this.files, ...newFiles];
    const totalSize = allFiles.reduce((sum, f) => sum + f.size, 0);
    const totalSizeMB = (totalSize / 1024 / 1024).toFixed(1);

    const el = document.createElement("div");
    el.className = `${rootClass} ${rootClass}--uploading`;
    el.dataset.state = "uploading";
    el.dataset.mode = "multiple";
    el.innerHTML = `
      <div class="${rootClass}__icon">
        <svg><use href="#icon-upload"></use></svg>
      </div>
      <div class="${rootClass}__text">
        <span class="${rootClass}__main-text">Adding ${newFiles.length} new file(s)...</span>
        <span class="${rootClass}__hint-text">${allFiles.length} total files • ${totalSizeMB} MB</span>
      </div>
      <div class="${rootClass}__progress">
        <div class="${rootClass}__progress-fill" style="width: 0%"></div>
      </div>
    `;

    const fileInput = this.createFileInput();
    fileInput.multiple = true;
    el.appendChild(fileInput);

    // Store all files for the uploading state
    el.__files = allFiles;
    el.__existingFiles = this.files;

    return el;
  }

  removeFile(index) {
    this.files.splice(index, 1);

    if (this.files.length === 0) {
      this.resetToEmpty();
    } else {
      this.refreshPreview();
    }
  }

  refreshPreview() {
    const previewEl = this.createPreviewElement();
    previewEl.__files = this.files;

    this.destroy();
    this.element.replaceWith(previewEl);

    import("../DropzoneFactory").then(({ default: DropzoneFactory }) => {
      DropzoneFactory.init(previewEl);
    });
  }

  createPreviewElement() {
    const rootClass = this.rootBaseClass;
    const totalSize = this.files.reduce((sum, f) => sum + (f.size || 0), 0);
    const totalSizeMB = (totalSize / 1024 / 1024).toFixed(1);

    let previewItems = "";
    for (let i = 0; i < this.files.length; i++) {
      const file = this.files[i];
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
          <span class="${rootClass}__main-text">${this.files.length} file(s) uploaded successfully</span>
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

  createEmptyElement() {
    const rootClass = this.rootBaseClass;

    const el = document.createElement("div");
    el.className = rootClass;
    el.dataset.state = "empty";
    el.dataset.mode = "multiple";
    el.innerHTML = `
      <div class="${rootClass}__icon">
        <svg><use href="#icon-upload"></use></svg>
      </div>
      <div class="${rootClass}__text">
        <span class="${rootClass}__main-text">Drag & drop or click to upload</span>
        <span class="${rootClass}__hint-text">PNG, JPG, GIF • Max 5MB each</span>
      </div>
    `;

    const fileInput = this.createFileInput();
    fileInput.multiple = true;
    el.appendChild(fileInput);

    return el;
  }

  resetToEmpty() {
    const emptyEl = this.createEmptyElement();
    this.destroy();
    this.element.replaceWith(emptyEl);

    import("../DropzoneFactory").then(({ default: DropzoneFactory }) => {
      DropzoneFactory.init(emptyEl);
    });
  }

  truncateFilename(filename, maxLength = 20) {
    if (!filename) return "unknown";
    if (filename.length <= maxLength) return filename;
    const ext = filename.split(".").pop();
    const name = filename.slice(0, maxLength - ext.length - 3);
    return `${name}...${ext}`;
  }

  destroy() {
    if (this.input && this.boundChangeHandler) {
      this.input.removeEventListener("change", this.boundChangeHandler);
      this.boundChangeHandler = null;
    }

    const removeBtns = this.element.querySelectorAll(".remove");
    removeBtns.forEach((btn) => {
      if (btn._removeHandler) {
        btn.removeEventListener("click", btn._removeHandler);
        btn._removeHandler = null;
      }
    });

    const addMoreBtn = this.element.querySelector(".add-more");
    if (addMoreBtn && addMoreBtn._clickHandler) {
      addMoreBtn.removeEventListener("click", addMoreBtn._clickHandler);
      addMoreBtn._clickHandler = null;
    }

    const addMoreItem = this.element.querySelector(".add-more-item");
    if (addMoreItem && addMoreItem._clickHandler) {
      addMoreItem.removeEventListener("click", addMoreItem._clickHandler);
      addMoreItem._clickHandler = null;
    }

    this._processingFiles = false;
    super.destroy();
  }
}
