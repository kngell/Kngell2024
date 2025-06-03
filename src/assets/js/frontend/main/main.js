import "js/frontend/home/index";
import Dropzone from "js/frontend/main/components/dropzone";
import ImportFiles from "js/frontend/main/components/_importFiles";
import emptyCart from "src/assets/img/empty_cart.png";
import UserCart from "js/frontend/main/components/_userCart";

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
    //Manage User cart
    // new UserCart();
  }
}

if (document.readyState !== "loading") {
  new Main();
} else {
  document.addEventListener("DOMContentLoaded", () => {
    new Main();
  });
}
