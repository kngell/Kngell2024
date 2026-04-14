import BrowserLogger from "js/core/utils/BrowserLogger";

export default class ToggleSwitch {
  constructor(element) {
    this.element = element;
    this.logger = new BrowserLogger("ToggleSwitch");
    this.checkbox = null;

    this.init();
  }

  init() {
    this.checkbox = this.element.querySelector('input[type="checkbox"]');

    if (!this.checkbox) {
      this.logger.warn("No checkbox found");
      return;
    }

    // Set initial visual state based on input's checked state
    this.updateVisualState();

    // Bind events
    this.bindEvents();

    this.element.setAttribute("data-toggle-initialized", "true");
    this.logger.debug("Initialized", {
      name: this.checkbox.name,
      checked: this.checkbox.checked
    });
  }

  bindEvents() {
    // Listen for checkbox changes
    this.checkbox.addEventListener("change", () => {
      this.updateVisualState();
      this.logger.debug(`Changed: ${this.checkbox.name} = ${this.checkbox.checked}`);
    });

    // When user clicks on the toggle container
    this.element.addEventListener("click", (e) => {
      // If clicking directly on the checkbox, let native behavior handle it
      if (e.target === this.checkbox) {
        return;
      }

      e.preventDefault();

      // Toggle the checkbox state
      const isNowChecked = !this.checkbox.checked;

      // Update both property AND attribute
      this.checkbox.checked = isNowChecked;

      if (isNowChecked) {
        this.checkbox.setAttribute("checked", "checked");
      } else {
        this.checkbox.removeAttribute("checked");
      }

      // Manually trigger change event
      const changeEvent = new Event("change", { bubbles: true });
      this.checkbox.dispatchEvent(changeEvent);
    });
  }

  updateVisualState() {
    // Update container class based on checkbox state
    if (this.checkbox.checked) {
      this.element.classList.add("is-checked");
    } else {
      this.element.classList.remove("is-checked");
    }
  }

  destroy() {
    const newElement = this.element.cloneNode(true);
    this.element.parentNode?.replaceChild(newElement, this.element);
    this.logger.debug("Destroyed");
  }
}
