import "js/frontend/home/index";
import Dropzone from "js/frontend/main/components/dropzone";

class Main {
  constructor() {
    this._init();
  }
  _init() {
    // Manage dropzone area
    const dropzone = document.querySelector("#dropzone");
    if (dropzone !== null) {
      new Dropzone(dropzone);
    }
  }
}

if (document.readyState !== "loading") {
  new Main();
} else {
  document.addEventListener("DOMContentLoaded", () => {
    new Main();
  });
}
