<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/client/post/main') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main" class="main">
   <!-- Content -->
   <div class='container'>
      <h1>Edit Post</h1>
      <?= $editFrom ?>
      <p><a href="/post/<?=$id?>/show">Cancel</a></p>
   </div>


   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/frontend/training/image/dropzone') ?>

<?php $this->end();
