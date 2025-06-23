<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/pratique/cms') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<div class="container">
   <header class="header">
      <h1>Image Gallery</h1>
      <p>Capturing Moment - Creating memory</p>
   </header>
   <main id="main-site">
      <!-- Content -->
      <div class="show-container">
         <?php if(! empty($show)): ?>
         <div class="show-img">
            <?php $img = '/public/assets/img/' . $show[0]; ?>
            <img src="<?= $img ?>" alt="">
         </div>
         <p class="show-text">
            <?= $show[1] ?>
         </p>
         <?php endif; ?>
      </div>
      <a href="/pratique/cms">go back</a>
      <!-- Fin Content -->

   </main>
</div>

<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();