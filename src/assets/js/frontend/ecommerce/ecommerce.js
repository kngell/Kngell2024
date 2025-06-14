import CategoryGap from "./Classes/_CategoryGap";

export default class Ecommerce {
  constructor() {
    this.categoryGap = null;
    this._init();
  }

  _init = () => {
    //Set Category
    this._setCategoryGap();
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

  // Method to reinitialize everything (useful for dynamic content changes)
  reinit = () => {
    this._init();
  };
}
