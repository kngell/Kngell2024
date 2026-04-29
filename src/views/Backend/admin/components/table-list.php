<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/backend/admin/pages/table-list') ?>
<?= $this->css('css/backend/admin/pages/confirm-deletion') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main main list" id="main">
    <!-- Content -->
    <div class="table-list span-all">
        <div class="table-list__header">
            <div class="table-list__title">
                <?= $adminMainHeader ?? '' ?>
            </div>
            <?= $headerSearchAndFilter ?? '' ?>
        </div>
        <div class="table-list__body">
            <?= $entityTable ?? '' ?>
        </div>
        <div class="table-list__footer">
            <?= $pagination ?? '' ?>
        </div>
    </div>
    <?= $confirmDeletetionModal ?? '' ?>
    <!-- Fin Content -->
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/backend/pages/products-list-main') ?>
<?= $this->js('js/backend/pages/hero-list-main') ?>

<?php $this->end();