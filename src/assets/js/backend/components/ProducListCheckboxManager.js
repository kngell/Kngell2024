export default class ProductListCheckboxManager {
  constructor(options = {}) {
    this.options = {
      selectAllSelector: "#select-all",
      itemSelector: 'input[name="products[]"]',
      rowSelector: ".table__body--row",
      selectedClass: "row--selected",
      ...options,
    };

    this.selectAll = document.querySelector(this.options.selectAllSelector);
    this.checkboxes = document.querySelectorAll(this.options.itemSelector);
    this.rows = document.querySelectorAll(this.options.rowSelector);

    this.init();
  }

  init() {
    this.bindEvents();
    this.updateSelectAllState();
  }

  bindEvents() {
    // Select all checkbox event
    if (this.selectAll) {
      this.selectAll.addEventListener("change", (e) => {
        this.toggleAllCheckboxes(e.target.checked);
      });
    }

    // Individual checkbox events
    this.checkboxes.forEach((checkbox) => {
      checkbox.addEventListener("change", () => {
        this.updateSelectAllState();
        this.toggleRowSelection(checkbox);
      });
    });
  }

  toggleAllCheckboxes(checked) {
    this.checkboxes.forEach((checkbox) => {
      checkbox.checked = checked;
      this.toggleRowSelection(checkbox);
    });

    // Update aria attributes
    this.updateSelectAllAria(checked);
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

    // Update select all checkbox state
    this.selectAll.checked = checkedCount === totalCount;
    this.selectAll.indeterminate = checkedCount > 0 && checkedCount < totalCount;

    // Update aria attributes
    this.updateSelectAllAria(this.selectAll.checked);
  }

  getCheckedCount() {
    return Array.from(this.checkboxes).filter((checkbox) => checkbox.checked).length;
  }

  updateSelectAllAria(isChecked) {
    if (!this.selectAll) return;

    const checkedCount = this.getCheckedCount();
    const totalCount = this.checkboxes.length;

    if (isChecked) {
      this.selectAll.setAttribute("aria-label", `Deselect all ${totalCount} products`);
    } else if (this.selectAll.indeterminate) {
      this.selectAll.setAttribute(
        "aria-label",
        `${checkedCount} of ${totalCount} products selected. Click to select all`,
      );
    } else {
      this.selectAll.setAttribute("aria-label", `Select all ${totalCount} products`);
    }
  }

  // Public methods for external control
  selectAllProducts() {
    this.toggleAllCheckboxes(true);
  }

  deselectAllProducts() {
    this.toggleAllCheckboxes(false);
  }

  getSelectedProducts() {
    return Array.from(this.checkboxes)
      .filter((checkbox) => checkbox.checked)
      .map((checkbox) => checkbox.value);
  }

  // Destroy method for cleanup
  destroy() {
    if (this.selectAll) {
      this.selectAll.removeEventListener("change", this.toggleAllCheckboxes);
    }

    this.checkboxes.forEach((checkbox) => {
      checkbox.removeEventListener("change", this.updateSelectAllState);
    });
  }
}
