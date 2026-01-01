import BrowserLogger from "js/utils/logger";
const logger = new BrowserLogger("MediaUpload");

export default class MediaUpload {
  constructor(container) {
    this.container = container;

    // Store the instance on the container for external access
    this.container._mediaUploadInstance = this;

    // Use optional chaining and null checks
    this.fileInput = container.querySelector(".media-file");
    this.preview = container.querySelector(".media-preview");
    this.uploadArea = container;

    // Check if required elements exist
    if (!this.fileInput || !this.preview || !this.uploadArea) {
      logger.warn("MediaUpload: Required elements not found", {
        fileInput: !!this.fileInput,
        preview: !!this.preview,
        uploadArea: !!this.uploadArea,
      });
      return;
    }
    // Bind methods once in constructor
    this.preventDefaults = this.preventDefaults.bind(this);
    this.highlight = this.highlight.bind(this);
    this.unhighlight = this.unhighlight.bind(this);
    this.handleDrop = this.handleDrop.bind(this);
    this.handleFileSelect = this.handleFileSelect.bind(this);
    // Clear any existing preview items on initialization
    // this.clearPreview();
    this.init();
  }

  init() {
    this.fileInput.addEventListener("change", (e) => this.handleFileSelect(e));
    this.setupDragAndDrop();
    this.updateUploadArea(); // Initialize state
  }

  setupDragAndDrop() {
    // Now use the bound methods directly
    ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
      this.uploadArea.addEventListener(eventName, this.preventDefaults, false);
    });

    ["dragenter", "dragover"].forEach((eventName) => {
      this.uploadArea.addEventListener(eventName, this.highlight, false);
    });

    ["dragleave", "drop"].forEach((eventName) => {
      this.uploadArea.addEventListener(eventName, this.unhighlight, false);
    });

    this.uploadArea.addEventListener("drop", this.handleDrop, false);
  }

  preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  highlight() {
    this.uploadArea.style.backgroundColor = "#f0f9ff"; // Light blue background
    this.uploadArea.style.borderColor = "#3b82f6"; // Blue border
    this.uploadArea.classList.add("drag-over");
  }

  unhighlight() {
    this.uploadArea.style.backgroundColor = "";
    this.uploadArea.style.borderColor = "";
    this.uploadArea.classList.remove("drag-over");
  }

  handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    this.handleFiles(files);
  }

  handleFiles(files) {
    if (this._processingExternalChange || this._processingFiles) return;
    this._processingFiles = true;

    try {
      const isMultiple = this.fileInput.multiple;
      let newFiles = Array.from(files);
      if (!isMultiple) {
        this.clearPreview();
        newFiles = [newFiles[0]];
      }

      newFiles.forEach((file) => this.addMediaItem(file));

      this.updateFileInput(newFiles);
    } finally {
      setTimeout(() => {
        this._processingFiles = false;
      }, 100);
    }
  }

  handleFileSelect(e) {
    const files = Array.from(e.target.files);
    this.handleFiles(files);
  }
  addMediaItem(file, isValidated = false) {
    const item = document.createElement("div");
    item.className = "media-preview__item";
    item.dataset.filename = file.name;

    const objectURL = URL.createObjectURL(file);
    const isImage = file.type.startsWith("image/");
    const isVideo = file.type.startsWith("video/");

    // 1. Generate Template
    let mediaElement = "";
    if (isImage) {
      mediaElement = `
            <img src="" alt="${file.name}" class="image" data-blob-src="${objectURL}">
            <div class="media-preview__item--loading">Loading...</div>`;
    } else if (isVideo) {
      mediaElement = `<video src="${objectURL}" class="video" controls></video>`;
    } else {
      mediaElement = `<div class="file-placeholder">${file.name}</div>`;
    }

    const successIconHtml = isValidated ? this._getSuccessIconHtml() : "";

    item.innerHTML = `
        <div class="media-preview__item--img-container">${mediaElement}</div>
        ${successIconHtml}
        <button class="media-preview__item--icon-remove" type="button" aria-label="Remove ${file.name}">
            <span class="btn__icon">
                <svg class="icon cancel"><use href="/public/assets/img/icons-sprite.svg#icon-cancel"></use></svg>
            </span>
        </button>
        <div class="media-preview__item--filename">${file.name}</div>
        <div class="media-preview__item--filesize">${this.formatFileSize(file.size)}</div>
    `;

    // 2. Attach Remove Listener Immediately (No setTimeout)
    const removeBtn = item.querySelector(".media-preview__item--icon-remove");
    if (removeBtn) {
      removeBtn.addEventListener("click", (e) => {
        e.preventDefault();
        this.removeMediaItem(item, objectURL);
      });
    }

    // 3. Handle Image Loading Logic
    if (isImage) {
      const img = item.querySelector("img.image");
      const loadingIndicator = item.querySelector(".media-preview__item--loading");

      if (img) {
        // Define handlers BEFORE setting src
        img.onload = () => {
          logger.debug("✅ Image loaded:", file.name);
          if (loadingIndicator) loadingIndicator.style.display = "none";
          if (!isValidated) this.addSuccessIcon(item);
        };

        img.onerror = () => {
          logger.error("❌ Failed to load image:", file.name);
          if (loadingIndicator) loadingIndicator.textContent = "Error";
          URL.revokeObjectURL(objectURL);
        };

        img.src = objectURL;
      }
    } else if (isValidated) {
      this.addSuccessIcon(item);
    }

    // 4. Append to UI
    this.preview.appendChild(item);
    this.updateUploadArea();
  }
  // addMediaItem(file, isValidated = false) {
  //   const item = document.createElement("div");
  //   item.className = "media-preview__item";
  //   item.dataset.filename = file.name;

  //   // Create object URL for preview
  //   const objectURL = URL.createObjectURL(file);

  //   // Determine if it's an image or video
  //   const isImage = file.type.startsWith("image/");
  //   const isVideo = file.type.startsWith("video/");

  //   let mediaElement = "";

  //   if (isImage) {
  //     mediaElement = `
  //     <img src="" alt="${file.name}" class="image" data-blob-src="${objectURL}">
  //     <div class="media-preview__item--loading">Loading...</div>
  //   `;
  //   } else if (isVideo) {
  //     mediaElement = `<video src="${objectURL}" class="video" controls></video>`;
  //   } else {
  //     mediaElement = `<div class="file-placeholder">${file.name}</div>`;
  //   }

  //   // Add success icon if file is validated
  //   const successIcon = isValidated
  //     ? `
  //   <div class="media-preview__item--icon-success">
  //     <svg class="icon success" aria-label="Success" role="img">
  //       <use href="/public/assets/img/icons-sprite.svg#icon-success"></use>
  //     </svg>
  //   </div>
  // `
  //     : "";

  //   item.innerHTML = `
  //   <div class="media-preview__item--img-container">
  //     ${mediaElement}
  //   </div>

  //   ${successIcon}

  //   <button class="media-preview__item--icon-remove" type="button" aria-label="Remove ${file.name}">
  //     <span class="btn__icon">
  //       <svg class="icon cancel" aria-label="Cancel" role="img">
  //         <use href="/public/assets/img/icons-sprite.svg#icon-cancel"></use>
  //       </svg>
  //     </span>
  //   </button>
  //   <div class="media-preview__item--filename">${file.name}</div>
  //   <div class="media-preview__item--filesize">${this.formatFileSize(file.size)}</div>
  // `;

  //   // Add remove functionality and image loading

  //   const removeBtn = item.querySelector(".media-preview__item--icon-remove");
  //   if (removeBtn) {
  //     removeBtn.addEventListener("click", () => this.removeMediaItem(item, objectURL));
  //   }

  //   // Handle image loading
  //   if (isImage) {
  //     const img = item.querySelector("img.image");
  //     const loadingIndicator = item.querySelector(".media-preview__item--loading");

  //     if (img) {
  //       img.onload = () => {
  //         logger.debug("✅ Image loaded successfully:", file.name);
  //         if (loadingIndicator) {
  //           loadingIndicator.style.display = "none";
  //         }
  //         // Add success icon after image loads (if not already added)
  //         if (!isValidated) {
  //           this.addSuccessIcon(item);
  //         }
  //       };

  //       img.onerror = (e) => {
  //         logger.error("❌ Failed to load image blob:", file.name, objectURL, e);
  //         if (loadingIndicator) {
  //           loadingIndicator.textContent = "Failed to load";
  //         }
  //         URL.revokeObjectURL(objectURL);
  //       };

  //       img.src = objectURL;
  //     }
  //   } else if (isValidated) {
  //     // For non-image files that are validated, ensure success icon is shown
  //     this.addSuccessIcon(item);
  //   }

  //   this.preview.appendChild(item);
  //   this.updateUploadArea();
  // }

  // Helper method to add success icon
  addSuccessIcon(item) {
    if (!item.querySelector(".media-preview__item--icon-success")) {
      const successIcon = document.createElement("div");
      successIcon.className = "media-preview__item--icon-success";
      successIcon.innerHTML = `
      <svg class="icon success" aria-label="Success" role="img">
        <use href="/public/assets/img/icons-sprite.svg#icon-success"></use>
      </svg>
    `;

      const removeBtn = item.querySelector(".media-preview__item--icon-remove");
      if (removeBtn) {
        removeBtn.parentNode.insertBefore(successIcon, removeBtn);
      }
    }
  }

  removeMediaItem(item, objectURL) {
    logger.debug("🗑️ removeMediaItem triggered for:", item.dataset.filename);

    // 1. Memory Cleanup
    if (objectURL) URL.revokeObjectURL(objectURL);

    // 2. Data Cleanup (Do this BEFORE UI removal to stay safe)
    const filenameToRemove = item.dataset.filename;
    const dt = new DataTransfer();
    Array.from(this.fileInput.files).forEach((file) => {
      if (file.name !== filenameToRemove) {
        dt.items.add(file);
      }
    });

    // 3. UI Cleanup
    // Forcefully remove the element from the DOM
    if (item && item.parentNode) {
      item.parentNode.removeChild(item);
    } else {
      item.remove();
    }

    // 4. Update the input data
    this.fileInput.files = dt.files;

    // 5. Trigger Validation
    // Use a flag to ensure handleFiles doesn't catch this change event and re-add the file
    this._processingExternalChange = true;
    this.fileInput.dispatchEvent(new Event("change", { bubbles: true }));
    this._processingExternalChange = false;

    this.updateUploadArea();
    logger.debug(`✅ UI Item removed. Data count: ${this.fileInput.files.length}`);
  }

  updateFileInput(files) {
    if (this._updatingFileInput) return;
    this._updatingFileInput = true;

    try {
      const dt = new DataTransfer();

      files.forEach((file) => dt.items.add(file));

      this.fileInput.files = dt.files;

      if (!this._processingExternalChange) {
        this.fileInput.dispatchEvent(new Event("change", { bubbles: true }));
      }
    } finally {
      this._updatingFileInput = false;
    }
  }

  updateFileInputAfterRemoval(filename) {
    const dt = new DataTransfer();

    // Rebuild file list excluding the removed file
    for (let file of this.fileInput.files) {
      if (file.name !== filename) {
        dt.items.add(file);
      }
    }

    this.fileInput.files = dt.files;

    const changeEvent = new Event("change", { bubbles: true });
    this.fileInput.dispatchEvent(changeEvent);

    logger.debug("🎯 File input change event dispatched after removal", {
      remaining: this.fileInput.files.length,
    });
    // ----------------------
  }

  clearPreview() {
    const items = this.preview.querySelectorAll(".media-preview__item");
    items.forEach((item) => {
      const img = item.querySelector("img, video");
      if (img && img.src.startsWith("blob:")) {
        URL.revokeObjectURL(img.src);
      }
      item.remove();
    });
    this.preview.innerHTML = "";
  }

  updateUploadArea() {
    const hasItems = this.preview.children.length > 0;
    this.uploadArea.classList.toggle("empty", !hasItems);
    this.uploadArea.classList.toggle("has-media", hasItems);
  }

  formatFileSize(bytes) {
    if (bytes === 0) return "0 Bytes";
    const k = 1024;
    const sizes = ["Bytes", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
  }

  destroy() {
    // Now these will work correctly
    ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
      this.uploadArea.removeEventListener(eventName, this.preventDefaults);
    });

    ["dragenter", "dragover"].forEach((eventName) => {
      this.uploadArea.removeEventListener(eventName, this.highlight);
    });

    ["dragleave", "drop"].forEach((eventName) => {
      this.uploadArea.removeEventListener(eventName, this.unhighlight);
    });

    this.uploadArea.removeEventListener("drop", this.handleDrop);
  }
}
