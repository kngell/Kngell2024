<!-- About items grid/list -->
<div class="about-items">
    <!-- Individual about items -->
    <div class="about-item" data-id="1">
        <div class="item-content">
            <p class="about-text">We are dedicated to providing innovative solutions...</p>
            <div class="item-meta">
                <span class="status-badge active">Active</span>
                <span>Valid from: 2024-01-01</span>
            </div>
        </div>
        <div class="item-actions">
            <button class="icon-btn" data-action="edit-about" data-id="1" data-modal-type="about">
                <svg class="icon edit">
                    <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
                </svg>
            </button>
            <button class="icon-btn delete" data-action="confirm-delete" data-id="1" data-modal-type="about">
                <svg class="icon trash">
                    <use href="/public/assets/img/icons-sprite.svg#icon-trash"></use>
                </svg>
            </button>
        </div>
    </div>

    <!-- Add button -->
    <button class="btn btn-secondary" data-action="add-about" data-modal-type="about">
        <svg class="icon plus">
            <use href="/public/assets/img/icons-sprite.svg#icon-plus"></use>
        </svg>
        Add About
    </button>
</div>