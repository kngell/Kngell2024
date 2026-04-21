<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/backend/admin/modals/confirm-deletion') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main main" id="main">
    <!-- Content -->
    <section class="confirm-deletion span-all">
        <div class="confirm-deletion__header span-all">
            <div class="title">
                <h4 class="title__text">Product Deletion Confirmation</h4>
                <nav class="title__breadcrumbs">
                    <ul class="breadcrumbs-list">
                        <li class="breadcrumbs-list__item">
                            <a href="#" class="breadcrumbs-list__item--link">Dashboard</a>
                        </li>
                        <li class="breadcrumbs-list__item">
                            <a href="#" class="breadcrumbs-list__item--link active">Product List</a>
                        </li>
                        <li class="breadcrumbs-list__item">
                            <a href="#" class="breadcrumbs-list__item--link active">Confirm Deletion</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="user-action">
                <button class="btn btn--outlined btn--md-compact btn--icon-left">
                    <span class="btn__icon">
                        <svg class="icon cancel" aria-label="Cancel" role="img">
                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-close"></use>
                        </svg>
                    </span>
                    <span class="btn__label">Cancel</span>
                </button>
                <button class="btn btn--primary btn--md-compact btn--icon-left">
                    <span class="btn__icon">
                        <svg class="icon save" aria-label="Save" role="img">
                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-check"></use>
                        </svg>
                    </span>
                    <span class="btn__label">Confirm</span>
                </button>
            </div>
        </div>
        <div class="confirm-deletion__body span-all">
            <div class="deletion-card">
                <!-- Header -->
                <div class="deletion-card__title">
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
                <form class="deletion-card__body" mathod="POST" enctype="multipart/form-data" id="product-deletion-frm">
                    <!-- Product summary -->
                    <div class="product-summary">
                        <h4 class="title">Product Summary</h4>
                        <div class="details">
                            <dl class="details__properties">
                                <div class="prop-row">
                                    <dt class="label">name :</dt>
                                    <dd class="value">Premium Wireless Headphones</dd>
                                </div>
                                <div class="prop-row">
                                    <dt class="label">sku :</dt>
                                    <dd class="value"><code>WH-1000XM4</code></dd>
                                </div>
                                <div class="prop-row">
                                    <dt class="label">stock :</dt>
                                    <dd class="value">94 units</dd>
                                </div>
                                <div class="prop-row prop-row--alert">
                                    <dt class="label">status :</dt>
                                    <dd class="value">Currently Live</dd>
                                </div>
                            </dl>

                            <div class="details__image">
                                <img src="../../../assets/img/upload_avatar.png" class="image" alt="Product Image">
                            </div>
                        </div>
                    </div>
                    <!-- Deletion options -->
                    <div class="deletion-options">
                        <h4 class="title">Deletion Options</h4>

                        <fieldset class="options-box">
                            <legend class="sr-only">Choose a deletion method</legend>

                            <div class="input-box">
                                <input type="radio" class="input-box__input" id="delete-soft" name="delete_type"
                                    value="soft" checked>
                                <label class="input-box__label" for="delete-soft">Move to archive (Soft Delete)</label>
                            </div>

                            <div class="input-box">
                                <input type="radio" class="input-box__input" id="delete-hard" name="delete_type"
                                    value="hard">
                                <label class="input-box__label" for="delete-hard">Delete Permanently (Hard
                                    Delete)</label>
                            </div>
                        </fieldset>
                    </div>
                    <!-- Deletion Impact -->
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
                    <div class="input-box span-all">
                        <input type="checkbox" class="input-box__input" id="delete-check" name="delete_type"
                            value="soft" checked>
                        <label class="input-box__label" for="delete-check">I understand this product will be hidden from
                            customers</label>
                    </div>
                </form>
            </div>
        </div>
        <div class="confirm-deletion__footer buttons-group">
            <div class="completeness">
                <span class="completeness__text">
                    Product completion:
                </span>
                <div class="completeness__progress-container">
                    <div class="completeness-progress">
                        <div class="completeness-progress--bar" style="width: 70%;"></div>
                    </div>
                    <span class="completeness-percentage">70%</span>
                </div>

            </div>
            <div class="buttons">
                <button class="btn btn--outlined btn--md-compact btn--icon-left">
                    <span class="btn__icon"><svg class="icon cancel" aria-label="Cancel" role="img">
                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel"></use>
                        </svg></span>
                    <span class="btn__label">Cancel</span>
                </button>
                <button class="btn btn--primary btn--md-compact btn--icon-left" form="product-frm">
                    <span class="btn__icon"><svg class="icon plus" aria-label="Plus" role="img">
                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-trash"></use>
                        </svg></span>
                    <span class="btn__label">Delete Product</span>
                </button>
            </div>
        </div>
    </section>
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();