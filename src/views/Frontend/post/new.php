<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/client/post/main') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class="container">
      <div style="text-align: right; margin-bottom: 20px;">
         <a href="/post/new">Create Post</a>
      </div>
      <h1>Create Post</h1>
      <?= $form ?>
   </div>
   <!-- Fin Content -->
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/frontend/posts/new') ?>

<?php $this->end();