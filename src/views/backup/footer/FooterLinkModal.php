  <div id="link-modal" class="modal" style="display: none;">
      <div class="modal-content">
          <div class="modal-header">
              <h3 id="link-modal-title">Add Link</h3>
              <button class="close-btn">&times;</button>
          </div>
          <form id="link-form">
              <input type="hidden" id="link-id">
              <div class="form-group">
                  <label>Column *</label>
                  <select id="link-column-id" required class="form-control">
                      <option value="1">Company</option>
                      <option value="2">Resources</option>
                      <option value="3">Support</option>
                      <option value="4">Legal</option>
                      <option value="5">Connect</option>
                  </select>
              </div>
              <div class="form-group">
                  <label>Title *</label>
                  <input type="text" id="link-title" required class="form-control">
              </div>
              <div class="form-group">
                  <label>URL *</label>
                  <input type="text" id="link-url" required class="form-control">
              </div>
              <div class="form-group">
                  <label>Target</label>
                  <select id="link-target" class="form-control">
                      <option value="_self">_self (Same window)</option>
                      <option value="_blank">_blank (New window)</option>
                  </select>
              </div>
              <div class="form-group">
                  <label>Sort Order</label>
                  <input type="number" id="link-sort" value="0" class="form-control">
              </div>
              <div class="form-group">
                  <label class="checkbox-label">
                      <input type="checkbox" id="link-active" checked>
                      <span>Active</span>
                  </label>
              </div>
              <div class="form-row">
                  <div class="form-group">
                      <label>Valid From</label>
                      <input type="datetime-local" id="link-valid-from" class="form-control">
                  </div>
                  <div class="form-group">
                      <label>Valid To</label>
                      <input type="datetime-local" id="link-valid-to" class="form-control">
                  </div>
              </div>
              <div class="form-actions">
                  <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Save</button>
              </div>
          </form>
      </div>
  </div>