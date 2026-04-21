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
      onLoad: null,
      onSelect: null,
      onReset: null,
      ...options
    };

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
    this.container = document.querySelector(this.selector);
    if (!this.container) {
      logger.error(`Container not found: ${this.selector}`);
      return;
    }

    // Find elements - updated selectors
    this.button = this.container.querySelector(".input-field__custom-select");
    this.body = this.container.querySelector(".input-field__body");
    this.dropdown = this.container.querySelector(".input-field__dropdown");
    this.searchInput = this.dropdown?.querySelector(".search-group__input-search");
    this.optionsList = this.dropdown?.querySelector(".option-list");
    this.textSpan = this.button?.querySelector(".text");
    this.hiddenInput = this.container.querySelector(".input-field__hidden-value");

    if (!this.button) {
      logger.error("Button not found");
      return;
    }
    if (!this.dropdown) {
      logger.error("Dropdown not found");
      return;
    }
    if (!this.optionsList) {
      logger.error("Options list not found");
      return;
    }

    // Set placeholder
    if (this.textSpan && (!this.textSpan.textContent || this.textSpan.textContent === "")) {
      this.textSpan.textContent = this.options.placeholder;
      this.textSpan.classList.add("placeholder");
    }

    // Create hidden input if not exists
    if (!this.hiddenInput) {
      this.hiddenInput = document.createElement("input");
      this.hiddenInput.type = "hidden";
      this.hiddenInput.className = "input-field__hidden-value";
      this.hiddenInput.name = this.options.name || "select_value";
      this.container.appendChild(this.hiddenInput);
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

    // Load data if dataSource is provided
    if (this.options.dataSource) {
      this.loadItems(true);
    } else if (this.optionsList.children.length > 0) {
      // Use static options from HTML
      this.loadStaticOptions();
    }

    logger.debug(`CustomSelect initialized for "${this.selector}"`);
  }

  loadStaticOptions() {
    const items = [];
    const options = this.optionsList.querySelectorAll(".option-list__item");
    options.forEach((option) => {
      const item = {
        value: option.getAttribute("data-value") || option.textContent,
        label: option.textContent
      };
      items.push(item);
      option.addEventListener("click", (e) => {
        e.stopPropagation();
        this.selectOption(item);
      });
    });
    this.allItems = items;
    this.filteredItems = items;
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

  async loadItems(reset = true) {
    if (this.isLoading) return;
    if (!this.options.dataSource) return;

    if (reset) {
      this.currentPage = 1;
      this.hasMore = true;
      this.allItems = [];
      this.clearOptionsList();
    }

    this.isLoading = true;
    this.showLoading();

    try {
      let items = [];
      let total = 0;

      if (typeof this.options.dataSource === "function") {
        const result = await this.options.dataSource(this.currentPage, this.options.pageSize);
        items = result.items || result;
        total = result.total || items.length;
        this.hasMore = this.currentPage * this.options.pageSize < total;
      } else if (typeof this.options.dataSource === "string") {
        const params = new URLSearchParams({
          page: this.currentPage,
          limit: this.options.pageSize
        });
        const response = await this.ajaxHandler.get(`${this.options.dataSource}?${params}`);
        items = response.items || response.data || [];
        total = response.total || items.length;
        this.hasMore = response.hasMore || this.currentPage * this.options.pageSize < total;
      } else if (Array.isArray(this.options.dataSource)) {
        items = this.options.dataSource;
        total = items.length;
        this.hasMore = false;
      }

      if (reset) {
        this.allItems = [...items];
      } else {
        this.allItems = [...this.allItems, ...items];
      }

      this.totalItems = total;
      this.applyFilter();

      if (this.options.onLoad) {
        this.options.onLoad(this.allItems);
      }

      logger.debug(`Loaded ${items.length} items. Total: ${this.allItems.length}/${total}`);
    } catch (error) {
      logger.error("Failed to load items", error);
      this.showError(error.message);
    } finally {
      this.isLoading = false;
      this.hideLoading();
    }
  }

  applyFilter() {
    if (!this.searchTerm.trim()) {
      this.filteredItems = [...this.allItems];
    } else {
      const searchLower = this.searchTerm.toLowerCase();
      this.filteredItems = this.allItems.filter((item) => {
        const text = (item.label || item.name || item.text || "").toLowerCase();
        return text.includes(searchLower);
      });
    }
    this.renderOptions(this.filteredItems);
  }

  renderOptions(items) {
    this.clearOptionsList();

    if (items.length === 0) {
      this.showEmptyState();
      return;
    }

    items.forEach((item) => {
      const option = this.createOptionElement(item);
      this.optionsList.appendChild(option);
    });

    if (this.options.enableInfiniteScroll && this.hasMore && !this.searchTerm) {
      this.optionsList.addEventListener("scroll", this.handleScroll);
    }
  }

  createOptionElement(item) {
    const li = document.createElement("li");
    li.className = "option-list__item";
    li.setAttribute("data-value", item.value || item.id);

    const label = item.label || item.name || item.text || String(item.value);
    li.textContent = label;
    li.itemData = { ...item, label };

    const handleClick = (e) => {
      e.stopPropagation();
      this.selectOption(li.itemData);
    };

    li.addEventListener("click", handleClick);
    this.optionHandlers.set(li, handleClick);

    return li;
  }

  selectOption(item) {
    this.selectedValue = item.value || item.id;
    this.selectedText = item.label || item.name || item.text;

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

    const event = new CustomEvent("select:change", {
      detail: { value: this.selectedValue, text: this.selectedText, item },
      bubbles: true
    });
    this.container.dispatchEvent(event);

    if (this.options.onSelect) {
      this.options.onSelect(this.selectedValue, this.selectedText, item);
    }

    this.closeDropdown();
    logger.debug(`Selected: ${this.selectedText} (${this.selectedValue})`);
  }

  handleSearch(event) {
    clearTimeout(this.debounceTimer);
    this.searchTerm = event.target.value;

    this.debounceTimer = setTimeout(() => {
      this.applyFilter();
    }, this.options.searchDebounceMs);
  }

  handleScroll() {
    if (!this.optionsList || this.isLoading || !this.hasMore) return;
    if (this.searchTerm) return;

    const { scrollTop, scrollHeight, clientHeight } = this.optionsList;
    if (scrollTop + clientHeight >= scrollHeight - 100) {
      this.currentPage++;
      this.loadItems(false);
    }
  }

  openDropdown() {
    if (this.isOpen) return;

    this.body.classList.add("open");
    this.dropdown.classList.add("is-open");
    this.isOpen = true;

    if (this.options.enableSearch && this.searchInput) {
      setTimeout(() => this.searchInput.focus(), 100);
    }
  }

  closeDropdown() {
    if (!this.isOpen) return;

    this.body.classList.remove("open");
    this.dropdown.classList.remove("is-open");
    this.isOpen = false;
  }

  handleToggle(e) {
    e.preventDefault();
    e.stopPropagation();
    logger.debug(`handleToggle called, isOpen: ${this.isOpen}`);
    if (this.isOpen) {
      this.closeDropdown();
    } else {
      this.openDropdown();
    }
  }

  handleOutsideClick(e) {
    if (this.isOpen && !this.container.contains(e.target)) {
      this.closeDropdown();
    }
  }

  handleEscape(e) {
    if (e.key === "Escape" && this.isOpen) {
      this.closeDropdown();
    }
  }

  setValue(value) {
    const item = this.allItems.find((i) => (i.value || i.id) == value);
    if (item) {
      this.selectOption(item);
    }
  }

  reset() {
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
    li.textContent = message;
    this.optionsList.appendChild(li);
    setTimeout(() => li.remove(), 3000);
  }

  destroy() {
    this.button.removeEventListener("click", this.handleToggle);
    document.removeEventListener("click", this.handleOutsideClick);
    document.removeEventListener("keydown", this.handleEscape);
    if (this.searchInput) {
      this.searchInput.removeEventListener("input", this.handleSearch);
    }
    if (this.clearButton) {
      this.clearButton.removeEventListener("click", this.handleClear);
    }
    this.clearOptionsList();
    logger.debug("CustomSelect destroyed");
  }
}
