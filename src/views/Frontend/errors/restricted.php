<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <section class="container" id="notfound">
      <div class="notfound">
         <div class="notfound-restricted">
            <h3>Oops! Access restricted</h3>
         </div>
         <h6>You do not have permissions to access to this page.<br> Please contact your admin(admin@kngell.com), <br>
            <hr>Or <a href="/login">Log In
            </a>
            <hr>
            Or
            <a href="/"> Return to the homepage</a>
         </h6>
      </div>
   </section>
   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();