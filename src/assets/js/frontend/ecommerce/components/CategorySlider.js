import BrowserLogger from "js/core/utils/BrowserLogger";

export default class CategorySlider {
  constructor(containerSelector = ".category-section") {
    this.logger = new BrowserLogger("CategorySlider");
    this.logger.debug("Initializing...");

    // Find container by selector or get the element directly
    this.container =
      typeof containerSelector === "string"
        ? document.querySelector(containerSelector)
        : containerSelector;

    if (!this.container) {
      this.logger.warn("No container found, trying default selector");
      this.container = document.querySelector(".category-section");
    }

    if (!this.container) {
      this.logger.error("No category section found on page");
      return;
    }

    // Find elements within this container
    this.slider = this.container.querySelector(".category-body");
    this.prevBtn =
      this.container.querySelector(".arrow-left") ||
      this.container.querySelector('[data-slider-direction="prev"]');
    this.nextBtn =
      this.container.querySelector(".arrow-right") ||
      this.container.querySelector('[data-slider-direction="next"]');
    this.cards = this.container.querySelectorAll(".category-body__card");

    if (!this.slider || !this.prevBtn || !this.nextBtn || this.cards.length === 0) {
      this.logger.error("Required elements not found");
      return;
    }

    this.init();
  }

  init() {
    this.logger.debug("Initializing slider functionality");

    // Add click event listeners
    this.prevBtn.addEventListener("click", () => this.scroll("left"));
    this.nextBtn.addEventListener("click", () => this.scroll("right"));

    // Calculate dimensions
    this.calculateCardWidth();

    // Initial button state update
    this.updateButtonStates();

    // Handle window resize with debounce
    this.resizeTimeout = null;
    window.addEventListener("resize", this.handleResize.bind(this));

    // Handle mouse wheel for horizontal scroll
    this.slider.addEventListener("wheel", (e) => {
      e.preventDefault();
      this.slider.scrollLeft += e.deltaY;
    });

    // Setup touch events for mobile
    this.setupTouchEvents();

    this.logger.info("Slider initialized successfully");
  }

  calculateCardWidth() {
    if (this.cards.length === 0) return;

    const firstCard = this.cards[0];
    this.cardWidth = firstCard.offsetWidth;

    // Get gap from slider computed style
    const sliderStyle = window.getComputedStyle(this.slider);
    const gapMatch = sliderStyle.gap.match(/(\d+)px/);
    this.gap = gapMatch ? parseInt(gapMatch[1]) : 32;

    this.logger.debug(`Card width: ${this.cardWidth}px, Gap: ${this.gap}px`);
  }

  scroll(direction) {
    const scrollAmount = this.cardWidth + this.gap;
    const currentScroll = this.slider.scrollLeft;
    const maxScroll = this.slider.scrollWidth - this.slider.clientWidth;

    let newScroll;

    if (direction === "left") {
      newScroll = Math.max(currentScroll - scrollAmount, 0);
    } else {
      newScroll = Math.min(currentScroll + scrollAmount, maxScroll);
    }

    this.slider.scrollTo({
      left: newScroll,
      behavior: "smooth"
    });

    // Update button states after scrolling
    setTimeout(() => this.updateButtonStates(), 300);
  }

  updateButtonStates() {
    const currentScroll = this.slider.scrollLeft;
    const maxScroll = this.slider.scrollWidth - this.slider.clientWidth;

    const isAtStart = currentScroll <= 10;
    const isAtEnd = currentScroll >= maxScroll - 10;

    // Update button disabled state
    this.prevBtn.disabled = isAtStart;
    this.nextBtn.disabled = isAtEnd;

    // Update visual appearance
    this.prevBtn.style.opacity = isAtStart ? "0.5" : "1";
    this.prevBtn.style.cursor = isAtStart ? "not-allowed" : "pointer";

    this.nextBtn.style.opacity = isAtEnd ? "0.5" : "1";
    this.nextBtn.style.cursor = isAtEnd ? "not-allowed" : "pointer";

    // Update ARIA labels
    this.prevBtn.setAttribute(
      "aria-label",
      isAtStart ? "No previous categories" : "Previous categories"
    );
    this.nextBtn.setAttribute("aria-label", isAtEnd ? "No more categories" : "Next categories");
  }

  setupTouchEvents() {
    let touchStartX = 0;
    let isScrolling = false;

    this.slider.addEventListener("touchstart", (e) => {
      touchStartX = e.touches[0].clientX;
      isScrolling = true;
    });

    this.slider.addEventListener("touchmove", (e) => {
      if (!isScrolling) return;

      e.preventDefault();
      const touchX = e.touches[0].clientX;
      const diff = touchStartX - touchX;
      this.slider.scrollLeft += diff;
      touchStartX = touchX;
    });

    this.slider.addEventListener("touchend", () => {
      isScrolling = false;
    });
  }

  handleResize() {
    clearTimeout(this.resizeTimeout);
    this.resizeTimeout = setTimeout(() => {
      this.calculateCardWidth();
      this.updateButtonStates();
    }, 100);
  }

  destroy() {
    // Clean up event listeners
    if (this.prevBtn) this.prevBtn.removeEventListener("click", () => this.scroll("left"));
    if (this.nextBtn) this.nextBtn.removeEventListener("click", () => this.scroll("right"));
    window.removeEventListener("resize", this.handleResize);
    this.logger.info("Slider destroyed");
  }
}
