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
         <h1>Internal server Error</h1>
         <div class="notfound-404">
            <h1>500</h1>
         </div>
         <h2>Oops! Internal Server Error</h2>
         <p>The server encountered an unexpected condition that prevented it from fulfilling the request.</p>
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