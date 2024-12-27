<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <?php if($this->isUserLoggedIn()) :?>
   <p><a href="/logout" id="">Log Out</a></p>
   <?php else : ?>
   <p><a href="/signup" id="">Sign Up</a></p>
   <p><a href="/login" id="">Login</a></p>
   <?php endif; ?>

   <?=$message ?? ''?>
   Here is home index page
   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/Frontend/Home/index') ?>

<?php $this->end();