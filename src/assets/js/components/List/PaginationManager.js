import BrowserLogger from "js/core/utils/BrowserLogger";

/**
 * Pure client-side pagination manager.
 * Updates pagination display locally without server round trips.
 */
export default class PaginationManager {
  constructor(options = {}) {
    this.logger = new BrowserLogger("PaginationManager");

    this.options = {
      containerSelector: ".pagination",
      totalSelector: ".pagination__total",
      currentSelector: ".pagination__current",
      perPageSelector: "#per-page-selector",
      perPage: 10,
      ...options
    };

    this.container = options.container || document.querySelector(this.options.containerSelector);
    this.currentPage = 1;
    this.perPage = this.options.perPage;
    this.totalItems = 0;

    if (this.container) {
      this._readInitialValues();
    } else {
      this.logger.debug("No pagination container found — manager inert");
    }
  }

  _readInitialValues() {
    // Read total from DOM
    const totalEl = this.container.querySelector(this.options.totalSelector);
    if (totalEl) {
      this.totalItems = parseInt(totalEl.textContent.trim(), 10) || 0;
    }

    // Read current range to determine current page
    const currentEl = this.container.querySelector(this.options.currentSelector);
    if (currentEl) {
      const match = currentEl.textContent.match(/(\d+)\s*-\s*(\d+)/);
      if (match) {
        const start = parseInt(match[1], 10);
        this.currentPage = Math.ceil(start / this.perPage);
      }
    }

    // Read per-page from select if exists
    const perPageSelect = this.container.querySelector(this.options.perPageSelector);
    if (perPageSelect) {
      this.perPage = parseInt(perPageSelect.value, 10);
    }

    this.logger.debug(
      `Initialized: page ${this.currentPage}, perPage ${this.perPage}, total ${this.totalItems}`
    );
  }

  /**
   * Called after a row has been removed from the table (local update only)
   */
  handleRowRemoved(remainingOnPage) {
    if (!this.container) return;

    // Decrement total by 1 (local update)
    const newTotal = Math.max(0, this.totalItems - 1);

    // Get current start position
    const start = (this.currentPage - 1) * this.perPage + 1;

    // Update total display
    this._updateTotalDisplay(newTotal);

    // Update range display
    this._updateRangeDisplay(start, remainingOnPage, newTotal);

    // Store new total
    this.totalItems = newTotal;

    // If page becomes empty but there are other items, we need to load previous page
    if (remainingOnPage === 0 && newTotal > 0 && this.currentPage > 1) {
      this.logger.debug(
        `Page ${this.currentPage} is empty, need to show page ${this.currentPage - 1}`
      );
      // Dispatch event to load previous page (this requires server data)
      this._requestPreviousPage();
    }

    this.logger.debug(`Row removed: new total ${newTotal}, remaining on page ${remainingOnPage}`);
  }

  /**
   * Called after multiple rows are deleted
   */
  handleBulkDeletion(remainingOnPage, deletedCount) {
    if (!this.container) return;

    const newTotal = Math.max(0, this.totalItems - deletedCount);
    const start = (this.currentPage - 1) * this.perPage + 1;

    this._updateTotalDisplay(newTotal);
    this._updateRangeDisplay(start, remainingOnPage, newTotal);

    this.totalItems = newTotal;

    if (remainingOnPage === 0 && newTotal > 0 && this.currentPage > 1) {
      this._requestPreviousPage();
    }
  }

  _updateTotalDisplay(newTotal) {
    const totalEl = this.container.querySelector(this.options.totalSelector);
    if (totalEl) {
      totalEl.textContent = String(newTotal);
    }
  }

  _updateRangeDisplay(start, remainingOnPage, newTotal) {
    const currentEl = this.container.querySelector(this.options.currentSelector);
    if (!currentEl) return;

    if (remainingOnPage <= 0 || newTotal === 0) {
      currentEl.textContent = "0-0";
    } else {
      const newEnd = start + remainingOnPage - 1;
      currentEl.textContent = `${start}-${newEnd}`;
    }
  }

  _requestPreviousPage() {
    // Only request if we're not already loading
    if (this._isLoading) return;

    this._isLoading = true;

    // Dispatch event to load previous page content
    const event = new CustomEvent("pagination:load-page", {
      detail: {
        page: this.currentPage - 1,
        perPage: this.perPage,
        reason: "page_empty"
      }
    });
    document.dispatchEvent(event);

    // Reset loading flag after a delay
    setTimeout(() => {
      this._isLoading = false;
    }, 500);
  }

  /**
   * Update the entire pagination state (used after page load from server)
   * This should only be called when we actually fetch a new page.
   */
  updateFromServer(total, page, perPage) {
    this.totalItems = total;
    this.currentPage = page;
    this.perPage = perPage;

    const start = (page - 1) * perPage + 1;
    const end = Math.min(page * perPage, total);

    // Update total display
    const totalEl = this.container.querySelector(this.options.totalSelector);
    if (totalEl) {
      totalEl.textContent = String(total);
    }

    // Update range display
    const currentEl = this.container.querySelector(this.options.currentSelector);
    if (currentEl) {
      if (total === 0) {
        currentEl.textContent = "0-0";
      } else {
        currentEl.textContent = `${start}-${end}`;
      }
    }

    this.logger.debug(`Updated from server: page ${page}, total ${total}`);
  }

  /**
   * Get current state (for debugging)
   */
  getState() {
    return {
      currentPage: this.currentPage,
      perPage: this.perPage,
      totalItems: this.totalItems,
      start: (this.currentPage - 1) * this.perPage + 1,
      end: Math.min(this.currentPage * this.perPage, this.totalItems)
    };
  }

  destroy() {
    this.container = null;
  }
}
