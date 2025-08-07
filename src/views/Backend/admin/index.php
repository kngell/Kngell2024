<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main" id="main">

    <!-- Content -->

    <section class="dashboard__stats">Stats</section>
    <section class="dashboard__orders">Orders</section>
    <section class="dashboard__products">Products</section>


    <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();