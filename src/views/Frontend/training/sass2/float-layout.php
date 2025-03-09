<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/sass2/main') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <section class="float-grig">
      <div class="row">
         <div class="col-1-of-2">
            col 1 of 2
         </div>
         <div class="col-1-of-2">
            col 1 of 2
         </div>
      </div>
      <div class="row">
         <div class="col-1-of-3">
            col 1 of 3
         </div>
         <div class="col-1-of-3">
            col 1 of 3
         </div>
         <div class="col-1-of-3">
            col 1 of 3
         </div>
      </div>
      <div class="row">
         <div class="col-1-of-3">
            col 1 of 3
         </div>
         <div class="col-2-of-3">
            col 2 of 3
         </div>
      </div>
      <div class="row">
         <div class="col-1-of-4">
            col 1 of 4
         </div>
         <div class="col-1-of-4">
            col 1 of 4
         </div>
         <div class="col-1-of-4">
            col 1 of 4
         </div>
         <div class="col-1-of-4">
            col 1 of 4
         </div>
      </div>

      <div class="row">
         <div class="col-1-of-4">
            col 1 of 4
         </div>
         <div class="col-1-of-4">
            col 1 of 4
         </div>
         <div class="col-2-of-4">
            col 2 of 4
         </div>
      </div>

      <div class="row">
         <div class="col-1-of-4">
            col 1 of 4
         </div>
         <div class="col-3-of-4">
            col 3 of 4
         </div>
      </div>
   </section>

   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();