// Description: This file contains the DropFile class which handles the drag and drop functionality for the file upload area.
export default class DropFile {
  constructor() {
    this.dropArea = document.getElementById("drop-area");
    this.inputFile = document.getElementById("drop-area__input");
    this.dropAreaView = document.getElementById("drop-area__view");
  }
  upload() {
    const imgLink = URL.createObjectURL(this.inputFile.files[0]);
    this.dropAreaView.style.backgroundImage = `url(${imgLink})`;
    this.dropAreaView.style.backgroundRepeat = "no-repeat";
    this.dropAreaView.style.border = "none";
    this.dropAreaView.style.boxShadow = "none";
    this.dropAreaView.style.transition = "all 0.5s ease";
    this.dropAreaView.style.opacity = "1";
    this.dropAreaView.style.filter = "blur(0)";
    this.dropAreaView.style.pointerEvents = "none";
    this.dropAreaView.textContent = "";
  }
  handle() {
    this.inputFile.addEventListener("change", (e) => {
      this.inputFile.files = e.target.files;
      this.upload();
    });
    this.dropArea.addEventListener("drop", (e) => {
      e.preventDefault();
      e.stopPropagation();
      this.inputFile.files = e.dataTransfer.files;
      this.upload();
    });
    this.dropArea.addEventListener("dragover", (e) => {
      e.preventDefault();
      e.stopPropagation();
    });
    // this.dropArea.addEventListener("dragenter", (e) => {
    //   e.preventDefault();
    //   e.stopPropagation();
    //   this.dropArea.classList.add("dragover");
    // });

    // this.dropArea.addEventListener("dragleave", (e) => {
    //   e.preventDefault();
    //   e.stopPropagation();
    //   this.dropArea.classList.remove("dragover");
    // });
  }
}
