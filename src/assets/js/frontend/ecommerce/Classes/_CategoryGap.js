export default class CategoryGap {
  constructor() {
    this._init();
  }
  _init = () => {};
  _setCategoryGap = () => {
    // Place this after DOMContentLoaded or in your main JS bundle

    const nav = document.querySelector(".category-nav");
    if (!nav) return;

    const links = nav.querySelectorAll(".category-nav .category-nav__link");
    if (links.length < 2) return;

    // Get total width of all flex items
    let totalItemsWidth = 0;
    links.forEach((link) => {
      const style = window.getComputedStyle(link);
      totalItemsWidth +=
        link.offsetWidth +
        parseFloat(style.marginLeft) +
        parseFloat(style.marginRight);
    });

    // Calculate the gap as per space-between
    const style = window.getComputedStyle(nav);
    const navWidth =
      nav.clientWidth -
      parseFloat(style.paddingLeft) -
      parseFloat(style.paddingRight);

    const gap = (navWidth - totalItemsWidth) / (links.length - 1);

    // Set as CSS variable on the nav
    nav.style.setProperty("--category-nav-gap", `${gap / 10}rem`);
  };
}
