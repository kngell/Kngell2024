import BrowserLogger from "js/core/utils/BrowserLogger";

/**
 * Simple FilterManager - Minimal working version
 *
 * @example
 * const filters = new FilterManager('#footer-filters', {
 *   filterSelector: '#column-filter',
 *   itemSelector: '.column-group',
 *   attribute: 'data-column-id',
 *   allValue: 'all',
 *   onFilter: (value) => { console.log('Filtered to:', value); }
 * });
 */
export default class FilterManager {
  constructor(container, options = {}) {
    this.container = typeof container === "string" ? document.querySelector(container) : container;

    if (!this.container) {
      throw new Error("Filter container not found");
    }

    this.options = {
      filterSelector: options.filterSelector || "#column-filter",
      itemSelector: options.itemSelector || ".column-group",
      attribute: options.attribute || "data-column-id",
      allValue: options.allValue || "all",
      onFilter: options.onFilter || null,
      ...options
    };

    this.logger = new BrowserLogger("FilterManager");
    this._initialized = false;
    this._currentValue = null;

    // Bind handler
    this._handleFilterChange = this._handleFilterChange.bind(this);

    // Initialize
    this.init();
  }

  init() {
    if (this._initialized) return this;

    // Find filter element
    this.filterElement = this.container.querySelector(this.options.filterSelector);

    if (!this.filterElement) {
      this.logger.warn(`Filter element "${this.options.filterSelector}" not found`);
      return this;
    }

    // Find items to filter
    this._findItems();

    // Setup event listener
    this.filterElement.removeEventListener("change", this._handleFilterChange);
    this.filterElement.addEventListener("change", this._handleFilterChange);

    // Apply initial filter
    const initialValue = this.filterElement.value;
    if (initialValue) {
      this._applyFilter(initialValue, true);
    }

    this._initialized = true;
    this.logger.debug(`FilterManager initialized with ${this.items.length} items`);

    return this;
  }

  _findItems() {
    // Find items using the selector
    this.items = this.container.querySelectorAll(this.options.itemSelector);

    // If no items found, try to find by attribute
    if (this.items.length === 0 && this.options.attribute) {
      this.items = this.container.querySelectorAll(`[${this.options.attribute}]`);
    }

    // If still no items, try to find by data attribute
    if (this.items.length === 0) {
      const attrName = this.options.attribute.replace("data-", "");
      this.items = this.container.querySelectorAll(`[data-${attrName}]`);
    }
  }

  _handleFilterChange(event) {
    const value = event.target.value;
    this._applyFilter(value);
  }

  _applyFilter(value, silent = false) {
    this._currentValue = value;
    const showAll = value === this.options.allValue || value === "" || value === null;

    this.items.forEach((item) => {
      let itemValue = item.getAttribute(this.options.attribute);

      // If attribute not found, try dataset
      if (!itemValue) {
        const attrName = this.options.attribute.replace("data-", "");
        itemValue = item.dataset[attrName];
      }

      const shouldShow = showAll || itemValue === value;

      // Show/hide using display property
      if (shouldShow) {
        item.style.display = "";
        item.style.visibility = "visible";
        item.removeAttribute("data-filter-hidden");
      } else {
        item.style.display = "none";
        item.style.visibility = "hidden";
        item.setAttribute("data-filter-hidden", "true");
      }
    });

    if (!silent && this.options.onFilter) {
      this.options.onFilter(value);
    }
  }

  getCurrentValue() {
    return this._currentValue || this.filterElement?.value || null;
  }

  setValue(value) {
    if (this.filterElement) {
      this.filterElement.value = value;
      this._applyFilter(value);
    }
    return this;
  }

  refresh() {
    this._findItems();
    const currentValue = this.getCurrentValue();
    if (currentValue) {
      this._applyFilter(currentValue, true);
    }
    return this;
  }

  destroy() {
    if (this.filterElement) {
      this.filterElement.removeEventListener("change", this._handleFilterChange);
    }
    this._initialized = false;
    return this;
  }
}
