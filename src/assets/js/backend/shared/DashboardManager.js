import BrowserLogger from "js/core/utils/logger";

const logger = new BrowserLogger("DashboardManager");

export default class DashboardManager {
  constructor(options = {}) {
    this.debug = options.debug || false;
    this.isInitialized = false;
    // Bind methods once in constructor
    this.handleDropdownClick = this.handleDropdownClick.bind(this);
    this.handleOutsideClick = this.handleOutsideClick.bind(this);
    this.handleLinkClick = this.handleLinkClick.bind(this);
  }

  static async create(options = {}) {
    const instance = new DashboardManager(options);
    await instance.init();
    return instance;
  }

  async init() {
    try {
      this.setup();
      this.addEventListeners();

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
      menuList.classList.add("js-ready");
      menuList.style.opacity = "1";
    }
  }

  addEventListeners() {
    this.setupDropdownToggles();
    this.setupClickTracking();
    this.setupOutsideClick();
  }

  setupDropdownToggles() {
    document.querySelectorAll(".menu-list__item--dropdown-button").forEach((button) => {
      button.removeEventListener("click", this.handleDropdownClick);
      button.addEventListener("click", this.handleDropdownClick);
    });
  }

  setupOutsideClick() {
    document.removeEventListener("click", this.handleOutsideClick);
    document.addEventListener("click", this.handleOutsideClick);
  }

  setupClickTracking() {
    document
      .querySelectorAll(".menu-list__item--link, .dropdown-list__item--link")
      .forEach((link) => {
        link.removeEventListener("click", this.handleLinkClick);
        link.addEventListener("click", this.handleLinkClick);
      });
  }

  handleLinkClick(e) {
    const link = e.currentTarget;
    const text = link.textContent.trim();
    const href = link.getAttribute("href");

    // Prevent reload if already active and pointing to same page or #
    const isAlreadyActive = link.classList.contains("active");
    const isSamePage =
      href === "#" ||
      href === "" ||
      href === window.location.pathname ||
      href === window.location.href;

    if (isSamePage) {
      if (href === "#") {
        e.preventDefault();
      } else if (isAlreadyActive) {
        // If it's already active and we are on the same page, prevent reload loop
        e.preventDefault();
        if (this.debug) {
          logger.debug("Prevented redundant navigation/reload for active link", { text, href });
        }
        return;
      }
    }

    this.trackNavigation(text, href);
    this.updateActiveState(link);

    if (this.debug) {
      logger.debug("Menu navigation:", { text, href });
    }
  }
  handleOutsideClick(e) {
    const menuList = document.querySelector(".menu-list");
    if (!menuList) return;

    const openDropdown = document.querySelector(
      ".menu-list__item.opened, .menu-list__item.has-active-child"
    );
    if (!openDropdown) return;

    const clickedMenuItem = e.target.closest(".menu-list__item");

    if (!clickedMenuItem) {
      if (this.debug) {
        logger.debug("Click outside menu - keeping dropdown open");
      }
      return;
    }

    const isClickOnCurrentDropdownToggle =
      clickedMenuItem === openDropdown && e.target.closest(".menu-list__item--dropdown-button");

    if (isClickOnCurrentDropdownToggle) {
      if (this.debug) {
        logger.debug("Click on current dropdown toggle - letting handleDropdownClick handle it");
      }
      return;
    }

    if (clickedMenuItem !== openDropdown) {
      if (this.debug) {
        logger.debug("Click on different menu item - closing dropdown");
      }
      this.closeAllDropdownsExcept();
    }
  }
  // handleOutsideClick(e) {
  //   const menuList = document.querySelector(".menu-list");
  //   if (menuList && !menuList.contains(e.target)) {
  //     this.closeAllDropdownsExcept();
  //   }
  // }

  handleDropdownClick(e) {
    e.preventDefault();
    e.stopPropagation();

    const button = e.currentTarget;
    const menuItem = button.closest(".menu-list__item");

    if (!menuItem) return;

    // Check if this dropdown is currently open
    const isCurrentlyOpen =
      menuItem.classList.contains("opened") || menuItem.classList.contains("has-active-child");

    // Always close all other dropdowns first
    this.closeAllDropdownsExcept(menuItem);

    if (!isCurrentlyOpen) {
      // If it was closed (or we are forcing open), open it
      menuItem.classList.add("opened");
      button.setAttribute("aria-expanded", "true");

      if (this.debug) {
        logger.debug("Dropdown opened:", button.querySelector("span")?.textContent);
      }
    } else {
      // If it was open, close it
      menuItem.classList.remove("opened");
      menuItem.classList.remove("has-active-child"); // Allow closing active ones too
      button.setAttribute("aria-expanded", "false");

      if (this.debug) {
        logger.debug("Dropdown closed:", button.querySelector("span")?.textContent);
      }
    }
  }

  closeAllDropdownsExcept(exceptMenuItem = null) {
    document.querySelectorAll(".menu-list__item").forEach((item) => {
      if (exceptMenuItem && item === exceptMenuItem) {
        return;
      }

      item.classList.remove("opened");
      item.classList.remove("has-active-child");

      const button = item.querySelector(".menu-list__item--dropdown-button");
      if (button) {
        button.setAttribute("aria-expanded", "false");
      }
    });
  }

  updateActiveState(clickedLink) {
    // Clear all active states and has-active-child classes
    document
      .querySelectorAll(
        ".menu-list__item, .dropdown-list__item, .menu-list__item--link, .dropdown-list__item--link"
      )
      .forEach((el) => {
        el.classList.remove("active");
        if (el.classList.contains("menu-list__item")) {
          el.classList.remove("has-active-child");
        }
      });

    // Set new active state
    clickedLink.classList.add("active");
    const parentLi = clickedLink.closest(".menu-list__item, .dropdown-list__item");
    if (parentLi) {
      parentLi.classList.add("active");

      // If it's a dropdown item, ensure parent has the class
      const parentDropdown = parentLi.closest(".menu-list__item");
      if (parentDropdown && parentLi.classList.contains("dropdown-list__item")) {
        parentDropdown.classList.add("has-active-child");
      }
    }
  }

  refreshRoutePatterns() {
    if (this.debug) {
      logger.debug("refreshRoutePatterns called (placeholder)");
    }
  }

  trackNavigation(text, href) {
    if (typeof gtag !== "undefined") {
      gtag("event", "menu_navigation", {
        event_category: "Navigation",
        event_label: text,
        value: href
      });
    }
  }

  destroy() {
    document.removeEventListener("click", this.handleOutsideClick);
    document.querySelectorAll(".menu-list__item--dropdown-button").forEach((button) => {
      button.removeEventListener("click", this.handleDropdownClick);
    });
    document
      .querySelectorAll(".menu-list__item--link, .dropdown-list__item--link")
      .forEach((link) => {
        link.removeEventListener("click", this.handleLinkClick);
      });
    this.isInitialized = false;
  }
}
