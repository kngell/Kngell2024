<div class="form-card">
    <h2>Footer Settings</h2>

    <div class="settings-group">
        <h3>Cache Management</h3>
        <div class="form-group">
            <button class="btn btn-secondary" id="clear-cache-btn">
                <svg class="icon">
                    <use href="/public/assets/img/icons-sprite.svg#icon-refresh"></use>
                </svg>
                Clear Footer Cache
            </button>
            <small>Clear cached footer data to see recent changes immediately</small>
        </div>
    </div>

    <div class="settings-group">
        <h3>Preview</h3>
        <div class="form-group">
            <button class="btn btn-secondary" id="preview-btn">
                <svg class="icon">
                    <use href="/public/assets/img/icons-sprite.svg#icon-eye"></use>
                </svg>
                Live Preview
            </button>
            <button class="btn btn-primary" id="publish-btn">
                <svg class="icon">
                    <use href="/public/assets/img/icons-sprite.svg#icon-publish"></use>
                </svg>
                Publish to Production
            </button>
        </div>
    </div>

    <div class="settings-group">
        <h3>Export/Import</h3>
        <div class="form-group">
            <button class="btn btn-secondary" id="export-btn">
                <svg class="icon">
                    <use href="/public/assets/img/icons-sprite.svg#icon-download"></use>
                </svg>
                Export Configuration
            </button>
            <label class="btn btn-secondary" id="import-btn">
                <svg class="icon">
                    <use href="/public/assets/img/icons-sprite.svg#icon-upload"></use>
                </svg>
                Import Configuration
                <input type="file" id="import-file-input" accept=".json" style="display: none;">
            </label>
        </div>
    </div>
</div>