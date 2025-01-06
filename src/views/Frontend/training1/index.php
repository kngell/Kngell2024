<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<!-- header -->
<header>
   <div class="header">
      <div class="header__brand">
         <a href="" class="logo">
            <i class="fas fa-utensils"></i>
         </a>
         <div>
            <h1 class="logo__main-name">
               Georgia
            </h1>
            <p class="logo__sub-name">Restaurant</p>
         </div>

      </div>
      <div class="header__banner">
         <h1 class="main-heading">
            Wellcome
         </h1>
         <h3 class="sub-heading">try great geordian dishes</h3>
         <button class="main-btn">Reservation</button>
      </div>
   </div>
</header>
<!-- header -->
<main id="main-site">
   <!-- Content -->

   <!-- Fin Content -->
</main>

<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js() ?>

<?php $this->end();