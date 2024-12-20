<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <?=$product1?>
   <?=$product2?>
   <?=$memory?>
   <?php foreach ($vehicules as $vehicule) :?>
   <p><?= $vehicule->afficheCaracteristiques(); ?></p>
   <?php endforeach; ?>
   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();