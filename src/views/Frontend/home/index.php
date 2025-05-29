<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Custom-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site" class="main">
   <!-- Content -->
   <div class="container">
      <?=$message ?? ''?>
      Here is home index page
   </div>
   <div class="container payments">
      <h3>Amount:</h3>
      <p>$500</p>
      <form action="/payments/pay" method="post">
         <input type="hidden" name="amount" value="500">
         <button class="btn btn-primary">Pay</button>
      </form>
   </div>
   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/Frontend/Home/index') ?>

<?php $this->end();