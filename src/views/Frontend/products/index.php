<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class='container'>
      <h1>Products</h1>
      <h5>Total Product : <?= $total?></h5>
      <p><a href="/products/new">New Product</a></p>
      <?php foreach ($products as $product) :?>
      <h2><a href="/products/show/<?= $product['id'] ?>"> <?=htmlspecialchars($product['name']) ?></a></h2>
      <?php endforeach; ?>

   </div>


   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/Frontend/Home/index') ?>

<?php $this->end();