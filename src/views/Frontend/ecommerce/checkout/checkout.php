<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css($formAsset ?? '') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
    <!-- Content -->

    <section class="checkout-section span-all">
        <div class="container <?= $formAsset['sectionClass'] ?? ''?>">
            <div class="<?= $formAsset['sectionClass'] ?? ''?>__header">
                <h2 class="checkout__header__title">Checkout</h2>
                <p class="checkout__header__text">Proceed to checkout</p>
            </div>
            <?= $checkoutForm ?? '' ?>
        </div>
    </section>
    <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js($formAsset['js'] ?? '') ?>

<?php $this->end();