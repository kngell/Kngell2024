<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class='container mb-3 w-50'>
      <?=$message ?? ''?>
      <div style="text-align: right;margin-bottom: 20px;">
         <a href="/post/index">All Posts</a>
      </div>
      <h2><?= $post->getTitle()?></h2>
      <p><?= $this->htmlDecode($post->getContent())?></p>
      <p><a href="/post/<?=$post->getPostId()?>/edit">Edit</a></p>
      <p><a href="/post/<?=$post->getPostId()?>/delete">Delete</a></p>
      <p><a href="/post/index">Cancel</a></p>
   </div>

   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();