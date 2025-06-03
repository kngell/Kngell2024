<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/paypal/main') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
   <div class="mt-5 container">
      <span class="float-end">&nbsp;You are not logged in <a href="/login">login</a> </span>
      <span class="float-end"><a href="/paypal/cart">Your cart (<?= $nbItems ?? 0 ?>)</a> </span>
      <h1><span class="text-info">SUPER</span> <span class="text-danger">SHOP</span></h1>
      <p class="text-success">The best online store</p>
      <hr>
      <div class="row">
         <?= $userCart ?? '' ?>
      </div>
   </div>
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();