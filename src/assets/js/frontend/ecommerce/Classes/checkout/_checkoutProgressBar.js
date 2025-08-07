export default class CheckoutProgressBar {
  constructor() {
    this.progressBar = document.querySelector(".progress-bar");
    if (!this.progressBar) return;

    this.steps = Array.from(this.progressBar.querySelectorAll(".progress-step"));
    this.radioInputs = document.querySelectorAll('input[name="step"]');
    this.resizeObserver = null;

    this.init();
  }

  init() {
    setTimeout(() => {
      this.positionConnectors();
      this.setupEventListeners();
    }, 50);
  }

  positionConnectors() {
    if (this.steps.length < 2) return;
    const containerRect = this.progressBar.getBoundingClientRect();

    this.steps.forEach((currentStep, index) => {
      if (index >= this.steps.length - 1) return;

      const nextStep = this.steps[index + 1];
      const connector = currentStep.querySelector(".progress-step__connector");
      if (!connector) return;

      const currentContent = currentStep.querySelector(".progress-step__content");
      const nextContent = nextStep.querySelector(".progress-step__content");
      if (!currentContent || !nextContent) return;

      const currentRect = currentContent.getBoundingClientRect();
      const nextRect = nextContent.getBoundingClientRect();

      connector.style.left = `${currentRect.right - containerRect.left}px`;
      connector.style.width = `${nextRect.left - currentRect.right}px`;
      connector.style.top = "50%";
    });
  }

  setupEventListeners() {
    this.resizeObserver = new ResizeObserver(() => {
      this.positionConnectors();
    });
    this.resizeObserver.observe(this.progressBar);

    this.radioInputs.forEach((radio) => {
      radio.addEventListener("change", () => {
        requestAnimationFrame(() => {
          this.positionConnectors();
        });
      });
    });
  }

  destroy() {
    if (this.resizeObserver) {
      this.resizeObserver.disconnect();
    }
    this.radioInputs.forEach((radio) => {
      radio.removeEventListener("change", this.positionConnectors);
    });
  }
}
