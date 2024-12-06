<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class="container mt-4">
      <div class="row py-4 justify-content-center">
         <?=$this->msg?>
      </div>
   </div>

   <!-- Fin Content -->
   <input type="hidden" id="ipAddress" style="display:none" value="">
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>
<?php $this->end();