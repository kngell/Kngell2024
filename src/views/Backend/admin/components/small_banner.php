<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/backend/admin/pages/small-banner-section') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main main" id="main">
    <!-- Content -->
    <div class="small-banner span-all">
        <div class="small-banner__header">
            <div class="title">
                <div class="title-left">
                    <h4 class="title-left__text">Small Banner Manager</h4>
                    <nav class="title-left__breadcrumbs">
                        <ul class="breadcrumbs-list">
                            <li class="breadcrumbs-list__item">
                                <a href="#" class="breadcrumbs-list__item--link">Dashboard</a>
                            </li>
                            <li class="breadcrumbs-list__item">
                                <a href="#" class="breadcrumbs-list__item--link active">Pages</a>
                            </li>
                            <li class="breadcrumbs-list__item">
                                <a href="#" class="breadcrumbs-list__item--link active">Small Banner</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <div class="title-right">
                    <form action="/small-banner-delete/delete" method="POST">
                        <button class="btn btn--danger btn--md-compact btn--icon-left">
                            <span class="btn__icon">
                                <svg class="icon cancel" aria-label="Cancel" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-trash"></use>
                                </svg>
                            </span>
                            <span class="btn__label">Delete</span>
                        </button>
                    </form>
                    <form action="/small-banner-page/add" method="POST">

                        <button class="btn btn--primary btn--md-compact btn--icon-left">
                            <span class="btn__icon">
                                <svg class="icon plus" aria-label="Plus" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-plus"></use>
                                </svg>
                            </span>
                            <span class="btn__label">Add New</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?= $smallBannerForm ?? '' ?>
    </div>
    </div>
    <!-- Fin Content -->
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/backend/pages/small-banner-main') ?>

<?php $this->end();