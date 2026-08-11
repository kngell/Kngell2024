<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
    <!-- Content -->
    <section class="section shopping-cart-section">
        <div class="container shopping-cart">
            <?= $cartList ?? '' ?>
            <div class="shopping-cart__summary">
                <h4 class="shopping-cart__summary--title">Order Summary</h4>
                <div class="shopping-cart__summary--content">
                    <div class="subtotal">
                        <div class="subtotal__coupon">
                            <div class="subtotal__coupon--code">
                                <p class="title">Discount code</p>
                                <div class="discount-box">
                                    <input type="text" name="discount-code" id="discount-code"
                                        class="discount-box__input" placeholder="coupon code...">
                                </div>
                            </div>
                            <div class="subtotal__coupon--code">
                                <p class="title">Promo code</p>
                                <div class="discount-box">
                                    <input type="text" name="discount-code" id="promo-code" class="discount-box__input"
                                        placeholder="promo code...">
                                    <button type="button" class="discount-box__apply">
                                        <span>Apply</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="subtotal__price">
                            <div class="subtotal__price--items">
                                <h6 class="subtotal__price--items-title">Subtotal</h6>
                                <span class="subtotal__price--items-value">$2347</span>
                            </div>
                            <div class="subtotal__price--taxes">
                                <div class="subtotal__price--taxes-group">
                                    <div class="taxes-text">
                                        <p class="taxes-text__title">Estimated Tax</p>
                                        <p class="taxes-text__value">$50</p>
                                    </div>
                                    <div class="taxes-text">
                                        <p class="taxes-text__title">Estimated shipping & Handling</p>
                                        <p class="taxes-text__value">$29</p>
                                    </div>
                                </div>
                            </div>
                            <div class="subtotal__price--total">
                                <h6 class="title">Total</h6>
                                <h6 class="value">$2426</h6>

                            </div>
                        </div>
                    </div>
                    <div class="btns">
                        <button class="btn btn-dark">Checkout</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();