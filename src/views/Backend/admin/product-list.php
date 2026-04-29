<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/backend/admin/pages/list') ?>
<?= $this->css('css/backend/admin/pages/confirm-deletion') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main main product-list" id="main">
    <!-- Content -->

    <div class="product-list__title">
        <?= $adminMainHeader ?? '' ?>
    </div>
    <?= $headerSearchAndFilter ?? '' ?>

    <div class="product-list__table-wrapper">
        <?= $productTable ?? '' ?>
        <?= $productTablePagination ?? '' ?>
    </div>
    <?= $confirmDeletetionModal ?? '' ?>
    <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/backend/pages/products-list-main') ?>

<?php $this->end();