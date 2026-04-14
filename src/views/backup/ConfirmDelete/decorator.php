<!-- Deletion Subtitle -->

<p class="content__text">This action will remove the product from your storefront.</p>

<!-- Confirm Deletion Form -->

<form class="modal-body confirm-deletion__body confirm-frm" mathod="POST" enctype="multipart/form-data"
    id="product-deletion-frm">
    <!-- Product summary -->
    <?= $productSummary ?? '' ?>
    <!-- Deletion options -->
    <div class="deletion-options">
        <h4 class="title">Deletion Options</h4>
        <div class="options">
            <div class="options-box selected">
                <span class="options-box__title">Archive Product</span>
                <span class="options-box__description">Hide from storefront; data remains restorable.</span>
            </div>
            <div class="options-box">
                <span class="options-box__title">Delete Permanently</span>
                <span class="options-box__description">Remove product entirely from the system</span>
            </div>
        </div>
    </div>
    <!-- Deletion Impact -->
    <?= $deletionInpact ?? '' ?>
    <div class="input-box">
        <input type="checkbox" class="input-box__input" id="delete-check" name="delete_type" value="soft" checked>
        <label class="input-box__label" for="delete-check">I understand this product will be hidden from
            customers</label>
    </div>
</form>


<!-- Product Summary -->
<div class="product-summary">
    <h4 class="title">Product Summary</h4>
    <div class="details">
        <div class="details__image-container">
            <img src="../../../assets/img/upload_avatar.png" class="image" alt="Product Image">
        </div>
        <p class="details__text">
            <span class="product-name">Premium Wireless Headphones</span>
            <span class="other-properties">WH-1000XM4, 95 units</span>
        </p>
    </div>
</div>


<!-- Deletion impact -->
<section class="deletion-impact">
    <h4 class="title">Deletion impacts :</h4>
    <ul class="impact-list">
        <li class="impact-list__item impact-item--positive">
            <div class="icon-container">
                <svg class="icon cancel" aria-label="Cancel" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-check"></use>
                </svg>
            </div>
            <p class="text">Product removed from 4 active customer carts</p>
        </li>
        <li class="impact-list__item impact-item--positive">
            <div class="icon-container">
                <svg class="icon cancel" aria-label="Cancel" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-check"></use>
                </svg>
            </div>
            <p class="text"> Product hidden from all storefronts and search</p>
        </li>
        <li class="impact-list__item impact-item--positive">
            <div class="icon-container">
                <svg class="icon cancel" aria-label="Cancel" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-check"></use>
                </svg>
            </div>
            <p class="text">All related media files are archived</p>
        </li>
        <li class="impact-list__item impact-item--info">
            <div class="icon-container">
                <svg class="icon cancel" aria-label="Cancel" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel"></use>
                </svg>
            </div>
            <p class="text"> No data is permanently deleted, the product can be restored later</p>
        </li>
    </ul>
</section>


<div class="modal-overlay confirm-deletion-modal">
    <div class="modal confirm-deletion">
        <!-- Close Button (X) in top-right corner -->
        <button type="button" class="modal-close-btn" aria-label="Close modal" data-modal-close>
            <svg class="icon" role="img" aria-hidden="true">
                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-close"></use>
            </svg>
        </button>

        <!-- Header -->
        <div class="modal-header confirm-deletion__header">
            <h4 class="title">Delete Confirmation</h4>
            <span class="content">
                <div class="content__icon-container">
                    <svg class="icon warning" aria-label="Warning" role="img">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-warning"></use>
                    </svg>
                </div>
                <p class="content__text">This action will remove the product from your storefront.</p>
            </span>
        </div>

        <!-- Body -->
        <form class="modal-body confirm-deletion__body confirm-deletion-frm" id="confirm-deletion-frm"
            action="/product-delete/delete" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrfToken" value="<?= $csrfToken ?>">
            <input type="hidden" name="frm_name" value="confirm-deletion-frm">

            <!-- Product summary -->
            <div class="product-summary">
                <h4 class="title">Product Summary</h4>
                <div class="details">
                    <div class="details__image-container">
                        <img src="<?= $productImage ?>" class="image" alt="Product Image">
                    </div>
                    <div class="details__text">
                        <span class="product-name"><?= htmlspecialchars($productName) ?></span>
                        <span class="other-properties"><?= htmlspecialchars($productDetails) ?></span>
                    </div>
                </div>
            </div>

            <!-- Deletion options -->
            <div class="deletion-options">
                <h4 class="title">Deletion Options</h4>
                <div class="options">
                    <div class="options-box selected" data-option="archive">
                        <span class="options-box__title">Archive Product</span>
                        <span class="options-box__description">Hide from storefront; data remains restorable.</span>
                    </div>
                    <div class="options-box" data-option="permanent">
                        <span class="options-box__title">Delete Permanently</span>
                        <span class="options-box__description">Remove product entirely from the system</span>
                    </div>
                </div>
            </div>

            <!-- Deletion Impact -->
            <section class="deletion-impact">
                <h4 class="title">Deletion impacts:</h4>
                <ul class="impact-list">
                    <li class="impact-list__item impact-item--positive">
                        <div class="icon-container">
                            <svg class="icon" aria-label="Check" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-check"></use>
                            </svg>
                        </div>
                        <p class="text">Product removed from <?= $cartCount ?> active customer carts</p>
                    </li>
                    <li class="impact-list__item impact-item--positive">
                        <div class="icon-container">
                            <svg class="icon" aria-label="Check" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-check"></use>
                            </svg>
                        </div>
                        <p class="text">Product hidden from all storefronts and search</p>
                    </li>
                    <li class="impact-list__item impact-item--positive">
                        <div class="icon-container">
                            <svg class="icon" aria-label="Check" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-check"></use>
                            </svg>
                        </div>
                        <p class="text">All related media files are archived</p>
                    </li>
                    <li class="impact-list__item impact-item--info">
                        <div class="icon-container">
                            <svg class="icon" aria-label="Info" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-info"></use>
                            </svg>
                        </div>
                        <p class="text">No data is permanently deleted, the product can be restored later</p>
                    </li>
                </ul>
            </section>

            <!-- Confirmation checkbox -->
            <div class="input-box">
                <input type="checkbox" class="input-box__input" id="confirm-deletion-check" name="confirm_deletion"
                    value="1" required>
                <label class="input-box__label" for="confirm-deletion-check">
                    I understand this product will be hidden from customers
                </label>
            </div>
        </form>

        <!-- Footer -->
        <div class="modal-footer confirm-deletion__footer">
            <div class="buttons">
                <!-- Cancel button -->
                <button type="button" class="btn btn--outlined btn--md-compact btn--icon-left"
                    data-action="cancel-deletion">
                    <span class="btn__icon">
                        <svg class="icon cancel" aria-label="Cancel" role="img">
                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel"></use>
                        </svg>
                    </span>
                    <span class="btn__label">Cancel</span>
                </button>

                <!-- Delete button (initially disabled until checkbox is checked) -->
                <button type="submit" class="btn btn--danger btn--md-compact btn--icon-left" form="confirm-deletion-frm"
                    disabled>
                    <span class="btn__icon">
                        <svg class="icon delete" aria-label="Delete" role="img">
                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-trash"></use>
                        </svg>
                    </span>
                    <span class="btn__label">Delete Product</span>
                </button>
            </div>
        </div>
    </div>
</div>