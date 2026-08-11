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
      this.checkInitialActiveState(); // Add this line

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

    const isAlreadyActive = link.classList.contains("active");

    // Improved same page detection
    const isSamePage = this.isSamePage(href);

    if (isSamePage) {
      if (href === "#" || href === "") {
        e.preventDefault();
      } else if (isAlreadyActive) {
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

  isSamePage(href) {
    if (!href || href === "#" || href === "") return true;

    try {
      const currentUrl = new URL(window.location.href);
      const linkUrl = new URL(href, window.location.origin);

      const currentSegments = currentUrl.pathname.split("/").filter((s) => s);
      const linkSegments = linkUrl.pathname.split("/").filter((s) => s);

      let linkIndex = 0;
      let currentIndex = 0;

      while (linkIndex < linkSegments.length && currentIndex < currentSegments.length) {
        const linkSegment = linkSegments[linkIndex];
        const currentSegment = currentSegments[currentIndex];

        if (linkSegment === "**") {
          // ** matches multiple segments (greedy)
          if (linkIndex === linkSegments.length - 1) {
            // Last segment is **, matches all remaining
            return true;
          }

          // Find the next non-wildcard segment in link pattern
          let nextLinkIndex = linkIndex + 1;
          while (nextLinkIndex < linkSegments.length && linkSegments[nextLinkIndex] === "**") {
            nextLinkIndex++;
          }

          if (nextLinkIndex >= linkSegments.length) {
            return true;
          }

          const nextLinkSegment = linkSegments[nextLinkIndex];

          // Find where the next segment appears in current URL
          let found = false;
          for (let i = currentIndex; i < currentSegments.length; i++) {
            if (this.matchesSegment(currentSegments[i], nextLinkSegment)) {
              linkIndex = nextLinkIndex;
              currentIndex = i;
              found = true;
              break;
            }
          }

          if (!found) return false;
          continue;
        }

        if (!this.matchesSegment(currentSegment, linkSegment)) {
          return false;
        }

        linkIndex++;
        currentIndex++;
      }

      // Both should have consumed all segments
      return linkIndex === linkSegments.length && currentIndex === currentSegments.length;
    } catch (e) {
      return href === window.location.pathname || href === window.location.href;
    }
  }

  matchesSegment(currentSegment, patternSegment) {
    if (patternSegment === "*") return true;
    if (patternSegment.startsWith(":")) {
      const isNumeric = /^\d+$/.test(currentSegment);
      const isUUID = /^[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}$/i.test(
        currentSegment
      );
      return isNumeric || isUUID;
    }
    return patternSegment === currentSegment;
  }

  getCurrentBlockType() {
    const pathParts = window.location.pathname.split("/").filter((p) => p);
    // Look for pattern: admin/content-block/{type}/...
    const contentBlockIndex = pathParts.findIndex((p) => p === "content-block");
    if (contentBlockIndex !== -1 && pathParts[contentBlockIndex + 1]) {
      return pathParts[contentBlockIndex + 1];
    }
    return null;
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
    // Clear all active states
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
  checkInitialActiveState() {
    const currentPathname = window.location.pathname;

    document
      .querySelectorAll(".menu-list__item--link, .dropdown-list__item--link")
      .forEach((link) => {
        const href = link.getAttribute("href");
        if (href && this.isSamePage(href)) {
          this.updateActiveState(link);
        }
      });
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
