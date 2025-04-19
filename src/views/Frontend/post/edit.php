<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/client/post/main') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class='container'>
      <h>Edit Post</h>
      <?= $editFrom ?>
      <p><a href="/post/<?=$id?>/show">Cancel</a></p>
   </div>

   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/frontend/posts/new') ?>

<?php $this->end();