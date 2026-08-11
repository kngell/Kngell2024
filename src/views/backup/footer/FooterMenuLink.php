  <div class="section-header">
      <h2>Footer Menu Links</h2>
      <div class="filter-group">
          <select id="column-filter" class="filter-select">
              <option value="all">All Columns</option>
              <option value="1">Company</option>
              <option value="2">Resources</option>
              <option value="3">Support</option>
              <option value="4">Legal</option>
              <option value="5">Connect</option>
          </select>
          <button class="btn btn-secondary" data-action="add-link">
              <svg class="icon">
                  <use href="/public/assets/img/icons-sprite.svg#icon-plus"></use>
              </svg>
              Add Link
          </button>
      </div>
  </div>

  <div class="links-container" id="links-container">
      <!-- Company Column Links -->
      <div class="column-group" data-column-id="1">
          <div class="column-group-header">
              <h3>Company</h3>
              <span class="link-count">4 links</span>
          </div>
          <div class="sortable-list" data-column="1">
              <div class="list-item" data-id="1" data-sort="1">
                  <div class="drag-handle">
                      <svg class="icon">
                          <use href="/public/assets/img/icons-sprite.svg#icon-drag"></use>
                      </svg>
                  </div>
                  <div class="item-info">
                      <strong>About Us</strong>
                      <code>/about</code>
                      <span class="target-badge">_self</span>
                  </div>
                  <div class="item-status">
                      <span class="status-badge active">Active</span>
                  </div>
                  <div class="item-actions">
                      <button class="icon-btn" data-action="edit-link" data-id="1">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
                          </svg>
                      </button>
                      <button class="icon-btn delete" data-action="delete-link" data-id="1">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-trash"></use>
                          </svg>
                      </button>
                  </div>
              </div>
              <div class="list-item" data-id="2" data-sort="2">
                  <div class="drag-handle">
                      <svg class="icon">
                          <use href="/public/assets/img/icons-sprite.svg#icon-drag"></use>
                      </svg>
                  </div>
                  <div class="item-info">
                      <strong>Careers</strong>
                      <code>/careers</code>
                      <span class="target-badge">_self</span>
                  </div>
                  <div class="item-status">
                      <span class="status-badge active">Active</span>
                  </div>
                  <div class="item-actions">
                      <button class="icon-btn" data-action="edit-link" data-id="2">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
                          </svg>
                      </button>
                      <button class="icon-btn delete" data-action="delete-link" data-id="2">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-trash"></use>
                          </svg>
                      </button>
                  </div>
              </div>
              <div class="list-item" data-id="3" data-sort="3">
                  <div class="drag-handle">
                      <svg class="icon">
                          <use href="/public/assets/img/icons-sprite.svg#icon-drag"></use>
                      </svg>
                  </div>
                  <div class="item-info">
                      <strong>Blog</strong>
                      <code>/blog</code>
                      <span class="target-badge">_self</span>
                  </div>
                  <div class="item-status">
                      <span class="status-badge active">Active</span>
                  </div>
                  <div class="item-actions">
                      <button class="icon-btn" data-action="edit-link" data-id="3">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
                          </svg>
                      </button>
                      <button class="icon-btn delete" data-action="delete-link" data-id="3">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-trash"></use>
                          </svg>
                      </button>
                  </div>
              </div>
              <div class="list-item" data-id="4" data-sort="4">
                  <div class="drag-handle">
                      <svg class="icon">
                          <use href="/public/assets/img/icons-sprite.svg#icon-drag"></use>
                      </svg>
                  </div>
                  <div class="item-info">
                      <strong>Partners</strong>
                      <code>/partners</code>
                      <span class="target-badge">_self</span>
                  </div>
                  <div class="item-status">
                      <span class="status-badge inactive">Inactive</span>
                  </div>
                  <div class="item-actions">
                      <button class="icon-btn" data-action="edit-link" data-id="4">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
                          </svg>
                      </button>
                      <button class="icon-btn delete" data-action="delete-link" data-id="4">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-trash"></use>
                          </svg>
                      </button>
                  </div>
              </div>
          </div>
      </div>

      <!-- Resources Column Links -->
      <div class="column-group" data-column-id="2">
          <div class="column-group-header">
              <h3>Resources</h3>
              <span class="link-count">5 links</span>
          </div>
          <div class="sortable-list" data-column="2">
              <div class="list-item" data-id="5" data-sort="1">
                  <div class="drag-handle">
                      <svg class="icon">
                          <use href="/public/assets/img/icons-sprite.svg#icon-drag"></use>
                      </svg>
                  </div>
                  <div class="item-info">
                      <strong>Documentation</strong>
                      <code>/docs</code>
                      <span class="target-badge">_self</span>
                  </div>
                  <div class="item-status">
                      <span class="status-badge active">Active</span>
                  </div>
                  <div class="item-actions">
                      <button class="icon-btn" data-action="edit-link" data-id="5">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
                          </svg>
                      </button>
                      <button class="icon-btn delete" data-action="delete-link" data-id="5">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-trash"></use>
                          </svg>
                      </button>
                  </div>
              </div>
              <div class="list-item" data-id="6" data-sort="2">
                  <div class="drag-handle">
                      <svg class="icon">
                          <use href="/public/assets/img/icons-sprite.svg#icon-drag"></use>
                      </svg>
                  </div>
                  <div class="item-info">
                      <strong>API Reference</strong>
                      <code>/api</code>
                      <span class="target-badge">_self</span>
                  </div>
                  <div class="item-status">
                      <span class="status-badge active">Active</span>
                  </div>
                  <div class="item-actions">
                      <button class="icon-btn" data-action="edit-link" data-id="6">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
                          </svg>
                      </button>
                      <button class="icon-btn delete" data-action="delete-link" data-id="6">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-trash"></use>
                          </svg>
                      </button>
                  </div>
              </div>
              <div class="list-item" data-id="7" data-sort="3">
                  <div class="drag-handle">
                      <svg class="icon">
                          <use href="/public/assets/img/icons-sprite.svg#icon-drag"></use>
                      </svg>
                  </div>
                  <div class="item-info">
                      <strong>Tutorials</strong>
                      <code>/tutorials</code>
                      <span class="target-badge">_self</span>
                  </div>
                  <div class="item-status">
                      <span class="status-badge active">Active</span>
                  </div>
                  <div class="item-actions">
                      <button class="icon-btn" data-action="edit-link" data-id="7">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
                          </svg>
                      </button>
                      <button class="icon-btn delete" data-action="delete-link" data-id="7">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-trash"></use>
                          </svg>
                      </button>
                  </div>
              </div>
              <div class="list-item" data-id="8" data-sort="4">
                  <div class="drag-handle">
                      <svg class="icon">
                          <use href="/public/assets/img/icons-sprite.svg#icon-drag"></use>
                      </svg>
                  </div>
                  <div class="item-info">
                      <strong>Community</strong>
                      <code>/community</code>
                      <span class="target-badge">_blank</span>
                  </div>
                  <div class="item-status">
                      <span class="status-badge active">Active</span>
                  </div>
                  <div class="item-actions">
                      <button class="icon-btn" data-action="edit-link" data-id="8">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
                          </svg>
                      </button>
                      <button class="icon-btn delete" data-action="delete-link" data-id="8">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-trash"></use>
                          </svg>
                      </button>
                  </div>
              </div>
              <div class="list-item" data-id="9" data-sort="5">
                  <div class="drag-handle">
                      <svg class="icon">
                          <use href="/public/assets/img/icons-sprite.svg#icon-drag"></use>
                      </svg>
                  </div>
                  <div class="item-info">
                      <strong>Beta Program</strong>
                      <code>/beta</code>
                      <span class="target-badge">_self</span>
                  </div>
                  <div class="item-status">
                      <span class="status-badge active">Active</span>
                  </div>
                  <div class="date-range">
                      <small>Jun 1 - Aug 31, 2026</small>
                  </div>
                  <div class="item-actions">
                      <button class="icon-btn" data-action="edit-link" data-id="9">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
                          </svg>
                      </button>
                      <button class="icon-btn delete" data-action="delete-link" data-id="9">
                          <svg class="icon">
                              <use href="/public/assets/img/icons-sprite.svg#icon-trash"></use>
                          </svg>
                      </button>
                  </div>
              </div>
          </div>
      </div>
  </div>