<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <h1>Page not found</h1>
   <section id="notfound">
      <div class="notfound">
         <div class="notfound-404">
            <h1>403</h1>
         </div>
         <h2>Oops! Access restricted</h2>
         <p>You do not have access to this page. Please contact your admin</p>
         <a href="/">Go To Homepage</a>
      </div>
   </section>
   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();