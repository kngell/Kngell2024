<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
   <!-- Content -->
   <form>
      <label for="name">Name</label>
      <input type="text" name="name" id="name">
      <button>Submit</button>
   </form>
   <h1><?php print_r($response) ?></h1>
   <p>Status Code:&nbsp;<?= $statusCode ?? '' ?></p>


   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();