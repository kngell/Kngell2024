<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
    <!-- Content -->
    <section class="checkout-section payment-method">
        <h2>4. Payment Method</h2>

        <fieldset class="payment-options">
            <legend class="sr-only">Choose a payment method</legend>

            <!-- Credit Card -->
            <div class="payment-option active">
                <label class="payment-header">
                    <input type="radio" name="payment_method" value="card" checked>

                    <span class="payment-info">
                        <span class="payment-title">
                            Credit / Debit Card
                        </span>

                        <span class="payment-icons">
                            <svg class="icon icon--options" aria-label="Options" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-visa"></use>
                            </svg>
                            <svg class="icon icon--options" aria-label="Options" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-mastercard"></use>
                            </svg>
                            <svg class="icon icon--options" aria-label="Options" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-google-pay"></use>
                            </svg>
                        </span>
                    </span>
                </label>

                <div class="payment-content">

                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input id="card_number" type="text" autocomplete="cc-number" placeholder="1234 5678 9012 3456">
                    </div>

                    <div class="form-row">

                        <div class="form-group">
                            <label for="card_name">Cardholder Name</label>
                            <input id="card_name" type="text" autocomplete="cc-name">
                        </div>

                    </div>

                    <div class="form-row">

                        <div class="form-group">
                            <label for="expiry">Expiry</label>
                            <input id="expiry" type="text" placeholder="MM / YY" autocomplete="cc-exp">
                        </div>

                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input id="cvv" type="password" maxlength="4" autocomplete="cc-csc">
                        </div>

                    </div>

                    <label class="checkbox">
                        <input type="checkbox">
                        Save this card securely
                    </label>

                </div>
            </div>

            <!-- PayPal -->
            <div class="payment-option">

                <label class="payment-header">
                    <input type="radio" name="payment_method" value="paypal">

                    <span class="payment-info">
                        <span class="payment-title">
                            PayPal
                        </span>

                        <svg class="icon icon--options" aria-label="Options" role="img">
                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-paypal"></use>
                        </svg>
                    </span>
                </label>

                <div class="payment-content">
                    <p>
                        After clicking <strong>Complete Purchase</strong>,
                        you'll be redirected to PayPal to complete your payment.
                    </p>
                </div>

            </div>

            <!-- Apple Pay -->
            <div class="payment-option">

                <label class="payment-header">
                    <input type="radio" name="payment_method" value="applepay">

                    <span class="payment-info">
                        <span class="payment-title">
                            Apple Pay
                        </span>

                        <svg class="icon icon--options" aria-label="Options" role="img">
                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-apple-pay"></use>
                        </svg>
                    </span>
                </label>

                <div class="payment-content">
                    <p>
                        Complete your purchase securely using Apple Pay.
                    </p>
                </div>

            </div>

            <!-- Google Pay -->
            <div class="payment-option">

                <label class="payment-header">
                    <input type="radio" name="payment_method" value="googlepay">

                    <span class="payment-info">
                        <span class="payment-title">
                            Google Pay
                        </span>
                        <svg class="icon icon--options" aria-label="Options" role="img">
                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-google-pay"></use>
                        </svg>
                    </span>
                </label>

                <div class="payment-content">
                    <p>
                        Complete your purchase securely using Google Pay.
                    </p>
                </div>

            </div>

        </fieldset>

        <div class="payment-security">
            🔒 Your payment is encrypted and processed securely.
        </div>

    </section>

    <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();