<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/paypal/main') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
   <!-- Content -->
   <div class="mt-5 container">
      <span class="float-end">&nbsp;You are not logged in <a href="/login">login</a> </span>
      <span class="float-end"><a href="<?= route('cart.index') ?>">Your cart (<?= $nbItems ?? ''?>)</a> </span>

      <h1><span class="text-info">SUPER</span> <span class="text-danger">SHOP</span></h1>
      <p class="text-success">The best online store</p>
      <hr>
      <div class="row row-cols-1 row-cols-md-3 text-center">
         <div class="card-group">
            <?= $products ?? '' ?>
         </div>

      </div>
   </div>
   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();