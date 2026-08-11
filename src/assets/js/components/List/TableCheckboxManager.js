import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("TableCheckboxManager");

export default class TableCheckboxManager {
  constructor(options = {}) {
    this.options = {
      tableElement: null,
      tableSelector: ".table",
      selectAllSelector: "#select-all-select",
      itemSelector: 'input[type="checkbox"][name$="[]"]',
      rowSelector: ".table__body--row",
      selectedClass: "row--selected",
      itemValueAttribute: "data-id",
      entityName: "items",
      onSelectionChange: null,
      ...options
    };

    this.tableElement = null;
    this.selectAll = null;
    this.checkboxes = [];

    // Stored bound handlers — required for symmetric removeEventListener
    this._onSelectAllChange = null;
    this._onCheckboxChange = null;
    this._boundCheckboxes = []; // [{ el, handler }]

    this.init();
  }

  _getTable() {
    if (this.options.tableElement && this.options.tableElement.isConnected) {
      return this.options.tableElement;
    }
    return document.querySelector(this.options.tableSelector);
  }

  init() {
    this.tableElement = this._getTable();

    if (!this.tableElement) {
      logger.warn(`Table not found: ${this.options.tableSelector}`);
      return;
    }

    // Scope select-all to this table when possible. Falls back to document if
    // the select-all lives outside the table (e.g., in a toolbar).
    this.selectAll =
      this.tableElement.querySelector(this.options.selectAllSelector) ||
      document.querySelector(this.options.selectAllSelector);

    this.checkboxes = this.tableElement.querySelectorAll(this.options.itemSelector);

    if (!this.selectAll) {
      logger.warn(`Select all element not found: ${this.options.selectAllSelector}`);
      // Continue without select-all — individual checkboxes still work
    }

    this.bindEvents();
    this.updateSelectAllState();
  }

  bindEvents() {
    // Unbind anything previously bound (defensive — e.g., if init() runs twice)
    this._unbindEvents();

    if (this.selectAll) {
      this._onSelectAllChange = (e) => this.toggleAllCheckboxes(e.target.checked);
      this.selectAll.addEventListener("change", this._onSelectAllChange);
    }

    this._onCheckboxChange = (e) => {
      this.updateSelectAllState();
      this.toggleRowSelection(e.target);
      this.triggerSelectionChange();
    };

    this.checkboxes.forEach((checkbox) => {
      checkbox.addEventListener("change", this._onCheckboxChange);
      this._boundCheckboxes.push({ el: checkbox, handler: this._onCheckboxChange });
    });
  }

  _unbindEvents() {
    if (this.selectAll && this._onSelectAllChange) {
      this.selectAll.removeEventListener("change", this._onSelectAllChange);
    }
    this._onSelectAllChange = null;

    this._boundCheckboxes.forEach(({ el, handler }) => {
      el.removeEventListener("change", handler);
    });
    this._boundCheckboxes = [];
    this._onCheckboxChange = null;
  }

  toggleAllCheckboxes(checked) {
    this.checkboxes.forEach((checkbox) => {
      checkbox.checked = checked;
      this.toggleRowSelection(checkbox);
    });

    this.updateSelectAllAria(checked);
    this.triggerSelectionChange();
  }

  toggleRowSelection(checkbox) {
    const row = checkbox.closest(this.options.rowSelector);
    if (!row) return;

    if (checkbox.checked) {
      row.classList.add(this.options.selectedClass);
    } else {
      row.classList.remove(this.options.selectedClass);
    }
  }

  updateSelectAllState() {
    if (!this.selectAll) return;

    const checkedCount = this.getCheckedCount();
    const totalCount = this.checkboxes.length;

    if (totalCount === 0 || checkedCount === 0) {
      this.selectAll.checked = false;
      this.selectAll.indeterminate = false;
    } else if (checkedCount === totalCount) {
      this.selectAll.checked = true;
      this.selectAll.indeterminate = false;
    } else {
      this.selectAll.checked = false;
      this.selectAll.indeterminate = true;
    }
  }

  getCheckedCount() {
    return Array.from(this.checkboxes).filter((checkbox) => checkbox.checked).length;
  }

  getSelectedItems() {
    return Array.from(this.checkboxes)
      .filter((checkbox) => checkbox.checked)
      .map((checkbox) => {
        const row = checkbox.closest(this.options.rowSelector);
        return row?.getAttribute(this.options.itemValueAttribute);
      })
      .filter((id) => id);
  }

  updateSelectAllAria(isChecked) {
    if (!this.selectAll) return;

    const checkedCount = this.getCheckedCount();
    const totalCount = this.checkboxes.length;
    const entityName = this.options.entityName;

    if (isChecked) {
      this.selectAll.setAttribute("aria-label", `Deselect all ${totalCount} ${entityName}`);
    } else if (this.selectAll.indeterminate) {
      this.selectAll.setAttribute(
        "aria-label",
        `${checkedCount} of ${totalCount} ${entityName} selected. Click to select all`
      );
    } else {
      this.selectAll.setAttribute("aria-label", `Select all ${totalCount} ${entityName}`);
    }
  }

  triggerSelectionChange() {
    if (this.options.onSelectionChange) {
      this.options.onSelectionChange(this.getSelectedItems());
    }
  }

  selectAllItems() {
    this.toggleAllCheckboxes(true);
  }

  deselectAllItems() {
    this.toggleAllCheckboxes(false);
  }

  /**
   * Re-scan the table for checkboxes (e.g., after rows are added/removed)
   * and rebind events to the new set.
   */
  refresh() {
    if (!this.tableElement) return;
    this._unbindEvents();
    this.checkboxes = this.tableElement.querySelectorAll(this.options.itemSelector);
    this.bindEvents();
    this.updateSelectAllState();
  }

  destroy() {
    this._unbindEvents();
    this.tableElement = null;
    this.selectAll = null;
    this.checkboxes = [];
  }
}
