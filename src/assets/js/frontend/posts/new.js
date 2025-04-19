// import Dropzone from "./components/dropzone";
import Table from "./components/table";
class New {
  constructor() {
    this._init();
  }
  _init() {
    // new Dropzone(document.querySelector("#dropzone"));
    new Table(document.querySelector(".table-container"));
  }
}

if (document.readyState !== "loading") {
  new New();
} else {
  document.addEventListener("DOMContentLoaded", () => {
    new New();
  });
}
