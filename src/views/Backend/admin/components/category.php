<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/backend/admin/pages/category') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main main" id="main">
    <!-- Content -->

    <section class="category span-all">
        <div class="category__header">
            <div class="category__header-left">
                <h4 class="category__header-left__text">Categpries Manager</h4>
                <nav class="category__header-left__breadcrumbs">
                    <ul class="breadcrumbs-list">
                        <li class="breadcrumbs-list__item">
                            <a href="#" class="breadcrumbs-list__item--link">Dashboard</a>
                        </li>
                        <li class="breadcrumbs-list__item">
                            <a href="#" class="breadcrumbs-list__item--link active">Pages</a>
                        </li>
                        <li class="breadcrumbs-list__item">
                            <a href="#" class="breadcrumbs-list__item--link active">Category</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="category__header-right">
                <button class="btn btn--danger btn--md-compact btn--icon-left">
                    <span class="btn__icon">
                        <svg class="icon cancel" aria-label="Delete" role="img">
                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-trash"></use>
                        </svg>
                    </span>
                    <span class="btn__label">Delete</span>
                </button>
                <button class="btn btn--primary btn--md-compact btn--icon-left">
                    <span class="btn__icon">
                        <svg class="icon plus" aria-label="Plus" role="img">
                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-plus"></use>
                        </svg>
                    </span>
                    <span class="btn__label">Add New</span>
                </button>
            </div>
        </div>
        <?= $categoryForm ?? '' ?>
    </section>
    <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/backend/pages/category-main') ?>

<?php $this->end();