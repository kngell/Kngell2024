<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/backend/admin/pages/product-list') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main main product-list" id="main">
    <!-- Content -->

    <div class="product-list__title">
        <div class="title">
            <h4 class="title__text">Product</h4>
            <nav class="title__breadcrumbs">
                <ul class="breadcrumbs-list">
                    <li class="breadcrumbs-list__item">
                        <a href="#" class="breadcrumbs-list__item--link">Dashboard</a>
                    </li>
                    <li class="breadcrumbs-list__item">
                        <a href="#" class="breadcrumbs-list__item--link active">Product List</a>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="user-action">
            <button class="btn btn--secondary btn--md-compact btn--icon-left">
                <span class="btn__icon">
                    <svg class="icon export" aria-label="Export" role="img">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-export"></use>
                    </svg>
                </span>
                <span class="btn__label">Export</span>
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
    <div class="product-list__search-and-filter">
        <form class="search-form">
            <button type="submit" class="search-form__btn">
                <svg class="icon search" aria-label="Search" role="img">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-search">
                    </use>
                </svg>
            </button>
            <input type="text" name="search" id="search-form--input-id" class="search-form__input"
                placeholder="Search product. . .">
        </form>
        <div class="right">
            <button class="right__date-picker">
                <span class="icon-container">
                    <svg class="icon calendar" aria-label="Calendar" role="img">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-calendar">
                        </use>
                    </svg>
                </span>
                <span class="icon-text">Select Dates</span>
            </button>
            <button class="right__filter">
                <span class="icon-container">
                    <svg class="icon slider" aria-label="Slider" role="img">
                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-slider">
                        </use>
                    </svg>
                </span>
                <span class="icon-text">Filters</span>
            </button>
        </div>
    </div>
    <div class="product-list__table-wrapper">
        <?= $productTable ?? '' ?>
        <?= $productTablePagination ?? '' ?>
    </div>

    <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/backend/pages/products-list-main') ?>

<?php $this->end();