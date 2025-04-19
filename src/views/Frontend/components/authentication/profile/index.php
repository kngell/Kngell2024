<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/client/post/main') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main" class="main">
   <div class="container">
      <!-- Content -->
      <?=$userList ?>
      <!-- Fin Content -->
   </div>
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/frontend/posts/new') ?>

<?php $this->end();