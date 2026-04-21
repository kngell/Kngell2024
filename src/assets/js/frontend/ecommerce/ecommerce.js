import CategoryGap from "./Classes/_CategoryGap";
import LastReview from "./Classes/_LastReview";
import QuantityBoxMirror from "./Classes/_QuantityBoxMirror";
import ProgressBar from "./Classes/checkout/_checkoutProgressBar";
import UserMenu from "js/frontend/ecommerce/modules/user-menu";
import CategorySlider from "js/frontend/ecommerce/components/CategorySlider";

export default class Ecommerce {
  constructor() {
    this.categoryGap = null;
    this.categorySliders = [];
    this._init();
  }

  _init = () => {
    // Set Category Gap
    this._setCategoryGap();
    new LastReview()._init();
    new QuantityBoxMirror();

    // Checkout ProgressBar
    new ProgressBar();
    new UserMenu();

    // Initialize category sliders
    this.initCategorySliders();
  };

  initCategorySliders = () => {
    // Clear existing sliders
    this.categorySliders.forEach((slider) => {
      if (slider.destroy) slider.destroy();
    });
    this.categorySliders = [];

    // Find all category sections
    const sections = document.querySelectorAll(".category-section");

    // Initialize slider for each section
    sections.forEach((section, index) => {
      try {
        const slider = new CategorySlider(section);
        if (slider) {
          this.categorySliders.push(slider);
        }
      } catch (error) {
        console.error(`Error initializing slider ${index + 1}:`, error);
      }
    });
  };

  _setCategoryGap = () => {
    if (!this.categoryGap) {
      this.categoryGap = new CategoryGap();
    }
    this.categoryGap._setCategoryGap();
  };

  handleResize = () => {
    this._setCategoryGap();
    this.categorySliders.forEach((slider) => {
      if (slider.handleResize) slider.handleResize();
    });
  };

  reinit = () => {
    this._init();
  };
}
