// js/components/List/TableCheckboxManager.js
import BrowserLogger from "js/core/utils/BrowserLogger";

const logger = new BrowserLogger("TableCheckboxManager");

export default class TableCheckboxManager {
  constructor(options = {}) {
    this.options = {
      tableSelector: ".table",
      selectAllSelector: "#select-all-select",
      itemSelector: 'input[name="products[]"]',
      rowSelector: ".table__body--row",
      selectedClass: "row--selected",
      itemValueAttribute: "data-product-id",
      entityName: "items",
      onSelectionChange: null,
      ...options
    };

    this.selectAll = null;
    this.checkboxes = [];

    this.init();
  }

  init() {
    // Find elements
    this.selectAll = document.querySelector(this.options.selectAllSelector);
    this.checkboxes = document.querySelectorAll(this.options.itemSelector);

    if (!this.selectAll) {
      logger.error(`Select all element not found: ${this.options.selectAllSelector}`);
      return;
    }

    this.bindEvents();
    this.updateSelectAllState();
  }

  bindEvents() {
    // Select all checkbox event
    this.selectAll.addEventListener("change", (e) => {
      this.toggleAllCheckboxes(e.target.checked);
    });

    // Individual checkbox events
    this.checkboxes.forEach((checkbox) => {
      checkbox.addEventListener("change", () => {
        this.updateSelectAllState();
        this.toggleRowSelection(checkbox);
        this.triggerSelectionChange();
      });
    });
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
    if (row) {
      if (checkbox.checked) {
        row.classList.add(this.options.selectedClass);
      } else {
        row.classList.remove(this.options.selectedClass);
      }
    }
  }

  updateSelectAllState() {
    if (!this.selectAll) return;

    const checkedCount = this.getCheckedCount();
    const totalCount = this.checkboxes.length;

    if (totalCount === 0) {
      this.selectAll.checked = false;
      this.selectAll.indeterminate = false;
    } else if (checkedCount === 0) {
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

  refresh() {
    this.checkboxes = document.querySelectorAll(this.options.itemSelector);
    this.updateSelectAllState();
  }

  destroy() {
    if (this.selectAll) {
      this.selectAll.removeEventListener("change", this.toggleAllCheckboxes);
    }
    this.checkboxes.forEach((checkbox) => {
      checkbox.removeEventListener("change", this.updateSelectAllState);
    });
  }
}
