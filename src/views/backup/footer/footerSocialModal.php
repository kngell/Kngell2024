  <div id="social-modal" class="modal" style="display: none;">
      <div class="modal-content">
          <div class="modal-header">
              <h3 id="social-modal-title">Add Social Link</h3>
              <button class="close-btn">&times;</button>
          </div>
          <form id="social-form">
              <input type="hidden" id="social-id">
              <div class="form-group">
                  <label>Platform *</label>
                  <input type="text" id="social-platform" required class="form-control">
                  <small>Unique identifier (e.g., "facebook", "twitter")</small>
              </div>
              <div class="form-group">
                  <label>Name *</label>
                  <input type="text" id="social-name" required class="form-control">
              </div>
              <div class="form-group">
                  <label>URL *</label>
                  <input type="url" id="social-url" required class="form-control">
              </div>
              <div class="form-group">
                  <label>Icon Path *</label>
                  <input type="text" id="social-icon-path" required class="form-control">
                  <small>Path to icon image (e.g., "/icons/facebook.svg")</small>
              </div>
              <div class="form-group">
                  <label>Icon Class</label>
                  <input type="text" id="social-icon-class" class="form-control">
                  <small>CSS class for icon (e.g., "fab fa-facebook")</small>
              </div>
              <div class="form-group">
                  <label>Sort Order</label>
                  <input type="number" id="social-sort" value="0" class="form-control">
              </div>
              <div class="form-group">
                  <label class="checkbox-label">
                      <input type="checkbox" id="social-active" checked>
                      <span>Active</span>
                  </label>
              </div>
              <div class="form-actions">
                  <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Save</button>
              </div>
          </form>
      </div>
  </div>