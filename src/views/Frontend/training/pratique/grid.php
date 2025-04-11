<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/pratique/grid') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<div class="grid-container">
   <header class="grid-container__header">
      <h1>Css Grid layout #1</h1>
   </header>
   <aside class="grid-container__sidebar">
      <div class="logo">&#9812;</div>
      <button type="button" id="btn-resize" class="btn-resize">&#9776;</button>
   </aside>
   <main id="main-site" class="grid-container__main">
      <div class="card card-1">
         <h1>Welcome John Doe</h1>
      </div>
      <div class="card card-2">
         <h1>Orders - 2</h1>
      </div>
      <div class="card card-3">
         <h1>Shipped - 3</h1>
      </div>
      <div class="card card-4">
         <h1>Pending - 4</h1>
      </div>
      <div class="card card-5">
         <h1>Revenue - 5</h1>
      </div>
      <div class="card card-6">
         <h1>Users - 6</h1>
      </div>
      <div class="card card-7">
         <h1>Subscription - 7</h1>
      </div>
      <div class="card card-8">
         <h1>Analytics - 8</h1>
      </div>
      <div class="card card-9">
         <h1>Inbox - 9</h1>
      </div>
      <div class="card card-10">
         <h1>Calendar - 10</h1>
      </div>
      <div class="card card-11">
         <h1>User activity - 11</h1>
      </div>
      <div class="card card-12">
         <h1>Sales dynamics - 12</h1>
      </div>
      <div class="card card-13">
         <h1>Tasks - 13</h1>
      </div>
   </main>
   <footer class="grid-container__footer">

   </footer>
</div>

<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/frontend/training/grid/main') ?>

<?php $this->end();