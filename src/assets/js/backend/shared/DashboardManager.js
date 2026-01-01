import BrowserLogger from "js/utils/logger";

const logger = new BrowserLogger("DashboardManager");

export default class DashboardManager {
  constructor(options = {}) {
    this.debug = options.debug || false;
    this.isInitialized = false;
  }

  static async create(options = {}) {
    const instance = new DashboardManager(options);
    await instance.init();
    return instance;
  }

  async init() {
    try {
      this.setup();

      if (this.debug) {
        logger.debug("DashboardManager initialized");
      }

      this.isInitialized = true;
    } catch (error) {
      console.error("DashboardManager initialization failed:", error);
    }
  }

  setup() {
    const menuList = document.querySelector(".menu-list");
    if (menuList) {
      menuList.classList.add("js-enhanced");
      menuList.style.opacity = "1";
    }

    this.setupDropdownToggles();

    this.setupClickTracking();
  }

  setupDropdownToggles() {
    document.querySelectorAll(".menu-list__item--dropdown-button").forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault();
        const menuItem = button.closest(".menu-list__item");

        if (menuItem.classList.contains("has-active-child")) {
          return;
        }

        const isOpening = !menuItem.classList.contains("opened");

        if (isOpening) {
          this.closeAllDropdownsExcept(menuItem);
        }

        menuItem.classList.toggle("opened");

        button.setAttribute("aria-expanded", isOpening ? "true" : "false");

        const arrow = button.querySelector(".icon.arrow-down");
        if (arrow) {
          arrow.classList.toggle("rotated", isOpening);
        }

        if (this.debug) {
          logger.debug(
            `Dropdown ${isOpening ? "opened" : "closed"}:`,
            button.querySelector("span")?.textContent,
          );
        }
      });
    });
  }

  closeAllDropdownsExcept(exceptMenuItem = null) {
    document.querySelectorAll(".menu-list__item").forEach((item) => {
      if (exceptMenuItem && item === exceptMenuItem) {
        return;
      }

      if (item.classList.contains("has-active-child")) {
        return;
      }

      item.classList.remove("opened");

      const button = item.querySelector(".menu-list__item--dropdown-button");
      if (button) {
        button.setAttribute("aria-expanded", "false");

        const arrow = button.querySelector(".icon.arrow-down");
        if (arrow) {
          arrow.classList.remove("rotated");
        }
      }
    });
  }

  setupClickTracking() {
    document
      .querySelectorAll(".menu-list__item--link, .dropdown-list__item--link")
      .forEach((link) => {
        link.addEventListener("click", (e) => {
          const text = link.textContent.trim();
          const href = link.getAttribute("href");

          if (this.debug) {
            logger.debug("Menu navigation:", { text, href });
          }
        });
      });
  }

  trackNavigation(text, href) {
    if (typeof gtag !== "undefined") {
      gtag("event", "menu_navigation", {
        event_category: "Navigation",
        event_label: text,
        value: href,
      });
    }
  }
}
