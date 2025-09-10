<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="dashboard__main main" id="main">
    <!-- Content -->
    <div class="card card-1">
        <h2>Welcome John Doe</h2>
    </div>
    <div class="card card-2">
        <h2>Order 2</h2>
    </div>
    <div class="card card-3">
        <h2>Shipped 3</h2>
    </div>
    <div class="card card-4">
        <h2>Pending 4</h2>
    </div>
    <div class="card card-5">
        <h2>Revenue 5</h2>
    </div>
    <div class="card card-6">
        <h2>Users 6</h2>
    </div>
    <div class="card card-7">
        <h2>Suscription 7</h2>
    </div>
    <div class="card card-8">
        <h2>Analytics 8</h2>
    </div>
    <div class="card card-9">
        <h2>Inbox 9</h2>
    </div>
    <div class="card card-10">
        <h2>Calendar 10</h2>
    </div>
    <div class="card card-11">
        <h2>User activity 11</h2>
    </div>
    <div class="card card-12">
        <h2>Sales dynamics 12</h2>
    </div>
    <div class="card card-13">
        <h2>Tasks 13</h2>
    </div>

    <!-- Fin Content -->
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();