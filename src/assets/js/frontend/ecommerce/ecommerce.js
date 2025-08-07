import CategoryGap from "./Classes/_CategoryGap";
import LastReview from "./Classes/_LastReview";
import QuantityBoxMirror from "./Classes/_QuantityBoxMirror";
// import CheckoutForm from "./Classes/checkout/CheckoutForm";
import ProgressBar from "./Classes/checkout/_checkoutProgressBar";

export default class Ecommerce {
  constructor() {
    this.categoryGap = null;
    this._init();
  }

  _init = () => {
    //Set Category
    this._setCategoryGap();
    new LastReview()._init();
    new QuantityBoxMirror();
    // Checkout ProgressBar
    new ProgressBar();

    // const checkout = new CheckoutForm("checkoutForm");
  };

  _setCategoryGap = () => {
    // Create CategoryGap instance only once
    if (!this.categoryGap) {
      this.categoryGap = new CategoryGap();
    }

    // Set/recalculate category gap
    this.categoryGap._setCategoryGap();
  };

  // Public method for handling resize events
  handleResize = () => {
    this._setCategoryGap();
  };

  reinit = () => {
    this._init();
  };
}
