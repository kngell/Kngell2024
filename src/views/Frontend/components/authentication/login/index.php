<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class="container justify-content-center align-items-center w-50">
      <div class="row">
         <a href="/login">Login</a>
         <a href="/signup" id="">Sign Up</a>
         <hr>
         <!-- Login Form -->
         <?=$message ?? '' ?>
         <?=$authForm ?? '' ?>
      </div>
   </div>
   <!-- Fin Content -->
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/components/authentication/index') ?>

<?php $this->end();