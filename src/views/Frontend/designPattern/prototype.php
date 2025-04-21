<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <div class="container">
      <!-- Content -->
      <p><?=$affiche?></p>
      <p><?=$imprime?></p>
      <!-- Fin Content -->
   </div>
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();