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
    // Prevent double processing
    if (this._processingFiles) {
      logger.debug("⏸️  Skipping duplicate file processing");
      return;
    }

    this._processingFiles = true;

    try {
      const isMultiple = this.fileInput.multiple;
      const allFiles = Array.from(files);

      logger.debug("🔍 Handling files:", {
        isMultiple: isMultiple,
        fileCount: allFiles.length,
        files: allFiles.map((f) => f.name),
      });

      // Clear previous files for single file inputs
      if (!isMultiple) {
        this.clearPreview();
      }

      // ✅ Add files to preview
      allFiles.forEach((file) => this.addMediaItem(file));

      // ✅ Update file input (this will trigger validation)
      this.updateFileInput(allFiles);
    } finally {
      // Reset the flag after a short delay to allow the current processing to complete
      setTimeout(() => {
        this._processingFiles = false;
      }, 100);
    }
  }

  handleFileSelect(e) {
    const files = Array.from(e.target.files);
    this.handleFiles(files);
  }

  // addMediaItem(file) {
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
  //     mediaElement = `<img src="${objectURL}" alt="${file.name}" class="image">`;
  //   } else if (isVideo) {
  //     mediaElement = `<video src="${objectURL}" class="video" controls></video>`;
  //   } else {
  //     mediaElement = `<div class="file-placeholder">${file.name}</div>`;
  //   }

  //   item.innerHTML = `
  //     <div class="media-preview__item--img-container">
  //       ${mediaElement}
  //     </div>

  //        <div class="media-preview__item--icon-success">
  //         <svg class="icon success" aria-label="Success" role="img">
  //           <use href="/public/assets/img/icons-sprite.svg#icon-success"></use>
  //         </svg>
  //       </div>

  //       <button class="media-preview__item--icon-remove" type="button" aria-label="Remove ${file.name}">
  //         <span class="btn__icon">
  //           <svg class="icon cancel" aria-label="Cancel" role="img">
  //             <use href="/public/assets/img/icons-sprite.svg#icon-cancel"></use>
  //           </svg>
  //         </span>
  //       </button>
  //       <div class="media-preview__item--filename">${file.name}</div>
  //     <div class="media-preview__item--filesize">${this.formatFileSize(file.size)}</div>

  //   `;

  //   // Add remove functionality - wait for DOM to be ready
  //   setTimeout(() => {
  //     const removeBtn = item.querySelector(".media-preview__item--icon-remove");
  //     if (removeBtn) {
  //       removeBtn.addEventListener("click", () => this.removeMediaItem(item, objectURL));
  //     } else {
  //       logger.warn("Remove button not found in media item");
  //     }
  //   }, 0);

  //   this.preview.appendChild(item);
  //   this.updateUploadArea();
  // }
  addMediaItem(file, isValidated = false) {
    const item = document.createElement("div");
    item.className = "media-preview__item";
    item.dataset.filename = file.name;

    // Create object URL for preview
    const objectURL = URL.createObjectURL(file);

    // Determine if it's an image or video
    const isImage = file.type.startsWith("image/");
    const isVideo = file.type.startsWith("video/");

    let mediaElement = "";

    if (isImage) {
      mediaElement = `
      <img src="" alt="${file.name}" class="image" data-blob-src="${objectURL}">
      <div class="media-preview__item--loading">Loading...</div>
    `;
    } else if (isVideo) {
      mediaElement = `<video src="${objectURL}" class="video" controls></video>`;
    } else {
      mediaElement = `<div class="file-placeholder">${file.name}</div>`;
    }

    // Add success icon if file is validated
    const successIcon = isValidated
      ? `
    <div class="media-preview__item--icon-success">
      <svg class="icon success" aria-label="Success" role="img">
        <use href="/public/assets/img/icons-sprite.svg#icon-success"></use>
      </svg>
    </div>
  `
      : "";

    item.innerHTML = `
    <div class="media-preview__item--img-container">
      ${mediaElement}
    </div>
    
    ${successIcon}
       
    <button class="media-preview__item--icon-remove" type="button" aria-label="Remove ${file.name}">
      <span class="btn__icon">
        <svg class="icon cancel" aria-label="Cancel" role="img">
          <use href="/public/assets/img/icons-sprite.svg#icon-cancel"></use>
        </svg>
      </span>
    </button>
    <div class="media-preview__item--filename">${file.name}</div>
    <div class="media-preview__item--filesize">${this.formatFileSize(file.size)}</div>
  `;

    // Add remove functionality and image loading
    setTimeout(() => {
      const removeBtn = item.querySelector(".media-preview__item--icon-remove");
      if (removeBtn) {
        removeBtn.addEventListener("click", () => this.removeMediaItem(item, objectURL));
      }

      // Handle image loading
      if (isImage) {
        const img = item.querySelector("img.image");
        const loadingIndicator = item.querySelector(".media-preview__item--loading");

        if (img) {
          img.onload = () => {
            logger.debug("✅ Image loaded successfully:", file.name);
            if (loadingIndicator) {
              loadingIndicator.style.display = "none";
            }
            // Add success icon after image loads (if not already added)
            if (!isValidated) {
              this.addSuccessIcon(item);
            }
          };

          img.onerror = (e) => {
            logger.error("❌ Failed to load image blob:", file.name, objectURL, e);
            if (loadingIndicator) {
              loadingIndicator.textContent = "Failed to load";
            }
            URL.revokeObjectURL(objectURL);
          };

          img.src = objectURL;
        }
      } else if (isValidated) {
        // For non-image files that are validated, ensure success icon is shown
        this.addSuccessIcon(item);
      }
    }, 0);

    this.preview.appendChild(item);
    this.updateUploadArea();
  }

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
    // Revoke the object URL to free memory
    URL.revokeObjectURL(objectURL);

    // Remove the item from DOM
    item.remove();

    // Update the file input and UI state
    this.updateFileInputAfterRemoval(item.dataset.filename);
    this.updateUploadArea();
  }

  updateFileInput(files) {
    logger.debug("=== updateFileInput called ===");

    // Add flag to prevent recursion
    if (this._updatingFileInput) {
      logger.debug("⏸️  Skipping recursive updateFileInput call");
      return;
    }

    this._updatingFileInput = true;

    try {
      logger.debug("Input files before:", this.fileInput.files);
      logger.debug("New files to add:", files);

      // DON'T automatically truncate for single-file inputs
      // Let the validator handle max_files rule instead
      const dt = new DataTransfer();

      // Add ALL files to DataTransfer (don't truncate)
      files.forEach((file) => dt.items.add(file));

      this.fileInput.files = dt.files;

      logger.debug("Files after update (ALL files kept):", {
        fileCount: this.fileInput.files.length,
        fileNames: Array.from(this.fileInput.files).map((f) => f.name),
      });

      // Only trigger change event if we're not already processing one
      if (!this._processingExternalChange) {
        const changeEvent = new Event("change", { bubbles: true });
        this.fileInput.dispatchEvent(changeEvent);
        logger.debug("🎯 File input change event dispatched for validation");
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
  }

  clearPreview() {
    // Revoke all object URLs first
    const items = this.preview.querySelectorAll(".media-preview__item");
    items.forEach((item) => {
      const img = item.querySelector("img, video");
      if (img && img.src.startsWith("blob:")) {
        URL.revokeObjectURL(img.src);
      }
    });

    // Clear the preview container
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
