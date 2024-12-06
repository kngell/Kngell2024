<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <?= $this->log_file?>
   <?php require_once VIEW . 'client/home/partials/_new_products.php'?>

   <!-- Fin Content -->
   <input type="hidden" id="ipAddress" style="display:none" value="">
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>
<?php $this->end();