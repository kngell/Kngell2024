<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/backend/admin/pages/hero-section') ?>
<?= $this->css('css/backend/admin/pages/confirm-deletion') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main main" id="main">
    <!-- Content -->
    <section class="hero span-all">
        <div class="hero__header">
            <?= $heroHeader ?? '' ?>
        </div>
        <?= $heroSectionForm ?? '' ?>
    </section>
    <!-- Fin Content -->
    <?= $confirmDeletionModal ?? ''?>
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/backend/pages/hero-main') ?>

<?php $this->end();