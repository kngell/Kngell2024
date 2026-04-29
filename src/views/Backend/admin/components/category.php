<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/backend/admin/pages/category') ?>
<?= $this->css('css/backend/admin/pages/confirm-deletion') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main main" id="main">
    <!-- Content -->
    <section class="category span-all">
        <?= $categoryHeader ?? '' ?>
        <?= $categoryForm ?? '' ?>
    </section>
    <!-- Fin Content -->
    <?= $confirmDeletionModal ?? ''?>
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/backend/pages/category-main') ?>

<?php $this->end();