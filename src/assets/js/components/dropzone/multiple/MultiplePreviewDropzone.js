import BaseDropzone from "../BaseDropzone";
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("MultiplePreviewDropzone");

export default class MultiplePreviewDropzone extends BaseDropzone {
  constructor(element, files) {
    super(element);
    this.files = files || [];

    // CRITICAL: Sync files to the new input
    this.syncFilesToInput(this.files);

    logger.debug("MultiplePreviewDropzone initialized", { fileCount: this.files.length });

    this.setupPreviewListeners();
  }

  setupPreviewListeners() {
    // Remove individual files
    const removeButtons = this.element.querySelectorAll(".remove");
    removeButtons.forEach((btn) => {
      // Remove existing listener to prevent duplicates
      if (btn._removeHandler) {
        btn.removeEventListener("click", btn._removeHandler);
      }

      btn._removeHandler = (e) => {
        e.stopPropagation();
        const index = btn.dataset.index;
        if (index !== undefined) {
          this.removeFile(parseInt(index));
        }
      };

      btn.addEventListener("click", btn._removeHandler);
    });

    // Add more button
    const addMoreBtn = this.element.querySelector(".add-more");
    if (addMoreBtn) {
      if (addMoreBtn._clickHandler) {
        addMoreBtn.removeEventListener("click", addMoreBtn._clickHandler);
      }

      addMoreBtn._clickHandler = (e) => {
        e.stopPropagation();
        this.input.click();
      };

      addMoreBtn.addEventListener("click", addMoreBtn._clickHandler);
    }

    // Add more item (plus icon)
    const addMoreItem = this.element.querySelector(".add-more-item");
    if (addMoreItem) {
      if (addMoreItem._clickHandler) {
        addMoreItem.removeEventListener("click", addMoreItem._clickHandler);
      }

      addMoreItem._clickHandler = (e) => {
        e.stopPropagation();
        this.input.click();
      };

      addMoreItem.addEventListener("click", addMoreItem._clickHandler);
    }

    // File input for adding more - NO CLONING
    if (this.input) {
      if (this.boundChangeHandler) {
        this.input.removeEventListener("change", this.boundChangeHandler);
      }

      this.boundChangeHandler = async (e) => {
        const newFiles = Array.from(e.target.files || []);
        if (newFiles.length > 0) {
          await this.addFiles(newFiles);
        }
        // Reset input to allow selecting the same files again
        this.input.value = "";
      };

      this.input.addEventListener("change", this.boundChangeHandler);
    }
  }

  // CRITICAL: Override handleFiles to handle drops on preview
  handleFiles(files) {
    // Prevent multiple triggers
    if (this._processingFiles) return;
    this._processingFiles = true;

    logger.debug("Files dropped on multiple preview", { count: files.length });
    this.addFiles(files);

    setTimeout(() => {
      this._processingFiles = false;
    }, 300);
  }

  async addFiles(newFiles) {
    // Create a Set of existing file identifiers
    const existingFileKeys = new Set(
      this.files.map((file) => `${file.name}-${file.size}-${file.lastModified}`)
    );

    // Filter out duplicates
    const uniqueFiles = [];
    const duplicates = [];

    newFiles.forEach((file) => {
      const fileKey = `${file.name}-${file.size}-${file.lastModified}`;

      if (existingFileKeys.has(fileKey)) {
        duplicates.push(file);
        logger.debug(`Duplicate file detected: ${file.name}`);
      } else {
        uniqueFiles.push(file);
        existingFileKeys.add(fileKey);
      }
    });

    // Show warning if there were duplicates
    if (duplicates.length > 0) {
      this.showDuplicateWarning(duplicates.length);
    }

    // If no unique files, exit early
    if (uniqueFiles.length === 0) {
      logger.debug("No unique files to upload");
      return;
    }

    // Store all files (existing + new unique files)
    this.files = [...this.files, ...uniqueFiles];

    // CRITICAL: Sync all files to input
    this.syncFilesToInput(this.files);

    // Go to uploading state for new files
    const uploadingEl = this.createUploadingElement(uniqueFiles);
    this.destroy();
    this.element.replaceWith(uploadingEl);

    import("./MultipleUploadingDropzone").then((module) => {
      new module.default(uploadingEl, this.files);
    });
  }

  showDuplicateWarning(count) {
    const message =
      count === 1 ? "1 duplicate file was skipped" : `${count} duplicate files were skipped`;

    const warning = document.createElement("div");
    warning.className = "upload-duplicate-warning";
    warning.textContent = message;
    document.body.appendChild(warning);

    setTimeout(() => warning.remove(), 3000);
  }

  createUploadingElement(newFiles) {
    const rootClass = this.rootBaseClass;

    const el = document.createElement("div");
    el.className = `${rootClass} ${rootClass}--uploading`;
    el.dataset.state = "uploading";
    el.dataset.mode = "multiple";
    el.innerHTML = `
      <div class="${rootClass}__icon">
        <svg><use href="#icon-upload"></use></svg>
      </div>
      <div class="${rootClass}__text">
        <span class="${rootClass}__main-text">Adding ${newFiles.length} new files...</span>
        <span class="${rootClass}__hint-text">0% complete</span>
      </div>
      <div class="${rootClass}__progress">
        <div class="${rootClass}__progress-fill" style="width: 0%"></div>
      </div>
      <input type="file" name="image_url[]" multiple accept="image/*">
    `;

    return el;
  }

  removeFile(index) {
    this.files.splice(index, 1);

    // CRITICAL: Sync updated files to input
    this.syncFilesToInput(this.files);

    if (this.files.length === 0) {
      this.reset();
    } else {
      this.refreshPreview();
    }
  }

  refreshPreview() {
    const newEl = this.createPreviewElement();
    this.destroy();
    this.element.replaceWith(newEl);

    import("./MultiplePreviewDropzone").then((module) => {
      new module.default(newEl, this.files);
    });
  }

  createPreviewElement() {
    const rootClass = this.rootBaseClass;

    const previewItems = this.files
      .map((file, index) => {
        const url = file instanceof File ? URL.createObjectURL(file) : file;
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
      <input type="file" name="image_url[]" multiple accept="image/*">
    `;

    return el;
  }

  async reset() {
    // CRITICAL: Clear the file input
    this.syncFilesToInput([]);

    const emptyEl = this.createEmptyElement();
    this.destroy();
    this.element.replaceWith(emptyEl);

    import("./MultipleEmptyDropzone").then((module) => {
      new module.default(emptyEl);
    });
  }

  destroy() {
    // Clean up all listeners
    if (this.input && this.boundChangeHandler) {
      this.input.removeEventListener("change", this.boundChangeHandler);
    }

    // Clean up button listeners
    const removeButtons = this.element.querySelectorAll(".remove");
    removeButtons.forEach((btn) => {
      if (btn._removeHandler) {
        btn.removeEventListener("click", btn._removeHandler);
      }
    });

    const addMoreBtn = this.element.querySelector(".add-more");
    if (addMoreBtn && addMoreBtn._clickHandler) {
      addMoreBtn.removeEventListener("click", addMoreBtn._clickHandler);
    }

    const addMoreItem = this.element.querySelector(".add-more-item");
    if (addMoreItem && addMoreItem._clickHandler) {
      addMoreItem.removeEventListener("click", addMoreItem._clickHandler);
    }

    this._processingFiles = false;
    super.destroy();
  }
}
