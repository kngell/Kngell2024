<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
    <!-- Content -->
    <section class="checkout-steps-section">
        <form class="container checkout-form" id="checkoutForm">
            <input type="radio" name="step" id="step1" checked hidden>
            <input type="radio" name="step" id="step2" hidden>
            <input type="radio" name="step" id="step3" hidden>
            <input type="radio" name="step" id="step4" hidden>
            <div class="progress-bar">
                <div class="progress-step active" data-step="1">
                    <div class="progress-step__content">
                        <div class="progress-step__icon-container">
                            <svg class="icon address" aria-label="Address" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-address"></use>
                            </svg>
                        </div>
                        <div class="progress-step__text-container">
                            <h6 class="title">Step 1</h6>
                            <p class="description">Address</p>
                        </div>
                    </div>
                    <div class="progress-step__connector"></div>
                </div>
                <div class="progress-step" data-step="2">
                    <div class="progress-step__content">
                        <div class="progress-step__icon-container">
                            <svg class="icon shipping" aria-label="Address" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-shipping"></use>
                            </svg>
                        </div>
                        <div class="progress-step__text-container">
                            <h6 class="title">Step 2</h6>
                            <p class="description">Shipping</p>
                        </div>
                    </div>
                    <div class="progress-step__connector"></div>
                </div>
                <div class="progress-step" data-step="3">
                    <div class="progress-step__content">
                        <div class="progress-step__icon-container">
                            <svg class="icon payment" aria-label="Address" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-payment"></use>
                            </svg>
                        </div>
                        <div class="progress-step__text-container">
                            <h6 class="title">Step 3</h6>
                            <p class="description">Payment</p>
                        </div>
                    </div>
                    <div class="progress-step__connector"></div>
                </div>
            </div>
            <div class="form-step address active" data-step="1">
                <div class="form-step__block">
                    <h4 class="form-step__block--title">Select Address</h4>
                    <div class="details">
                        <div class="details__addresses">
                            <div class="details__addresses--address">
                                <div class="address-container">
                                    <div class="address-container--top">
                                        <input type="radio" name="shipping-address" id="input-box__radio-address1"
                                            checked>
                                        <label for="input-box__radio-address1" class="address-container--top__label">
                                            <span class="text-container">
                                                <span class="title">2118 Thornridge</span>
                                                <span class="tag"><span>Home</span></span>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="address-container--info">
                                        <p class="info-title">2118 Thornridge Cir. Syracuse, Connecticut 35624</p>
                                        <div class="info-phone">
                                            <p class="phone__title">Contact: </p>
                                            <p class="phone__number">(209) 555-0104</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="address-action">
                                    <label for="modalToggle" class="modal-trigger address-action__icon-container">
                                        <svg class="icon edit" aria-label="Edit" role="img">
                                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-edit"></use>
                                        </svg>
                                    </label>
                                    <div class="address-action__icon-container">
                                        <svg class="icon delete" aria-label="Delete" role="img">
                                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel">
                                            </use>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="details__addresses--address">
                                <div class="address-container">
                                    <div class="address-container--top">
                                        <input type="radio" name="shipping-address" id="input-box__radio-address2">
                                        <label for="input-box__radio-address2" class="address-container--top__label">
                                            <span class="text-container">
                                                <span class="title">Headoffic</span>
                                                <span class="tag"><span>Office</span></span>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="address-container--info">
                                        <p class="info-title">2715 Ash Dr. San Jose, South Dakota 83475</p>
                                        <div class="info-phone">
                                            <p class="phone__title">Contact: </p>
                                            <p class="phone__number">(704) 555-0127</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="address-action">
                                    <label for="modalToggle" class="modal-trigger address-action__icon-container">
                                        <svg class="icon edit" aria-label="Edit" role="img">
                                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-edit"></use>
                                        </svg>
                                    </label>
                                    <div class="address-action__icon-container">
                                        <svg class="icon delete" aria-label="Delete" role="img">
                                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel">
                                            </use>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <label for="modalToggle" class="modal-trigger details__new-line">
                            <span class="line-and-icon-wrapper">
                                <span class="icon-container">
                                    <svg class="icon address" aria-label="Plus" role="img">
                                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-plus-solid"></use>
                                    </svg>
                                </span>
                            </span>
                            <span class="text">Add New Address</span>
                        </label>
                    </div>
                </div>
                <div class="form-step__buttons">
                    <label for="step2" class="btn btn-dark btn-next">Next</label>
                </div>
            </div>
            <div class="form-step shipping" data-step="2">
                <div class="form-step__block">
                    <h4 class="form-step__block--title">Shipment Method</h4>
                    <div class="form-step__block--methods">
                        <div class="shipping-method">
                            <div class="input-box-radio shipping-method__text">
                                <input type="radio" name="shipping-method" id="method1" checked>
                                <label for="method1" class="input-box-radio__label">
                                    <span class="cost">Free</span>
                                    <span class="desc">Regulary shipment</span>
                                </label>
                            </div>
                            <div class="shipping-method__date">
                                <p class="date">17 Oct, 2023</p>
                            </div>
                        </div>
                        <div class="shipping-method">
                            <div class="input-box-radio input-box-radio--horizontal shipping-method__text">
                                <input type="radio" name="shipping-method" id="method2">
                                <label for="method2" class="input-box-radio__label">
                                    <span class="cost">$8.50</span>
                                    <span class="desc">Get your delivery as soon as possible</span>
                                </label>
                            </div>
                            <div class="shipping-method__date">
                                <p class="date">1 Oct, 2023</p>
                            </div>
                        </div>
                        <div class="shipping-method">
                            <div class="input-box-radio shipping-method__text">
                                <input type="radio" name="shipping-method" id="method3">
                                <label for="method3" class="input-box-radio__label">
                                    <span class="cost">Schedule</span>
                                    <span class="desc">Pick a date when you want to get your delivery</span>
                                </label>
                            </div>
                            <div class="shipping-method__date">
                                <p class="date">Select Date</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-step__buttons">
                    <label for="step1" class="btn btn-outline-dark btn-back">Back</label>
                    <label for="step3" class="btn btn-dark btn-next">Next</label>
                </div>
            </div>
            <div class="form-step summary" data-step="3">
                <div class="summary__left">
                    <h4 class="summary__left--title">Summary</h4>
                    <div class="summary__left--cart">
                        <div class="added-product">
                            <div class="added-product__img-container">
                                <img src="../../../../assets/img/ecommerce/product-img.png" alt="" class="image">
                            </div>
                            <div class="added-product__info">
                                <h6 class="added-product__info--title">Apple iPhone 14 Pro Max 128Gb </h6>
                                <span class="added-product__info--price">$1399</span>
                            </div>
                        </div>
                        <div class="added-product">
                            <div class="added-product__img-container">
                                <img src="../../../../assets/img/ecommerce/product-img.png" alt="" class="image">
                            </div>
                            <div class="added-product__info">
                                <h6 class="added-product__info--title">Apple iPhone 14 Pro Max 128Gb </h6>
                                <span class="added-product__info--price">$1399</span>
                            </div>
                        </div>
                        <div class="added-product">
                            <div class="added-product__img-container">
                                <img src="../../../../assets/img/ecommerce/product-img.png" alt="" class="image">
                            </div>
                            <div class="added-product__info">
                                <h6 class="added-product__info--title">Apple iPhone 14 Pro Max 128Gb </h6>
                                <span class="added-product__info--price">$1399</span>
                            </div>
                        </div>
                    </div>
                    <div class="summary__left--details">
                        <div class="shipping-info">
                            <div class="shipping-info__details">
                                <h6 class="shipping-info__details--title">Address</h6>
                                <div class="shipping-info__details--descr"><span>1131 Dusty Townline, Jacksonville,
                                        TX
                                        40322</span> </div>
                            </div>
                            <div class="shipping-info__details">
                                <h6 class="shipping-info__details--title">Shipment method</h6>
                                <div class="shipping-info__details--descr"><span>Free</span> </div>
                            </div>
                        </div>
                        <div class="price-info">
                            <div class="price-info__subtotal">
                                <h6 class="price-info__subtotal--title">Subtotal</h6>
                                <span class="price-info__subtotal--value">$2347</span>
                            </div>
                            <div class="price-info__taxes">
                                <div class="price-info__taxes--tax-details">
                                    <div class="text">
                                        <h6 class="text__title">Estimated Tax</h6>
                                        <span class="text__value">
                                            $50
                                        </span>
                                    </div>
                                </div>
                                <div class="price-info__taxes--tax-details">
                                    <div class="text">
                                        <h6 class="text__title">Estimated shipping & Handling</h6>
                                        <span class="text__value">
                                            $29
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="price-info__total">
                                <h6 class="price-info__total--title">Total</h6>
                                <span class="price-info__total--value">$2426</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="summary__right">
                    <!-- Fallback radio inputs (CSS-only tab state) -->
                    <input type="radio" name="payment-method" id="tab-credit" checked>
                    <input type="radio" name="payment-method" id="tab-paypal">
                    <input type="radio" name="payment-method" id="tab-paypal-credit">
                    <div class="payment-details payment-tabs">
                        <div class="payment-details__header">
                            <h4 class="title">Payment</h4>
                            <!-- Tab labels -->
                            <div class="tabs-labels" role="tablist">
                                <label class="mode" for="tab-credit" data-mode="credit" role="tab">Credit
                                    Card</label>
                                <label class="mode" for="tab-paypal" data-mode="paypal" role="tab">Paypal</label>
                                <label class="mode" for="tab-paypal-credit" data-mode="paypal-credit" role="tab">Paypal
                                    Credit</label>
                            </div>
                        </div>
                        <!-- Tab content -->
                        <div class="payment-details__tab-content">
                            <div class="payment-form credit" data-mode="credit" role="tabpanel">
                                <div class="credit__img-container">
                                    <img src="../../../../assets/img/ecommerce/Mastercard.png" alt="Credit Card"
                                        class="image">
                                </div>
                                <div class="credit__card-fields">
                                    <div class="input-box card-field">
                                        <input type="text" name="card-name" id="card-name" placeholder=" "
                                            class="input-box__input">
                                        <label for="card-name" class="input-box__label">Cardholder Name</label>
                                        <div class="input-box__hint-text invalid-feedback">message</div>
                                    </div>
                                    <div class="input-box card-field">
                                        <input type="text" name="card-name" id="card-number" placeholder=" "
                                            class="input-box__input">
                                        <label for="card-number" class="input-box__label">Cardholder Name</label>
                                        <div class="input-box__hint-text invalid-feedback">message</div>
                                    </div>
                                    <div class="expiry-and-cvv">
                                        <div class="input-box card-field">
                                            <input type="text" name="card-expiry" id="card-expiry" placeholder=" "
                                                class="input-box__input">
                                            <label for="card-expiry" class="input-box__label">Cardholder
                                                Name</label>
                                            <div class="input-box__hint-text invalid-feedback">message</div>
                                        </div>
                                        <div class="input-box card-field">
                                            <input type="text" name="card-cvv" id="card-cvv" placeholder=" "
                                                class="input-box__input">
                                            <label for="card-cvv" class="input-box__label">Cardholder Name</label>
                                            <div class="input-box__hint-text invalid-feedback">message</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="credit__input-checkbox">
                                    <input type="checkbox" name="same-address" id="same-address">
                                    <label for="same-address" class="input-checkbox__label">Same as billing
                                        address</label>
                                    <div class="input-checkbox__hint-text">message</div>
                                </div>
                                <div class="credit__buttons">
                                    <label for="step2" class="btn btn-outline-dark btn-back">Back</label>
                                    <button type="submit" class="btn btn-dark">Submit</button>
                                </div>
                            </div>
                            <div class="payment-form paypal" data-mode="paypal" role="tabpanel">
                                <p>Paypal payment info here…</p>
                            </div>
                            <div class="payment-form paypal-credit" data-mode="paypal-credit" role="tabpanel">
                                <p>Paypal Credit payment info here…</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </section>
    <section class="section modals">
        <!-- Hidden checkbox to toggle modal -->
        <input type="checkbox" id="modalToggle" class="modal-toggle">
        <div class="modal-overlay" id="addressModal">
            <div class="modal">
                <label for="modalToggle" class="modal-close" aria-label="Close">&times;</label>

                <form class="form-address">
                    <h2>Add/Edit Address</h2>

                    <div class="input-box">
                        <input type="text" id="fullName" name="fullName" class="input-box__input" placeholder=" " />
                        <label for="fullName" class="input-box__label">Full Name</label>
                        <div class="input-box__hint-text" id="fullNameError"></div>
                    </div>

                    <div class="input-box">
                        <input type="text" id="addressLine1" name="addressLine1" class="input-box__input"
                            placeholder=" " />
                        <label for="addressLine1" class="input-box__label">Address Line 1:</label>
                        <div class="error-message" id="addressLine1Error"></div>
                    </div>
                    <div class="input-box">
                        <input type="text" id="city" name="city" class="input-box__input" placeholder=" " />
                        <label for="city" class="input-box__label">City:</label>
                        <div class="error-message" id="cityError"></div>
                    </div>
                    <div class="input-box">
                        <input type="text" id="postalCode" name="postalCode"
                            title="Enter a valid postal code (e.g., 75001 or 75001-1234)" class="input-box__input"
                            placeholder=" " />
                        <label for="postalCode" class="input-box__label">Postal Code:</label>
                        <div class="error-message" id="postalCodeError"></div>
                    </div>
                    <div class="input-box">
                        <input type="text" id="country" name="country" class="input-box__input" placeholder=" " />
                        <label for="country" class="input-box__label">Country:</label>
                        <div class="error-message" id="countryError"></div>
                    </div>

                    <div class="buttons">
                        <button type="button" class="btn cancel-btn">Cancel</button>
                        <button type="submit" class="btn next-btn">Submit</button>
                    </div>
                </form>
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