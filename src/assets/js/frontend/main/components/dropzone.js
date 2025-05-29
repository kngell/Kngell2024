export default class Dropzone {
  constructor(dropzone) {
    this.dropzone = dropzone;
    this.dropzoneInput = dropzone.querySelector(".dropzone__input");
    this.dropzoneBeforeUpload = dropzone.querySelector(
      ".dropzone__before-upload"
    );
    this.dropzoneAfterUpload = dropzone.querySelector(
      ".dropzone__after-upload"
    );
    this.dropzoneImgPreview = dropzone.querySelector(
      ".dropzone__after-upload--img-preview"
    );
    this.dropzoneClearBtn = dropzone.querySelector(
      ".dropzone__after-upload--btn-clear"
    );

    this.init();
  }

  init = () => {
    this.dropzoneBeforeUpload.addEventListener("click", (e) =>
      this.handleClick(e)
    );
    this.dropzone.addEventListener("dragenter", (e) => this.handleDragEnter(e));
    this.dropzone.addEventListener("dragover", (e) => this.handleDragOver(e));
    this.dropzone.addEventListener("dragleave", (e) => this.handleDragLeave(e));
    this.dropzone.addEventListener("drop", (e) => this.handleDrop(e));
    this.dropzoneInput.addEventListener("change", (e) =>
      this.handleFileSelect(e)
    );
    this.dropzoneClearBtn.addEventListener("click", (e) =>
      this.clearImagePreview(e)
    );
  };
  handleClick = (e) => {
    e.preventDefault();
    e.stopPropagation();
    this.dropzoneInput.click();
  };
  clearImagePreview = (e) => {
    e.preventDefault();
    e.stopPropagation();
    this.dropzoneImgPreview.src = "";
    this.dropzoneBeforeUpload.style.display = "flex";
    this.dropzoneAfterUpload.style.display = "none";
    this.dropzoneInput.value = "";
  };
  handleDragEnter = (e) => {
    e.preventDefault();
    e.stopPropagation();
    this.dropzone.classList.add("active");
  };
  handleDragOver = (e) => {
    e.preventDefault();
    e.stopPropagation();
    this.dropzone.classList.add("active");
  };
  handleDragLeave = (e) => {
    e.preventDefault();
    e.stopPropagation();
    this.dropzone.classList.remove("active");
  };

  handleDrop = (e) => {
    e.preventDefault();
    e.stopPropagation();
    this.dropzone.classList.remove("dragover");
    const files = e.dataTransfer.files;
    if (files.length > 0) {
      this.handleFiles(files);
    }
  };

  handleFileSelect = (e) => {
    const files = e.target.files;
    if (files.length > 0) {
      this.handleFiles(files);
    }
  };

  handleFiles = (files) => {
    const file = files[0];
    // Preview image
    const reader = new FileReader();
    reader.onload = (e) => {
      this.dropzoneImgPreview.src = e.target.result;
      this.dropzoneBeforeUpload.style.display = "none";
      this.dropzoneAfterUpload.style.display = "block";
    };
    reader.readAsDataURL(file);

    // Attach file to form input File
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    this.dropzoneInput.files = dataTransfer.files;
  };
}

// if (document.readyState !== "loading") {
//   new Dropzone(document.querySelector("#dropzone"));
// } else {
//   document.addEventListener("DOMContentLoaded", () => {
//     new Dropzone(document.querySelector("#dropzone"));
//   });
// }
