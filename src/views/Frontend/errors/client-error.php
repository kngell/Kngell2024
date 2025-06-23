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
         <h1>Page not found</h1>
         <div class="notfound-404">
            <h1>404</h1>
         </div>
         <h2>Oops! Browser error!</h2>
         <p>Your browser might have cause an error. Please refresh and try again</p>
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