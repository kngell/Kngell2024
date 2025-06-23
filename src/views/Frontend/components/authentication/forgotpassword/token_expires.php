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
         <h1>Request Password Reset</h1>
         <!-- Token Expires -->
         <p>Password request link invalid or expired. Please click <a href="/forgot">here</a> to request another one</p>
      </div>
   </div>
   <!-- Fin Content -->
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js() ?>

<?php $this->end();