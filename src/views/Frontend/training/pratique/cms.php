<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/pratique/cms') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<!-------Header-------->
<div class="container">
   <header class="header">
      <h1>Image Gallery</h1>
      <p>Capturing Moment - Creating memory</p>
   </header>
   <main id="main-site">
      <!-- Content -->
      <div class="img-gallery-container">
         <?php foreach ($datas as $data) : ?>
         <div class="img-gallery-item">
            <h2><?= $data['title']?></h2>
            <img src="<?= $data['img']?>" alt="">
            <?php foreach ($data['content'] as $content) : ?>
            <p><?= $content?></p>
            <?php endforeach; ?>
         </div>
         <?php endforeach; ?>
      </div>
      <!-- Fin Content -->
   </main>
</div>

<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/frontend/training/cms/main') ?>

<?php $this->end();