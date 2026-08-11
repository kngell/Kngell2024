<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?=  $this->css($formAsset ?? []) ?? '' ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main main" id="main">
    <!-- Content -->
    <section class="<?= $formAsset['sectionClass'] ?? ''?> span-all">
        <div class="<?= $formAsset['sectionClass'] ?? ''?>__header">
            <?= $adminMainHeader ?? '' ?>
        </div>
        <?= $mainForm ?? '' ?>
    </section>
    <!-- Fin Content -->
    <?= $confirmDeletionModal ?? ''?>
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js($formAsset['js'] ?? null) ?>

<?php $this->end();