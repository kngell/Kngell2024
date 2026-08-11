import BrowserLogger from "js/core/utils/BrowserLogger";
import AjaxHandler from "js/core/utils/AjaxHandler";

export default class FooterAboutHeader {
  constructor() {
    this.logger = new BrowserLogger("FooterAboutHeader");
    this.ajax = new AjaxHandler();
    this.init();
  }

  init() {
    const aboutContainer = document.querySelector(".tab-content.footer-page__content--about");
    if (aboutContainer) {
      this._boundHandleAddNewClick = this._handleAddNewClick.bind(this);
      aboutContainer.removeEventListener("click", this._boundHandleAddNewClick);
      aboutContainer.addEventListener("click", this._boundHandleAddNewClick);
      this.logger.info("FooterAboutHeader initialized with event delegation");
    } else {
      this.logger.warn("About container not found");
    }
  }

  _handleAddNewClick(event) {
    const button = event.target.closest("button.add-new[data-action='add-new']");
    if (!button) return;

    event.preventDefault();
    this.handleAddNew(event);
  }

  async handleAddNew(event) {
    const button = event.target.closest("button.add-new[data-action='add-new']");

    if (!button) {
      this.logger.warn("Add New button not found");
      return;
    }

    const actionUrl = button.getAttribute("data-action-url");
    if (!actionUrl) {
      this.logger.error("data-action-url attribute not found on button");
      return;
    }

    try {
      this.logger.info(`Fetching about form from: ${actionUrl}`);
      const response = await this.ajax.get(actionUrl);

      this.logger.debug("Response received:", response);

      if (response && response.mainForm) {
        this.replaceAboutForm(response.mainForm);
      } else {
        this.logger.warn("Unexpected response format - no mainForm property found");
      }
    } catch (error) {
      this.logger.error("Failed to fetch about form:", error);
    }
  }

  replaceAboutForm(htmlContent) {
    const aboutContainer = document.querySelector(".tab-content.footer-page__content--about");

    if (!aboutContainer) {
      this.logger.error("About container not found in DOM");
      return;
    }

    const parser = new DOMParser();
    const doc = parser.parseFromString(htmlContent, "text/html");
    const newForm = doc.getElementById("footer-about-frm-id");

    if (newForm) {
      const existingWrapper = aboutContainer.querySelector(".footer-content__about");

      if (existingWrapper) {
        const newWrapper = document.createElement("div");
        newWrapper.className = "footer-content__about";
        const flashContainer = doc.querySelector(".flash-container");
        if (flashContainer) {
          newWrapper.appendChild(flashContainer.cloneNode(true));
        }
        newWrapper.appendChild(newForm.cloneNode(true));
        aboutContainer.innerHTML = "";
        aboutContainer.appendChild(newWrapper);
      } else {
        const existingForm = aboutContainer.querySelector("#footer-about-frm-id");
        if (existingForm) {
          existingForm.replaceWith(newForm.cloneNode(true));
        } else {
          aboutContainer.appendChild(newForm.cloneNode(true));
        }
      }

      this.logger.info("About form replaced successfully");
    } else {
      this.logger.error("Could not find footer-about-frm-id in response");
    }
  }

  destroy() {
    const aboutContainer = document.querySelector(".tab-content.footer-page__content--about");
    if (aboutContainer && this._boundHandleAddNewClick) {
      aboutContainer.removeEventListener("click", this._boundHandleAddNewClick);
      this._boundHandleAddNewClick = null;
      this.logger.debug("FooterAboutHeader destroyed");
    }
  }
}
