<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class="container ">
      <div class="row">
         <!-- Account activated -->
         <h1>Sign up</h1>
         <p>Success. Your account is now active. You can now <a href="/login">Login</a> </p>
      </div>
   </div>
   <!-- Fin Content -->
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js() ?>

<?php $this->end();