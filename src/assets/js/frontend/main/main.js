import "js/frontend/home/index";
import Dropzone from "js/frontend/main/components/dropzone";
import ImportFiles from "js/core/_importFiles";
import emptyCart from "src/assets/img/empty_cart.png";
import UserCart from "js/frontend/main/components/_userCart";
import Ecommerce from "js/frontend/ecommerce/ecommerce";

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
    //Ecommerce
    const ecommerce = new Ecommerce();

    // Handle resize events with debouncing for better performance
    let resizeTimeout;
    window.addEventListener("resize", () => {
      clearTimeout(resizeTimeout);
      resizeTimeout = setTimeout(() => {
        // Use the dedicated resize handler for better performance
        ecommerce.handleResize();
      }, 250); // 250ms debounce delay
    });
    // Import SVG icons
    // this.importIcons();

    //Manage User cart
    // new UserCart();
  }

  importIcons() {
    try {
      // Create an instance of ImportFiles
      const importer = new ImportFiles();

      // Use the importAll method to import all SVG icons
      // This uses webpack's require.context to find all files in the icons directory
      // const iconsContext = require.context(
      //   "src/assets/img/icons",
      //   false,
      //   /\.(svg|png|jpe?g|gif)$/
      // );
      // importer.importAll(iconsContext);

      // console.log("Icons imported successfully");
    } catch (error) {
      console.error("Error importing icons:", error);
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
