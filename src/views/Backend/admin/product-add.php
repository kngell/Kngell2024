<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/backend/admin/pages/product') ?>
<?= $this->css('css/backend/admin/pages/confirm-deletion') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main main" id="main">
    <!-- Content -->
    <section class="product span-all">
        <div class="product__header">
            <?= $productMainHeader ?? '' ?>
        </div>
        <?= $productForm ?? '' ?>
    </section>
    <!-- Fin Content -->
    <?= $confirmDeletionModal ?? ''?>
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/backend/pages/products-save-main') ?>

<?php $this->end();