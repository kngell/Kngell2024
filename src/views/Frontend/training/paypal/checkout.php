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
      <span class="float-end"><a href="/paypal/cart">Your cart (4)</a> </span>
      <h1><span class="text-info">SUPER</span> <span class="text-danger">SHOP</span></h1>
      <p class="text-success">The best online store</p>
      <hr>
      <div class="row row-cols-1 row-cols-md-3 text-center">
         <div class="col-md-4 mt-4">
            <div class="row">
               <form method="POST" action="/paypal/create-payment">
                  <input type="image" name="submit_red" alt="Check out with PayPal"
                     src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png">
               </form>
            </div>
         </div>
         <div class="col-md-4 mt-4">
            <h4 class="d-flex justify-content-between align-items-center mb-3">
               <span class="text-muted">Your cart</span>
               <span class="badge badge-secondary badge-pill">4</span>
            </h4>
            <ul class="list-group mb-3">

               <li class="list-group-item d-flex justify-content-between lh-condensed">
                  <div>
                     <h6 class="my-0">Smartphone by Apple</h6>
                     <small class="text-muted">Amount: 1</small>
                  </div>
                  <span class="text-muted">$250.45</span>
               </li>
               <li class="list-group-item d-flex justify-content-between lh-condensed">
                  <div>
                     <h6 class="my-0">Watch by Rolex</h6>
                     <small class="text-muted">Amount: 1</small>
                  </div>
                  <span class="text-muted">$1450.55</span>
               </li>
               <li class="list-group-item d-flex justify-content-between lh-condensed">
                  <div>
                     <h6 class="my-0">TV by Panasonic</h6>
                     <small class="text-muted">Amount: 2</small>
                  </div>
                  <span class="text-muted">$600</span>
               </li>
               <li class="list-group-item d-flex justify-content-between">
                  <span>Total (USD)</span>
                  <strong>$2901</strong>
               </li>
            </ul>
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