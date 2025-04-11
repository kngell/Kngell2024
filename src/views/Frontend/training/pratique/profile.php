<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class="profile-upload-container">
      <div class="profile-upload">
         <h2>Upload Profile Picture</h2>
         <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="profile_picture" accept="image/*" required>
            <button type="submit">Upload</button>
         </form>
      </div>
   </div>

   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();