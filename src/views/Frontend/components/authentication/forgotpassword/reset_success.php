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
         <h1>Password reset successfully</h1>
         <!-- Password Success -->
         <p>You can now <a href="/login">Login</a> or <a href="/">continue</a></p>
      </div>
   </div>
   <!-- Fin Content -->
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js() ?>

<?php $this->end();