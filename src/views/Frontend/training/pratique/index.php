<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/pratique/main') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<!-- Content -->

<header class="header">
   <div class="header__logo-box">
      <img src="../../../../assets/img/training/logo-white.png" alt="Logo" class="header__logo--img">
   </div>
   <div class="header__hero-section">
      <h1 class="heading-1 header__heading-1">
         <span class="header__heading-1--main">Outdoors</span>
         <span class="header__heading-1--sub">is where life happens</span>
      </h1>
      <a href="#" class="btn btn-white btn-white--header btn-animated">discover our tours</a>
   </div>
</header>
<!-- Content -->


<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js() ?>

<?php $this->end();