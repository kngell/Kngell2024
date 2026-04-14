<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/backend/admin/pages/product') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main main" id="main">
    <!-- Content -->
    <div class="product span-all">
        <div class="product__header">
            <div class="title span-all">
                <div class="title-left">
                    <h4 class="title-left__text">Add Product</h4>
                    <nav class="title-left__breadcrumbs">
                        <ul class="breadcrumbs-list">
                            <li class="breadcrumbs-list__item">
                                <a href="#" class="breadcrumbs-list__item--link">Dashboard</a>
                            </li>
                            <li class="breadcrumbs-list__item">
                                <a href="#" class="breadcrumbs-list__item--link active">Product List</a>
                            </li>
                            <li class="breadcrumbs-list__item">
                                <a href="#" class="breadcrumbs-list__item--link active">Add Product</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <div class="title-right">
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
                            <svg class="icon plus" aria-label="Plus" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-plus"></use>
                            </svg>
                        </span>
                        <span class="btn__label">Add Product</span>
                    </button>
                </div>

            </div>

        </div>
        <?= $product_form ?? ''?>
    </div>
    <!-- Fin Content -->
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/backend/pages/products-save-main') ?>

<?php $this->end();