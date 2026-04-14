<!-- SINGLE MODE - All states use the same base class with modifiers -->

<!-- 1. EMPTY STATE -->
<div class="upload-single" data-state="empty" data-mode="single">
    <div class="upload-single__icon">
        <svg>
            <use href="#icon-upload"></use>
        </svg>
    </div>
    <div class="upload-single__text">
        <span class="upload-single__main-text">Drag & drop or click to upload</span>
        <span class="upload-single__hint-text">PNG, JPG, GIF • Max 5MB</span>
    </div>
    <input type="file" accept="image/*">
</div>

<!-- 2. UPLOADING STATE -->
<div class="upload-single upload-single--uploading" data-state="uploading" data-mode="single">
    <div class="upload-single__icon">
        <svg>
            <use href="#icon-upload"></use>
        </svg>
    </div>
    <div class="upload-single__text">
        <span class="upload-single__main-text">Uploading: 0%</span>
        <span class="upload-single__hint-text">filename.jpg • 0 MB / 2.4 MB</span>
    </div>
    <div class="upload-single__progress">
        <div class="upload-single__progress-fill" style="width: 0%"></div>
    </div>
    <input type="file" accept="image/*">
</div>

<!-- 3. PREVIEW STATE -->
<div class="upload-single upload-single--preview" data-state="preview" data-mode="single">
    <!-- Preview container with padding -->
    <div class="upload-single__preview-container">
        <div class="upload-single__preview">
            <img src="blob:https://example.com/..." alt="Preview">
        </div>
    </div>

    <!-- Content area -->
    <div class="upload-single__content">
        <div class="upload-single__info">
            <span class="upload-single__filename">filename.jpg</span>
            <span class="upload-single__filesize">2.4 MB</span>
        </div>
        <div class="upload-single__actions">
            <button class="remove">Remove</button>
        </div>
    </div>

    <!-- Hidden file input -->
    <input type="file" accept="image/*" hidden>
</div>

<!-- 1. EMPTY STATE -->
<div class="upload-multiple" data-state="empty" data-mode="multiple">
    <div class="upload-multiple__icon">
        <svg>
            <use href="#icon-upload"></use>
        </svg>
    </div>
    <div class="upload-multiple__text">
        <span class="upload-multiple__main-text">Drag & drop or click to upload</span>
        <span class="upload-multiple__hint-text">PNG, JPG, GIF • Max 5MB each</span>
    </div>
    <input type="file" multiple accept="image/*">
</div>

<!-- 2. UPLOADING STATE -->
<div class="upload-multiple upload-multiple--uploading" data-state="uploading" data-mode="multiple">
    <div class="upload-multiple__icon">
        <svg>
            <use href="#icon-upload"></use>
        </svg>
    </div>
    <div class="upload-multiple__text">
        <span class="upload-multiple__main-text">Uploading 3 files...</span>
        <span class="upload-multiple__hint-text">Total: 7.2 MB</span>
    </div>
    <div class="upload-multiple__progress">
        <div class="upload-multiple__progress-fill" style="width: 45%"></div>
    </div>
    <input type="file" multiple accept="image/*">
</div>

<!-- 3. PREVIEW STATE - With multiple files -->
<div class="upload-multiple upload-multiple--preview" data-state="preview" data-mode="multiple">
    <!-- Preview grid -->
    <div class="upload-multiple__previews-grid">
        <!-- File 1 -->
        <div class="upload-multiple__preview-item" data-index="0">
            <div class="upload-multiple__preview">
                <img src="blob:https://example.com/..." alt="Preview 1">
            </div>
            <div class="upload-multiple__preview-item-actions">
                <button class="remove" data-index="0"></button>
            </div>
        </div>

        <!-- File 2 -->
        <div class="upload-multiple__preview-item" data-index="1">
            <div class="upload-multiple__preview">
                <img src="blob:https://example.com/..." alt="Preview 2">
            </div>
            <div class="upload-multiple__preview-item-actions">
                <button class="remove" data-index="1"></button>
            </div>
        </div>

        <!-- Add more placeholder -->
        <div class="upload-multiple__preview-item add-more-item">
            <div class="upload-multiple__preview add-more">
                <svg>
                    <use href="#icon-plus"></use>
                </svg>
            </div>
        </div>
    </div>

    <!-- Content area -->
    <div class="upload-multiple__content">
        <div class="upload-multiple__info">
            <span class="upload-multiple__main-text">2 files uploaded</span>
            <span class="upload-multiple__hint-text">Click + or drag to add more</span>
        </div>
        <div class="upload-multiple__actions">
            <button class="add-more">Add More</button>
        </div>
    </div>

    <!-- Hidden file input -->
    <input type="file" multiple accept="image/*" hidden>
</div>




<!-- Single Empty State -->
<div class="upload-single" data-state="empty" data-mode="single">
    <div class="upload-single__icon">
        <svg>
            <use href="#icon-upload"></use>
        </svg>
    </div>
    <div class="upload-single__text">
        <span class="upload-single__main-text">Drag & drop or click to upload</span>
        <span class="upload-single__hint-text">PNG, JPG, GIF • Max 5MB</span>
    </div>
    <input type="file" accept="image/*">
</div>

<!-- Single Uploading State -->
<div class="upload-single upload-single--uploading" data-state="uploading" data-mode="single">
    <div class="upload-single__icon">
        <svg>
            <use href="#icon-upload"></use>
        </svg>
    </div>
    <div class="upload-single__text">
        <span class="upload-single__main-text">Uploading: 45%</span>
        <span class="upload-single__hint-text">filename.jpg • 1.2 MB / 2.4 MB</span>
    </div>
    <div class="upload-single__progress">
        <div class="upload-single__progress-fill" style="width: 45%"></div>
    </div>
    <input type="file" accept="image/*">
</div>

<!-- Single Preview State -->
<div class="upload-single upload-single--preview" data-state="preview" data-mode="single">
    <div class="upload-single__preview-container">
        <div class="upload-single__preview">
            <img src="https://example.com/uploads/image.jpg" alt="Preview">
        </div>
    </div>
    <div class="upload-single__content">
        <div class="upload-single__info">
            <span class="upload-single__filename">product-image.jpg</span>
            <span class="upload-single__filesize">2.4 MB</span>
        </div>
        <div class="upload-single__actions">
            <button class="remove">Remove</button>
        </div>
    </div>
    <input type="file" accept="image/*" hidden>
</div>

<!-- Single Error State -->
<div class="upload-single is-error" data-state="error" data-mode="single">
    <div class="upload-single__icon">
        <svg>
            <use href="#icon-upload"></use>
        </svg>
    </div>
    <div class="upload-single__text">
        <span class="upload-single__main-text">Upload failed</span>
        <span class="upload-single__hint-text">File size exceeds 5MB limit</span>
    </div>
    <div class="upload-single__error-message">
        <span>Please try again with a smaller file</span>
    </div>
    <input type="file" accept="image/*">
</div>
<!-- Single Disabled State -->
<div class="upload-single is-disabled" data-state="disabled" data-mode="single" aria-disabled="true">
    <div class="upload-single__icon">
        <svg>
            <use href="#icon-upload"></use>
        </svg>
    </div>
    <div class="upload-single__text">
        <span class="upload-single__main-text">Upload temporarily disabled</span>
        <span class="upload-single__hint-text">Please try again later</span>
    </div>
    <input type="file" accept="image/*" disabled>
</div>
<!-- Single Drag Active State (added via JS) -->
<div class="upload-single is-dragging" data-state="empty" data-mode="single">
    <div class="upload-single__icon">
        <svg>
            <use href="#icon-upload"></use>
        </svg>
    </div>
    <div class="upload-single__text">
        <span class="upload-single__main-text">Drop to upload</span>
        <span class="upload-single__hint-text">Release to upload your file</span>
    </div>
    <input type="file" accept="image/*">
</div>

<!-- MULTIPLES -->
<!-- Multiple Empty State -->
<div class="upload-multiple" data-state="empty" data-mode="multiple">
    <div class="upload-multiple__icon">
        <svg>
            <use href="#icon-upload"></use>
        </svg>
    </div>
    <div class="upload-multiple__text">
        <span class="upload-multiple__main-text">Drag & drop or click to upload</span>
        <span class="upload-multiple__hint-text">PNG, JPG, GIF • Max 5MB each</span>
    </div>
    <input type="file" multiple accept="image/*">
</div>
<!-- Multiple Uploading State -->
<div class="upload-multiple upload-multiple--uploading" data-state="uploading" data-mode="multiple">
    <div class="upload-multiple__icon">
        <svg>
            <use href="#icon-upload"></use>
        </svg>
    </div>
    <div class="upload-multiple__text">
        <span class="upload-multiple__main-text">Uploading 3 files...</span>
        <span class="upload-multiple__hint-text">Total: 7.2 MB • 45% complete</span>
    </div>
    <div class="upload-multiple__progress">
        <div class="upload-multiple__progress-fill" style="width: 45%"></div>
    </div>
    <div class="upload-multiple__file-list" style="display: none;">
        <!-- Hidden file list for tracking -->
    </div>
    <input type="file" multiple accept="image/*">
</div>
<!-- Multiple Preview State - With Files -->
<div class="upload-multiple upload-multiple--preview" data-state="preview" data-mode="multiple">
    <div class="upload-multiple__previews-grid">
        <!-- File 1 -->
        <div class="upload-multiple__preview-item" data-index="0">
            <div class="upload-multiple__preview">
                <img src="https://example.com/uploads/image1.jpg" alt="Preview 1">
            </div>
            <div class="upload-multiple__preview-item-actions">
                <button class="remove" data-index="0" title="Remove">×</button>
            </div>
        </div>
        <!-- File 2 -->
        <div class="upload-multiple__preview-item" data-index="1">
            <div class="upload-multiple__preview">
                <img src="https://example.com/uploads/image2.jpg" alt="Preview 2">
            </div>
            <div class="upload-multiple__preview-item-actions">
                <button class="remove" data-index="1" title="Remove">×</button>
            </div>
        </div>
        <!-- File 3 -->
        <div class="upload-multiple__preview-item" data-index="2">
            <div class="upload-multiple__preview">
                <img src="https://example.com/uploads/image3.jpg" alt="Preview 3">
            </div>
            <div class="upload-multiple__preview-item-actions">
                <button class="remove" data-index="2" title="Remove">×</button>
            </div>
        </div>
        <!-- Add more placeholder -->
        <div class="upload-multiple__preview-item add-more-item">
            <div class="upload-multiple__preview add-more">
                <svg>
                    <use href="#icon-plus"></use>
                </svg>
            </div>
        </div>
    </div>
    <div class="upload-multiple__content">
        <div class="upload-multiple__info">
            <span class="upload-multiple__main-text">3 files uploaded</span>
            <span class="upload-multiple__hint-text">Click + or drag to add more</span>
        </div>
        <div class="upload-multiple__actions">
            <button class="add-more">Add More</button>
        </div>
    </div>
    <input type="file" multiple accept="image/*" hidden>
</div>
<!-- Multiple Preview State - Empty Grid (just add more) -->
<div class="upload-multiple upload-multiple--preview" data-state="preview" data-mode="multiple">
    <div class="upload-multiple__previews-grid">
        <!-- Only add more placeholder when no files -->
        <div class="upload-multiple__preview-item add-more-item">
            <div class="upload-multiple__preview add-more">
                <svg>
                    <use href="#icon-plus"></use>
                </svg>
            </div>
        </div>
    </div>
    <div class="upload-multiple__content">
        <div class="upload-multiple__info">
            <span class="upload-multiple__main-text">No files uploaded</span>
            <span class="upload-multiple__hint-text">Click + or drag to add files</span>
        </div>
        <div class="upload-multiple__actions">
            <button class="add-more">Add Files</button>
        </div>
    </div>
    <input type="file" multiple accept="image/*" hidden>
</div>
<!-- Multiple Error State -->
<div class="upload-multiple is-error" data-state="error" data-mode="multiple">
    <div class="upload-multiple__icon">
        <svg>
            <use href="#icon-upload"></use>
        </svg>
    </div>
    <div class="upload-multiple__text">
        <span class="upload-multiple__main-text">Upload failed</span>
        <span class="upload-multiple__hint-text">Some files exceed the size limit</span>
    </div>
    <div class="upload-multiple__error-message">
        <span>Maximum file size is 5MB per file</span>
    </div>
    <input type="file" multiple accept="image/*">
</div>
<!-- Multiple Disabled State -->
<div class="upload-multiple is-disabled" data-state="disabled" data-mode="multiple" aria-disabled="true">
    <div class="upload-multiple__icon">
        <svg>
            <use href="#icon-upload"></use>
        </svg>
    </div>
    <div class="upload-multiple__text">
        <span class="upload-multiple__main-text">Upload temporarily disabled</span>
        <span class="upload-multiple__hint-text">Please try again later</span>
    </div>
    <input type="file" multiple accept="image/*" disabled>
</div>
<!-- Multiple Drag Active State (added via JS) -->
<div class="upload-multiple is-dragging" data-state="empty" data-mode="multiple">
    <div class="upload-multiple__icon">
        <svg>
            <use href="#icon-upload"></use>
        </svg>
    </div>
    <div class="upload-multiple__text">
        <span class="upload-multiple__main-text">Drop to upload</span>
        <span class="upload-multiple__hint-text">Release to add files to the gallery</span>
    </div>
    <input type="file" multiple accept="image/*">
</div>
<!-- Loading overlay example (used within preview during upload) -->
<div class="upload-single__preview loading">
    <img src="blob:..." alt="Preview">
    <div class="upload-single__loading-overlay">45%</div>
</div>

<!-- Multiple loading overlay example -->
<div class="upload-multiple__preview loading">
    <img src="blob:..." alt="Preview">
    <div class="upload-multiple__loading-overlay">45%</div>
</div>
<!-- Duplicate warning toast (appended to body) -->
<div class="upload-duplicate-warning">
    2 duplicate files were skipped
</div>