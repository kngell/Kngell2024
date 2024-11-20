<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <h1>Products</h1>
   <?php foreach ($products as $product) :?>
   <h2> <?=htmlspecialchars($product['name']) ?></h2>
   <p> <?=htmlspecialchars($product['description']) ?></p>
   <?php endforeach; ?>

   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/Frontend/Home/index') ?>

<?php $this->end();