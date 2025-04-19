<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/client/post/main') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class="container">
      <?=$message ?? ''?>
      <div style="text-align: right;">
         <a href="/post/new">New Post</a>
         <a href="/post-category/new">New Category</a>
      </div>
      <h1>Admin Post</h1>
      <?= $posts?>
      <?= $links?>
   </div>
   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();