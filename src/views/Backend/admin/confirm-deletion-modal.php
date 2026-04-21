<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/backend/admin/pages/confirm-deletion') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
    <!-- Content -->
    <section class="hero span-all">
        <div class="hero__header">
            <?= $heroHeader ?? '' ?>
        </div>
        <?= $heroSectionForm ?? '' ?>
    </section>
    <div class="modals-container">
        <div class="modal-overlay confirm-deletion-modal active">
            <div class="modal confirm-deletion">
                <button type="button" class="modal-close-btn" aria-label="Close modal" data-modal-close="">
                    <svg class="icon close" aria-label="Close Modal" role="img">
                        <use href="/public/assets/img/icons-sprite.svg#icon-close"></use>
                    </svg>
                </button>
                <!-- Header -->
                <div class="modal-header confirm-deletion__header">
                    <h4 class="title">Delete Confirmation</h4>
                    <span class="content">
                        <div class="content__icon-container">
                            <svg class="icon warning" aria-label="Warning" role="img">
                                <use href="/public/assets/img/icons-sprite.svg#icon-warning"></use>
                            </svg>
                        </div>
                        <p class="content__text"></p>
                    </span>
                </div>
                <!-- Body -->
                <form class="modal-body confirm-deletion__body confirm-deletion-frm" id="confirm-deletion-frm"
                    data-validate="true" data-validation-rules="productDeletionRules" name="confirm-deletion-frm"
                    novalidate="" action="/product-delete/delete" method="POST" enctype="multipart/form-data"
                    data-validator-initialized="true"><input type="hidden" name="csrfToken"
                        value="W02Ser5LnNoMyecx5RZP3x4iqs39IU6oS5FYT5DAUsdjb25maXJtLWRlbGV0aW9uLWZybVFjeW5CSlVrY29uZmlybS1kZWxldGlvbi1mcm0xNzc2NzQyODcy"><input
                        type="hidden" name="frm_name" value="confirm-deletion-frm">
                    <div class="product-summary">
                        <h4 class="title">Product Summary</h4>
                        <div class="details">
                            <div class="details__image-container">
                                <div class="image-container"><svg class="icon image" aria-label="Product thumbnail"
                                        role="img">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-media-image"></use>
                                    </svg></div>
                            </div>
                            <div class="details__text"><span class="product-name"></span><span
                                    class="other-properties">PriceTest411111111111, </span></div>
                        </div>
                    </div>
                    <div class="deletion-options">
                        <h4 class="title">Deletion Options</h4>
                        <div class="options">
                            <div class="options-box selected" data-option="archive" role="button"><input type="radio"
                                    id="delete-option-archive" style="display: none" name="delete_option"
                                    value="archive" checked=""><span class="options-box__title">Archive
                                    Product</span><span class="options-box__description">Hide from storefront; data
                                    remains restorable.</span>
                            </div>
                            <div class="options-box" data-option="permanent" role="button"><input type="radio"
                                    id="delete-option-permanent" style="display: none" name="delete_option"
                                    value="permanent"><span class="options-box__title">Delete Permanently</span><span
                                    class="options-box__description">Remove product entirely from the system</span>
                            </div>
                        </div>
                    </div>
                    <section class="deletion-impact"></section>
                    <div class="input-box span-all"><input type="checkbox" class="input-box__input"
                            id="confirm-deletion-frm_confirmDelete_0" name="confirm_delete"><label
                            class="input-box__label" for="confirm-deletion-frm_confirmDelete_0">I understand this
                            product will be hidden from
                            customers</label></div>
                </form>
                <div class="modal-footer confirm-deletion__footer buttons-group">
                    <div class="buttons"><button class="btn btn--outlined btn--md-compact btn--icon-left"
                            type="button"><span class="btn__icon"><svg class="icon" aria-label="Cancel" role="img">
                                    <use href="/public/assets/img/icons-sprite.svg#icon-cancel"></use>
                                </svg></span><span class="btn__label">Cancel</span></button><button
                            class="btn btn--danger btn--md-compact btn--icon-left" form="confirm-deletion-frm"
                            type="submit"><span class="btn__icon"><svg class="icon" aria-label="Delete Product"
                                    role="img">
                                    <use href="/public/assets/img/icons-sprite.svg#icon-trash"></use>
                                </svg></span><span class="btn__label">Delete Product</span></button></div>
                </div>

            </div>
        </div>
    </div>

    <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();