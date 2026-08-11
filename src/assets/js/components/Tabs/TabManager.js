import BrowserLogger from "js/core/utils/BrowserLogger";

export default class TabManager {
  constructor(container, options = {}) {
    this.container = typeof container === "string" ? document.querySelector(container) : container;

    if (!this.container) {
      throw new Error("Tab container not found");
    }

    this.options = {
      tabButtonSelector: options.tabButtonSelector || ".tab-btn",
      tabContentSelector: options.tabContentSelector || ".tab-content",
      onTabChange: options.onTabChange || null,
      defaultTab: options.defaultTab || null,
      // New option: whether to use URL hash or query params
      useUrlHash: options.useUrlHash || false,
      ...options
    };

    this.logger = new BrowserLogger("TabManager");
    this._currentTab = null;
    this._initialized = false;

    // Bind handler
    this._handleTabClick = this._handleTabClick.bind(this);

    // Initialize
    this.init();
  }

  init() {
    if (this._initialized) return this;

    // Find tabs and contents
    this.tabButtons = this.container.querySelectorAll(this.options.tabButtonSelector);
    this.tabContents = this.container.querySelectorAll(this.options.tabContentSelector);

    if (this.tabButtons.length === 0) {
      this.logger.warn("No tab buttons found");
      return this;
    }

    this.logger.debug(
      `Found ${this.tabButtons.length} tab buttons, ${this.tabContents.length} tab contents`
    );

    // Setup event listeners
    this.tabButtons.forEach((btn) => {
      btn.removeEventListener("click", this._handleTabClick);
      btn.addEventListener("click", this._handleTabClick);
    });

    // Determine initial tab
    let initialTab = this.options.defaultTab;

    // Check URL for tab parameter
    if (!initialTab) {
      const urlParams = new URLSearchParams(window.location.search);
      const tabParam = urlParams.get("tab");
      if (tabParam && this._findButton(tabParam)) {
        initialTab = tabParam;
      }
    }

    // If no initial tab, find first active tab or first tab
    if (!initialTab) {
      const activeBtn = Array.from(this.tabButtons).find((btn) => btn.classList.contains("active"));
      if (activeBtn) {
        initialTab = activeBtn.dataset.tab;
      } else {
        initialTab = this.tabButtons[0]?.dataset.tab;
      }
    }

    if (initialTab) {
      this.activateTab(initialTab, true);
    }

    this._initialized = true;
    this.logger.debug(`TabManager initialized with ${this.tabButtons.length} tabs`);

    return this;
  }

  _handleTabClick(event) {
    const button = event.currentTarget;
    const tabId = button.dataset.tab;
    if (!tabId) return;

    event.preventDefault();
    this.activateTab(tabId);
  }

  activateTab(tabId, silent = false) {
    const button = this._findButton(tabId);

    if (!button) {
      this.logger.error(`Tab "${tabId}" not found among buttons`);
      return false;
    }

    // Find corresponding content
    const content = this._findContent(tabId);

    // Deactivate all tabs
    this.tabButtons.forEach((btn) => btn.classList.remove("active"));
    this.tabContents.forEach((c) => c.classList.remove("active"));

    // Activate selected tab button
    button.classList.add("active");

    // Activate content if found
    if (content) {
      content.classList.add("active");
      this.logger.debug(`Tab "${tabId}" activated with content`);
    } else {
      this.logger.debug(`Tab "${tabId}" activated (no content found)`);
    }

    this._currentTab = tabId;

    // Update URL
    this._updateUrl(tabId);

    if (!silent && this.options.onTabChange) {
      this.options.onTabChange(tabId);
    }

    return true;
  }

  _findButton(tabId) {
    // Try direct match first
    let button = Array.from(this.tabButtons).find((btn) => btn.dataset.tab === tabId);

    // Try without # prefix if it has one
    if (!button && tabId.startsWith("#")) {
      button = Array.from(this.tabButtons).find((btn) => btn.dataset.tab === tabId.substring(1));
    }

    // Try with # prefix if it doesn't have one
    if (!button) {
      button = Array.from(this.tabButtons).find((btn) => btn.dataset.tab === `#${tabId}`);
    }

    return button;
  }

  _findContent(tabId) {
    // Try multiple ways to find content
    let content = null;

    // 1. Try direct ID match
    content = Array.from(this.tabContents).find((c) => c.id === tabId);
    if (content) return content;

    // 2. Try with # prefix
    if (tabId.startsWith("#")) {
      content = Array.from(this.tabContents).find((c) => c.id === tabId.substring(1));
      if (content) return content;
    }

    // 3. Try without # prefix
    if (!tabId.startsWith("#")) {
      content = Array.from(this.tabContents).find((c) => c.id === `#${tabId}`);
      if (content) return content;
    }

    // 4. Try matching by data-tab attribute
    content = Array.from(this.tabContents).find((c) => c.dataset.tab === tabId);
    if (content) return content;

    // 5. Try matching by ID that contains the tab name
    content = Array.from(this.tabContents).find((c) => c.id && c.id.includes(tabId));
    if (content) return content;

    // 6. Try matching by class that contains the tab name
    content = Array.from(this.tabContents).find((c) => {
      return c.className && c.className.includes(tabId);
    });

    if (content) return content;

    // 7. If no content found, check if there's a content element that was created dynamically
    // For tabs like "links" where the content might be inside a div with specific class
    if (tabId === "links") {
      content = document.querySelector(".footer-content__links");
      if (content) return content;
    }

    if (tabId === "columns") {
      content = document.querySelector("#columns-grid");
      if (content) return content;
    }

    if (tabId === "social") {
      content = document.querySelector(".footer-content__social");
      if (content) return content;
    }

    if (tabId === "settings") {
      content = document.querySelector(".footer-content__settings");
      if (content) return content;
    }

    return null;
  }

  /**
   * Update URL with tab parameter
   */
  _updateUrl(tabId) {
    try {
      const url = new URL(window.location);

      if (this.options.useUrlHash) {
        url.hash = tabId;
      } else {
        url.searchParams.set("tab", tabId);
      }

      window.history.pushState({ tab: tabId }, "", url);
    } catch (error) {
      // Silently fail
    }
  }

  getCurrentTab() {
    return this._currentTab;
  }

  refresh() {
    this.tabButtons = this.container.querySelectorAll(this.options.tabButtonSelector);
    this.tabContents = this.container.querySelectorAll(this.options.tabContentSelector);

    this.tabButtons.forEach((btn) => {
      btn.removeEventListener("click", this._handleTabClick);
      btn.addEventListener("click", this._handleTabClick);
    });

    if (this._currentTab) {
      this.activateTab(this._currentTab, true);
    } else if (this.tabButtons.length > 0) {
      this.activateTab(this.tabButtons[0].dataset.tab, true);
    }

    return this;
  }

  destroy() {
    this.tabButtons.forEach((btn) => {
      btn.removeEventListener("click", this._handleTabClick);
    });
    this._initialized = false;
    return this;
  }
}
