import BrowserLogger from "js/core/utils/BrowserLogger";
import AjaxHandler from "js/core/utils/AjaxHandler";

const logger = new BrowserLogger("CustomSelect");

export default class CustomSelect {
  constructor(selector, options = {}) {
    this.selector = selector;
    this.options = {
      dataSource: null,
      pageSize: 20,
      enableSearch: true,
      enableInfiniteScroll: true,
      placeholder: "Select an option...",
      emptyMessage: "No options found",
      loadingMessage: "Loading...",
      clearSearchOnOpen: false,
      searchDebounceMs: 300,
      name: null,
      onLoad: null,
      onSelect: null,
      onReset: null,
      responseMapper: null,
      itemMapper: null,
      ...options
    };
    this.staticOptionsLoaded = false;
    this.hasFetchedData = false;
    // DOM Elements
    this.container = null;
    this.button = null;
    this.body = null;
    this.dropdown = null;
    this.searchInput = null;
    this.optionsList = null;
    this.textSpan = null;
    this.hiddenInput = null;
    this.clearButton = null;

    // State
    this.isOpen = false;
    this.isLoading = false;
    this.hasMore = true;
    this.currentPage = 1;
    this.totalItems = 0;
    this.searchTerm = "";
    this.debounceTimer = null;
    this.selectedValue = null;
    this.selectedText = null;
    this.allItems = [];
    this.filteredItems = [];
    this.isLoaded = false;
    this.optionHandlers = new Map();

    this.currentRequestId = 0;
    this.abortController = null;
    this.scrollThrottle = null;

    this.ajaxHandler = new AjaxHandler({
      timeout: 10000,
      headers: { Accept: "application/json" }
    });

    // Bind methods
    this.handleToggle = this.handleToggle.bind(this);
    this.handleOutsideClick = this.handleOutsideClick.bind(this);
    this.handleEscape = this.handleEscape.bind(this);
    this.handleSearch = this.handleSearch.bind(this);
    this.handleScroll = this.handleScroll.bind(this);
    this.handleClear = this.handleClear.bind(this);
  }

  init() {
    logger.debug(`Initializing CustomSelect for "${this.selector}"`);
    console.log(this.selector);
    this.container = document.querySelector(this.selector);
    console.log(this.container);
    if (!this.container) {
      logger.error(`Container not found: ${this.selector}`);
      return false;
    }

    // Find DOM elements
    this.button = this.container.querySelector(".input-field__custom-select");
    this.body = this.container.querySelector(".input-field__body");
    this.dropdown = this.container.querySelector(".input-field__dropdown");
    this.searchInput = this.dropdown?.querySelector(".search-group__input-search");
    this.optionsList = this.dropdown?.querySelector(".option-list");
    this.textSpan = this.button?.querySelector(".text");
    this.hiddenInput = this.container.querySelector(".input-field__hidden-value");

    // Validate critical elements
    if (!this.button || !this.dropdown || !this.optionsList) {
      logger.error(`Required DOM elements not found for ${this.selector}`);
      return false;
    }

    logger.debug(`DOM elements found for ${this.selector}`);

    // Set placeholder
    if (this.textSpan && (!this.textSpan.textContent || this.textSpan.textContent === "")) {
      this.textSpan.textContent = this.options.placeholder;
      this.textSpan.classList.add("placeholder");
    }

    // Create hidden input if not exists
    if (!this.hiddenInput && this.options.name) {
      this.hiddenInput = document.createElement("input");
      this.hiddenInput.type = "hidden";
      this.hiddenInput.className = "input-field__hidden-value";
      this.hiddenInput.name = this.options.name;
      this.container.appendChild(this.hiddenInput);
      logger.debug(`Created hidden input with name: ${this.options.name}`);
    }

    // Setup clear button
    this.setupClearButton();

    // Events
    this.button.addEventListener("click", this.handleToggle);
    document.addEventListener("click", this.handleOutsideClick);
    document.addEventListener("keydown", this.handleEscape);

    if (this.options.enableSearch && this.searchInput) {
      this.searchInput.addEventListener("input", this.handleSearch);
    }

    // IMPORTANT: Do NOT load data on init!
    // Instead, load static options from DOM (server-rendered)
    this.loadStaticOptionsFromDOM();

    // If there are no static options and dataSource exists, mark for lazy loading
    if (this.allItems.length === 0 && this.options.dataSource) {
      logger.debug(`No static options found, will lazy load from dataSource when dropdown opens`);
    } else if (this.allItems.length > 0) {
      logger.debug(`Loaded ${this.allItems.length} static options from DOM (server-rendered)`);
    }

    // Expose instance on DOM element
    this.container.customSelectInstance = this;

    logger.success(`CustomSelect initialized for "${this.selector}"`);
    return true;
  }

  /**
   * Load static options from DOM (server-rendered)
   * This should be the primary data source on page load
   */
  loadStaticOptionsFromDOM() {
    const items = [];
    const options = this.optionsList.querySelectorAll(".option-list__item");

    if (options.length === 0) {
      logger.debug(`No static options found in DOM for ${this.selector}`);
      return;
    }

    logger.debug(`Found ${options.length} static options in DOM`);

    options.forEach((option, index) => {
      // Get existing data attributes
      const value = option.getAttribute("data-value");
      const label = option.textContent;

      // Try to get full product data from data attributes if available
      let itemData = {};
      try {
        const dataJson = option.getAttribute("data-item");
        if (dataJson) {
          itemData = JSON.parse(dataJson);
        }
      } catch (e) {
        // Ignore parse errors
      }

      const item = {
        value: value || label,
        label: label,
        original: { value: value || label, label: label, ...itemData },
        ...itemData
      };

      items.push(item);

      // Replace existing option with clean one (remove old listeners)
      const newOption = option.cloneNode(true);
      option.parentNode.replaceChild(newOption, option);
      newOption.addEventListener("click", (e) => {
        e.stopPropagation();
        this.selectOption(item);
      });
    });

    this.allItems = items;
    this.filteredItems = items;
    this.hasFetchedData = false;
    this.staticOptionsLoaded = true;

    // Don't render yet - wait for dropdown open
    logger.debug(`Loaded ${items.length} static options from DOM`);
  }

  loadStaticOptions() {
    logger.debug(`Loading static options for ${this.selector}`);

    const items = [];
    const options = this.optionsList.querySelectorAll(".option-list__item");

    logger.debug(`Found ${options.length} static options`);

    options.forEach((option, index) => {
      const item = {
        value: option.getAttribute("data-value") || option.textContent,
        label: option.textContent
      };
      items.push(item);

      // Remove existing click listeners to avoid duplicates
      const newOption = option.cloneNode(true);
      option.parentNode.replaceChild(newOption, option);
      newOption.addEventListener("click", (e) => {
        e.stopPropagation();
        this.selectOption(item);
      });
    });

    this.allItems = items;
    this.filteredItems = items;

    if (items.length === 0) {
      this.showEmptyState();
    } else {
      this.renderOptions(this.filteredItems);
    }

    logger.debug(`Loaded ${items.length} static options`);
  }
  /**
   * Lazy load data from server when dropdown opens
   * Only fetches if:
   * 1. There's a dataSource configured
   * 2. No data has been fetched yet
   * 3. There are no static options (or we need more)
   */
  async lazyLoadDataIfNeeded() {
    // Already have data from static options
    if (this.allItems.length > 0 && !this.hasFetchedData) {
      logger.debug(`Using ${this.allItems.length} static options, no fetch needed`);
      this.renderOptions(this.filteredItems);
      return;
    }

    // Already fetched data before
    if (this.hasFetchedData) {
      logger.debug(`Data already fetched, using existing ${this.allItems.length} items`);
      if (this.filteredItems.length === 0 && this.allItems.length === 0) {
        this.showEmptyState();
      } else {
        this.renderOptions(this.filteredItems);
      }
      return;
    }

    // Need to fetch data
    if (this.options.dataSource && !this.hasFetchedData) {
      logger.debug(`Lazy loading data from dataSource...`);
      await this.loadItems(true);
      this.hasFetchedData = true;
    }
  }
  setupClearButton() {
    this.clearButton = this.container.querySelector(".input-field__clear");
    if (!this.clearButton) return;
    this.clearButton.addEventListener("click", this.handleClear);
    if (!this.selectedValue) {
      this.clearButton.style.display = "none";
    }
  }

  handleClear(e) {
    e.stopPropagation();
    this.reset();
  }

  /**
   * Load items from dataSource (AJAX)
   * Now used only for:
   * - Lazy loading when dropdown opens
   * - Search/filtering
   * - Infinite scroll pagination
   */
  async loadItems(reset = true) {
    if (this.isLoading) {
      logger.debug(`Already loading, skipping`);
      return;
    }
    if (!this.options.dataSource) {
      logger.warn(`No dataSource provided`);
      return;
    }

    logger.debug(
      `loadItems called - reset: ${reset}, page: ${this.currentPage}, search: "${this.searchTerm}"`
    );

    // Cancel any in-flight request
    if (this.abortController) {
      this.abortController.abort();
    }

    this.abortController = new AbortController();
    const requestId = ++this.currentRequestId;

    if (reset) {
      this.currentPage = 1;
      this.hasMore = true;
      // Don't clear allItems if we're just searching - merge with existing
      if (this.searchTerm) {
        // Keep existing items, just mark for reset on next fetch
      } else {
        this.allItems = [];
        this.clearOptionsList();
      }
    }

    this.isLoading = true;
    this.showLoading();

    try {
      let items = [];
      let total = 0;

      if (typeof this.options.dataSource === "function") {
        const result = await this.options.dataSource(this.currentPage, this.options.pageSize, {
          signal: this.abortController.signal,
          searchTerm: this.searchTerm
        });

        if (requestId !== this.currentRequestId) return;

        const normalized = this.normalizeResponse(result);
        items = normalized.items;
        total = normalized.total;
        this.hasMore = normalized.hasMore;
      } else if (typeof this.options.dataSource === "string") {
        const params = new URLSearchParams({
          page: this.currentPage,
          limit: this.options.pageSize
        });

        if (this.searchTerm) {
          params.append("search", this.searchTerm);
          params.append("q", this.searchTerm);
        }

        logger.debug(`Fetching from URL: ${this.options.dataSource}?${params}`);

        const response = await this.ajaxHandler.get(this.options.dataSource, params, {
          signal: this.abortController.signal
        });

        if (requestId !== this.currentRequestId) return;

        const normalized = this.normalizeResponse(response);
        items = normalized.items;
        total = normalized.total;
        this.hasMore = normalized.hasMore;
      }

      if (requestId !== this.currentRequestId) return;

      if (reset) {
        this.allItems = [...items];
      } else {
        // For pagination, append items
        this.allItems = [...this.allItems, ...items];
      }

      this.totalItems = total;
      this.applyFilter();

      if (this.options.onLoad) {
        this.options.onLoad(this.allItems);
      }

      logger.debug(`Loaded ${items.length} items. Total: ${this.allItems.length}/${total}`);
    } catch (error) {
      if (error.name === "AbortError") {
        logger.debug("Request cancelled");
        return;
      }
      logger.error("Failed to load items", error);
      this.showError(error.message);
    } finally {
      if (requestId === this.currentRequestId) {
        this.isLoading = false;
        this.hideLoading();
        this.abortController = null;
      }
    }
  }

  normalizeResponse(response) {
    logger.debug(`Normalizing response:`, response);

    // Allow custom mapper if provided
    if (this.options.responseMapper) {
      const mapped = this.options.responseMapper(response);
      return {
        items: this.normalizeItems(mapped.items || mapped),
        total: mapped.total || mapped.items?.length || 0,
        hasMore: mapped.hasMore ?? false
      };
    }

    // Handle direct array
    if (Array.isArray(response)) {
      logger.debug(`Response is direct array with ${response.length} items`);
      return {
        items: this.normalizeItems(response),
        total: response.length,
        hasMore: false
      };
    }

    // Common API response patterns
    let items = null;
    let total = 0;
    let hasMore = false;

    // Try to find items array
    if (response.items && Array.isArray(response.items)) {
      items = response.items;
      total = response.total || items.length;
      hasMore = response.hasMore ?? this.currentPage * this.options.pageSize < total;
      logger.debug(`Found items in response.items (${items.length} items)`);
    } else if (response.data && Array.isArray(response.data)) {
      items = response.data;
      total = response.total || items.length;
      hasMore = response.hasMore ?? this.currentPage * this.options.pageSize < total;
      logger.debug(`Found items in response.data (${items.length} items)`);
    } else if (response.results && Array.isArray(response.results)) {
      items = response.results;
      total = response.totalCount || response.total || items.length;
      hasMore = response.hasMore ?? this.currentPage * this.options.pageSize < total;
      logger.debug(`Found items in response.results (${items.length} items)`);
    } else if (response.records && Array.isArray(response.records)) {
      items = response.records;
      total = response.recordsTotal || response.total || items.length;
      hasMore = response.hasMore ?? this.currentPage * this.options.pageSize < total;
      logger.debug(`Found items in response.records (${items.length} items)`);
    } else {
      // Try to find any array property
      for (const key in response) {
        if (Array.isArray(response[key]) && response[key].length > 0) {
          items = response[key];
          total = response.total || items.length;
          hasMore = false;
          logger.debug(`Found items in response.${key} (${items.length} items)`);
          break;
        }
      }
    }

    if (!items) {
      logger.warn(`Could not find items array in response`, Object.keys(response));
      items = [];
      total = 0;
      hasMore = false;
    }

    return {
      items: this.normalizeItems(items),
      total: total,
      hasMore: hasMore
    };
  }

  normalizeItems(items) {
    if (!items || !Array.isArray(items)) {
      return [];
    }

    return items.map((item) => {
      // Allow custom item mapper
      if (this.options.itemMapper) {
        const mapped = this.options.itemMapper(item);
        return {
          value: mapped.value,
          label: mapped.label,
          original: item,
          ...item,
          ...mapped
        };
      }

      // Auto-detect value field
      let value = null;
      const valueCandidates = [
        "value",
        "id",
        "product_id",
        "order_id",
        "user_id",
        "category_id",
        "uid",
        "key",
        "code"
      ];
      for (const candidate of valueCandidates) {
        if (item[candidate] !== undefined && item[candidate] !== null) {
          value = item[candidate];
          break;
        }
      }

      // Auto-detect label field
      let label = null;
      const labelCandidates = [
        "label",
        "name",
        "title",
        "text",
        "display_name",
        "full_name",
        "description"
      ];
      for (const candidate of labelCandidates) {
        if (item[candidate]) {
          label = item[candidate];
          break;
        }
      }

      // Smart label formatting based on available data
      if (!label) {
        if (item.sku && item.name) {
          label = `${item.name} (${item.sku})`;
        } else if (item.first_name && item.last_name) {
          label = `${item.first_name} ${item.last_name}`;
        } else if (item.order_number) {
          label = `Order #${item.order_number}`;
        } else if (item.invoice_number) {
          label = `Invoice #${item.invoice_number}`;
        } else if (value !== null && value !== undefined) {
          label = String(value);
        } else {
          label = "Unnamed Item";
        }
      }

      const normalized = {
        value: value,
        label: label,
        original: item,
        ...item
      };

      return normalized;
    });
  }

  applyFilter() {
    if (!this.searchTerm.trim()) {
      this.filteredItems = [...this.allItems];
    } else {
      const searchLower = this.searchTerm.toLowerCase();
      this.filteredItems = this.allItems.filter((item) => {
        const text = (item.label || item.name || item.title || "").toLowerCase();
        return text.includes(searchLower);
      });
    }

    logger.debug(
      `Filter applied - ${this.filteredItems.length} items (from ${this.allItems.length} total)`
    );
    this.renderOptions(this.filteredItems);
  }

  renderOptions(items) {
    logger.debug(`Rendering ${items.length} options`);
    this.clearOptionsList();

    if (items.length === 0) {
      this.showEmptyState();
      return;
    }

    // Use DocumentFragment for better performance
    const fragment = document.createDocumentFragment();
    items.forEach((item) => {
      fragment.appendChild(this.createOptionElement(item));
    });
    this.optionsList.appendChild(fragment);

    if (this.options.enableInfiniteScroll && this.hasMore && !this.searchTerm) {
      this.setupInfiniteScroll();
    }
  }

  createOptionElement(item) {
    const li = document.createElement("li");
    li.className = "option-list__item";
    li.setAttribute("data-value", item.value);
    li.textContent = item.label;
    li.itemData = item;

    const handleClick = (e) => {
      e.stopPropagation();
      this.selectOption(li.itemData);
    };

    li.addEventListener("click", handleClick);
    this.optionHandlers.set(li, handleClick);

    return li;
  }

  selectOption(item) {
    logger.debug(`Selecting option:`, item);

    this.selectedValue = item.value;
    this.selectedText = item.label;

    if (this.hiddenInput) {
      this.hiddenInput.value = this.selectedValue;
      this.hiddenInput.dispatchEvent(new Event("change", { bubbles: true }));
    }

    if (this.textSpan) {
      this.textSpan.textContent = this.selectedText;
      this.textSpan.classList.remove("placeholder");
    }

    this.button.classList.add("has-value");
    this.container.classList.add("has-value");
    if (this.clearButton) {
      this.clearButton.style.display = "flex";
    }

    // Dispatch custom event with full item data
    const event = new CustomEvent("select:change", {
      detail: {
        value: this.selectedValue,
        text: this.selectedText,
        item: item
      },
      bubbles: true
    });
    this.container.dispatchEvent(event);

    if (this.options.onSelect) {
      this.options.onSelect(this.selectedValue, this.selectedText, item);
    }

    this.closeDropdown();
  }

  handleSearch(event) {
    clearTimeout(this.debounceTimer);
    this.searchTerm = event.target.value;
    logger.debug(`Search term: "${this.searchTerm}"`);

    // Cancel pending requests
    if (this.abortController) {
      this.abortController.abort();
      this.currentRequestId++;
    }

    this.debounceTimer = setTimeout(() => {
      this.currentPage = 1;
      if (this.options.dataSource && this.searchTerm) {
        // When searching, always fetch from server
        this.loadItems(true);
        this.hasFetchedData = true;
      } else if (this.options.dataSource && !this.searchTerm && this.allItems.length === 0) {
        // No search term and no items - lazy load
        this.lazyLoadDataIfNeeded();
      } else {
        // Filter existing items
        this.applyFilter();
      }
    }, this.options.searchDebounceMs);
  }

  setupInfiniteScroll() {
    this.optionsList.removeEventListener("scroll", this.handleScroll);
    this.optionsList.addEventListener("scroll", this.handleScroll);
  }

  handleScroll() {
    if (this.isLoading || !this.hasMore) return;
    if (this.searchTerm) return;

    if (this.scrollThrottle) return;

    this.scrollThrottle = requestAnimationFrame(() => {
      const { scrollTop, scrollHeight, clientHeight } = this.optionsList;
      const threshold = Math.max(200, clientHeight * 0.3);

      if (scrollTop + clientHeight >= scrollHeight - threshold) {
        if (!this.isLoading && this.hasMore) {
          logger.debug(`Loading more items - page ${this.currentPage + 1}`);
          this.currentPage++;
          this.loadItems(false);
        }
      }

      this.scrollThrottle = null;
    });
  }

  openDropdown() {
    if (this.isOpen) return;

    logger.debug(`Opening dropdown for ${this.selector}`);

    this.body?.classList.add("open");
    this.dropdown?.classList.add("is-open");
    this.isOpen = true;

    // Lazy load data if needed
    this.lazyLoadDataIfNeeded();

    if (this.options.enableSearch && this.searchInput) {
      setTimeout(() => this.searchInput.focus(), 100);
    }
  }

  closeDropdown() {
    if (!this.isOpen) return;

    logger.debug(`Closing dropdown for ${this.selector}`);

    this.body?.classList.remove("open");
    this.dropdown?.classList.remove("is-open");
    this.isOpen = false;
  }

  handleToggle(e) {
    e.preventDefault();
    e.stopPropagation();
    logger.debug(`Toggle clicked - isOpen: ${this.isOpen}`);
    if (this.isOpen) {
      this.closeDropdown();
    } else {
      this.openDropdown();
    }
  }

  handleOutsideClick(e) {
    if (this.isOpen && this.container && !this.container.contains(e.target)) {
      this.closeDropdown();
    }
  }

  handleEscape(e) {
    if (e.key === "Escape" && this.isOpen) {
      this.closeDropdown();
    }
  }

  setValue(value) {
    logger.debug(`Setting value: ${value}`);
    const item = this.allItems.find((i) => i.value == value);
    if (item) {
      this.selectOption(item);
    } else {
      logger.warn(`Value not found: ${value}`);
    }
  }

  reset() {
    logger.debug(`Resetting custom select`);

    this.selectedValue = null;
    this.selectedText = null;

    if (this.hiddenInput) {
      this.hiddenInput.value = "";
    }

    if (this.textSpan) {
      this.textSpan.textContent = this.options.placeholder;
      this.textSpan.classList.add("placeholder");
    }

    this.button.classList.remove("has-value");
    this.container.classList.remove("has-value");
    if (this.clearButton) {
      this.clearButton.style.display = "none";
    }

    this.searchTerm = "";
    if (this.searchInput) {
      this.searchInput.value = "";
    }

    this.applyFilter();
    this.closeDropdown();

    const event = new CustomEvent("select:reset", { bubbles: true });
    this.container.dispatchEvent(event);

    if (this.options.onReset) {
      this.options.onReset();
    }
  }

  clearOptionsList() {
    this.optionHandlers.forEach((handler, item) => {
      item.removeEventListener("click", handler);
    });
    this.optionHandlers.clear();
    this.optionsList.innerHTML = "";
  }

  showLoading() {
    if (this.optionsList.children.length === 0) {
      const li = document.createElement("li");
      li.className = "option-list__item option-list__item--loading";
      li.textContent = this.options.loadingMessage;
      this.optionsList.appendChild(li);
    }
  }

  hideLoading() {
    const loading = this.optionsList.querySelector(".option-list__item--loading");
    if (loading) loading.remove();
  }

  showEmptyState() {
    const li = document.createElement("li");
    li.className = "option-list__item option-list__item--empty";
    li.textContent = this.options.emptyMessage;
    this.optionsList.appendChild(li);
  }

  showError(message) {
    const li = document.createElement("li");
    li.className = "option-list__item option-list__item--error";
    li.textContent = `Error: ${message}`;
    this.optionsList.appendChild(li);
    setTimeout(() => li.remove(), 3000);
  }

  destroy() {
    logger.debug(`Destroying custom select for ${this.selector}`);

    this.button?.removeEventListener("click", this.handleToggle);
    document.removeEventListener("click", this.handleOutsideClick);
    document.removeEventListener("keydown", this.handleEscape);
    this.searchInput?.removeEventListener("input", this.handleSearch);
    this.clearButton?.removeEventListener("click", this.handleClear);

    if (this.abortController) {
      this.abortController.abort();
    }

    clearTimeout(this.debounceTimer);
    if (this.scrollThrottle) {
      cancelAnimationFrame(this.scrollThrottle);
    }

    this.clearOptionsList();

    // Remove instance reference
    if (this.container) {
      delete this.container.customSelectInstance;
    }

    logger.debug(`CustomSelect destroyed for ${this.selector}`);
  }
}
