import "js/frontend/home/index";
import "js/ckeditor/ckeditor";

class Main {
  constructor() {
    this._init();
  }
  _init() {}
}

if (document.readyState !== "loading") {
  new Main();
} else {
  document.addEventListener("DOMContentLoaded", () => {
    new Main();
  });
}
