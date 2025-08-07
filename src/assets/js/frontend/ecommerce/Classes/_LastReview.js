export default class LastReview {
  constructor() {
    this._init();
  }
  _init = () => {
    this._setTopPosition();
  };
  _setTopPosition = () => {
    const reviewsBody = document.querySelector(".reviews-body");
    const reviews = reviewsBody ? reviewsBody.querySelectorAll(".review") : [];

    if (reviews.length > 0) {
      const lastReview = reviews[reviews.length - 1];

      // Temporarily ensure it's in the flow to get its natural position
      // Make sure your CSS does NOT have position: absolute initially for last-of-type
      // Or remove it with JS first, calculate, then reapply.
      // A safer way is to apply a class that sets position: absolute later.
      lastReview.style.position = "static"; // Ensure it's static for calculation
      lastReview.style.top = ""; // Clear any previous top values

      // Get the top offset relative to the .reviews-body parent
      const offsetTop = lastReview.offsetTop;
      const offsetLeft = lastReview.offsetLeft; // Keep for horizontal if needed
      const elementWidth = lastReview.offsetWidth; // Keep for width if needed

      // Now, apply the absolute positioning and calculated top
      lastReview.style.position = "absolute";
      lastReview.style.top = `${offsetTop}px`;
      lastReview.style.left = `${offsetLeft}px`; // Match original left position
      lastReview.style.width = `${elementWidth}px`; // Match original width
      lastReview.style.opacity = "0.1";
      lastReview.style.zIndex = "-1";
      lastReview.style.background =
        "linear-gradient(204deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 1) 100%)";
    }
  };
}
