export default class MediaUpload {
  constructor(container) {
    this.container = container;

    // Use optional chaining and null checks
    this.fileInput = container.querySelector(".media-file");
    this.preview = container.querySelector(".media-preview");
    this.uploadArea = container.querySelector(".input-box__media-upload");

    // Check if required elements exist
    if (!this.fileInput || !this.preview || !this.uploadArea) {
      console.warn("MediaUpload: Required elements not found", {
        fileInput: !!this.fileInput,
        preview: !!this.preview,
        uploadArea: !!this.uploadArea,
      });
      return;
    }
    this.init();
  }

  init() {
    this.fileInput.addEventListener("change", (e) => this.handleFileSelect(e));
    this.setupDragAndDrop(); // This method was missing!
  }

  // Add the missing method
  setupDragAndDrop() {
    // Prevent default drag behaviors
    ["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
      this.uploadArea.addEventListener(eventName, this.preventDefaults.bind(this), false);
      document.body.addEventListener(eventName, this.preventDefaults.bind(this), false);
    });

    // Highlight drop area when item is dragged over it
    ["dragenter", "dragover"].forEach((eventName) => {
      this.uploadArea.addEventListener(eventName, this.highlight.bind(this), false);
    });

    ["dragleave", "drop"].forEach((eventName) => {
      this.uploadArea.addEventListener(eventName, this.unhighlight.bind(this), false);
    });

    // Handle dropped files
    this.uploadArea.addEventListener("drop", this.handleDrop.bind(this), false);
  }

  preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  highlight() {
    this.uploadArea.style.backgroundColor = map - deep - get($color - primary, primary - 50); // Use your color variable
    this.uploadArea.style.borderColor = map - deep - get($color - primary, primary - 300);
  }

  unhighlight() {
    this.uploadArea.style.backgroundColor = "";
    this.uploadArea.style.borderColor = "";
  }

  handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    this.handleFiles(files);
  }

  handleFiles(files) {
    Array.from(files).forEach((file) => {
      if (file.type.match("image.*")) {
        this.addMediaItem(file);
      }
    });
  }

  handleFileSelect(e) {
    const files = Array.from(e.target.files);
    files.forEach((file) => this.addMediaItem(file));
  }

  addMediaItem(file) {
    const item = document.createElement("div");
    item.className = "media-preview__item";

    // Create object URL for image preview
    const objectURL = URL.createObjectURL(file);

    item.innerHTML = `
      <div class="media-preview__item--img-container">
        <img src="${objectURL}" alt="${file.name}" class="image">
      </div>
      <div class="media-preview__item--icon-container">
        <svg class="icon success" aria-label="Success" role="img">
          <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-success"></use>
        </svg>
      </div>
      <button class="media-preview__item--remove" type="button">
        <svg class="icon cancel" aria-label="Cancel" role="img">
          <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel"></use>
        </svg>
      </button>
      <div class="media-preview__item--filename">${file.name}</div>
    `;

    // Add remove functionality
    const removeBtn = item.querySelector(".media-preview__item--remove");
    removeBtn.addEventListener("click", () => this.removeMediaItem(item, objectURL));

    this.preview.appendChild(item);

    // Update upload area visibility
    this.updateUploadArea();
  }

  removeMediaItem(item, objectURL) {
    item.remove();
    // Revoke object URL to free memory
    URL.revokeObjectURL(objectURL);
    this.updateUploadArea();
  }

  updateUploadArea() {
    const hasItems = this.preview.children.length > 0;
    this.uploadArea.classList.toggle("has-media", hasItems);
  }
}
