<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class="container">
      <div class="row">
         <!-- Register Form -->
         <?=$message ?? '' ?>
         <div class="form-container register-frm-box">
            <?=$authForm ?? '' ?>
         </div>
      </div>
   </div>
   <!-- Fin Content -->
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/components/authentication/index') ?>

<?php $this->end();