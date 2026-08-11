<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css($assets['css'] ?? '') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
    <!-- Content -->

    <section class="section shopping-cart-section">
        <div class="container shopping-cart">
            <h2 class="shopping-cart__title">Shopping Cart</h2>
            <div class="shopping-cart__content">
                <?= $cartList ?? '' ?>
                <?= $cartSummary ?? '' ?>
            </div>
            <?= $emptyCart ?? '' ?>
        </div>
    </section>

    <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js($assets['js'] ?? '') ?>

<?php $this->end();