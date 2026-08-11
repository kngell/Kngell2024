<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/backend/admin/pages/confirm-deletion') ?>
<?= $this->css('css/backend/admin/pages/footer-page') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main main" id="main">
    <!-- Content -->
    <div class="footer-page span-all">
        <!-- Header -->
        <div class="footer-page__header">
            <div class="title">
                <div class="title-left">
                    <h4 class="title-left__text">Footer Page Manager</h4>
                    <nav class="title-left__breadcrumbs">
                        <ul class="breadcrumbs-list">
                            <li class="breadcrumbs-list__item">
                                <a href="#" class="breadcrumbs-list__item--link">Dashboard</a>
                            </li>
                            <li class="breadcrumbs-list__item">
                                <a href="#" class="breadcrumbs-list__item--link active">Pages</a>
                            </li>
                            <li class="breadcrumbs-list__item">
                                <a href="#" class="breadcrumbs-list__item--link active">Footer Manager</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <!-- <div class="title-right">
                    <button type="submit" form="about-form" class="btn btn--primary btn--md-compact btn--icon-left">
                        <span class="btn__icon">
                            <svg class="icon plus" aria-label="Save" role="img">
                                <use href="/public/assets/img/icons-sprite.svg#icon-save"></use>
                            </svg>
                        </span>
                        <span class="btn__label">Save Changes</span>
                    </button>
                </div> -->
            </div>
        </div>
        <?= $entityTable ?? '' ?>
    </div>
    <?= $pagination ?? '' ?>

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/backend/pages/footer-main') ?>

<noscript>
    <div class="no-js-message"
        style="position: fixed; bottom: 1rem; left: 1rem; background: #fef3c7; color: #d97706; padding: 0.75rem 1rem; border-radius: 0.5rem; font-size: 0.875rem;">
        ⚠️ JavaScript is disabled. Some features like drag & drop and modal dialogs may not work. All core functionality
        still works via traditional form submissions.
    </div>
</noscript>

<?php $this->end();