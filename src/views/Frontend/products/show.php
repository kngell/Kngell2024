<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class='container mb-3 w-50'>
      <h1>Products Show</h1>
      <h2> <?='Prodcut Name :' . $product['name'] ?></h2>
      <h3><?= 'Description :' . $product['description']?></h3>
      <p><a href="/products/<?=$product['id']?>/edit">Edit</a></p>
      <p><a href="/products/<?=$product['id']?>/delete">Delete</a></p>
   </div>
   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();
