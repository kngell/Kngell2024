  <div class="pagination">
      <div class="pagination__info">
          Showing <span class="pagination__current">1-10</span> of <span class="pagination__total">50</span>
          products
      </div>

      <nav class="pagination__nav" aria-label="Product pagination">
          <button class="pagination__btn pagination__btn--prev" aria-label="Previous page" disabled>
              <svg class="icon arrow-left" aria-hidden="true">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-left"></use>
              </svg>
          </button>

          <div class="pagination__pages">
              <button class="pagination__page pagination__page--active" aria-label="Page 1"
                  aria-current="page">1</button>
              <button class="pagination__page" aria-label="Page 2">2</button>
              <button class="pagination__page" aria-label="Page 3">3</button>
              <span class="pagination__ellipsis">...</span>
              <button class="pagination__page" aria-label="Page 5">5</button>
          </div>

          <button class="pagination__btn pagination__btn--next" aria-label="Next page">
              <svg class="icon arrow-right" aria-hidden="true">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-right"></use>
              </svg>
          </button>
      </nav>

      <div class="pagination__per-page">
          <label for="per-page" class="pagination__per-page-label">Items per page:</label>
          <select id="per-page" class="pagination__select">
              <option value="10">10</option>
              <option value="25">25</option>
              <option value="50">50</option>
          </select>
      </div>
  </div>