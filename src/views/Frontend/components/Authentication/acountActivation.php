<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Accueil-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<!-- Start Main -->
<main id="main-site">
   <div class="container">
      <div class="row wrapper">
         <div class="col-md-3 account-activation text-center w-100" id="account-activation">
            <!--====== mini-profile Start ======-->
            <?= $userAccountActivation ?? ''?>
            <!--====== mini-profile Ends ======-->
         </div>
         <button id="test_route">test Route</button>

      </div>
      <hr class="my-4">
   </div>
</main>
<!-- End  Main -->
<?php $this->end(); ?>
<?php $this->start('footer')?>
<!-- Custum Page -->
<?= $this->js('path') ?>
<?php $this->end();