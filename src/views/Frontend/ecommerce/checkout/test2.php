<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
    <!-- Content -->
    <section class="checkout span-all">
        <div class="checkout__header">
            <div class="title">
                <div class="title-left">
                    <h4 class="title-left__text">Checkout Manager</h4>
                    <nav class="title-left__breadcrumbs" aria-label="Breadcrumb">
                        <ul class="breadcrumbs-list"></ul>
                    </nav>
                </div>
            </div>
        </div>
        <div class="container checkout__body">
            <div class="flash-container" style="display: none;" aria-live="polite" aria-atomic="true"></div>
            <form class="checkout__form" id="checkoutForm" data-validate="true" data-validation-rules="checkoutRules"
                data-form-type="checkout" name="checkout-frm" novalidate="" action="" method="POST"
                enctype="multipart/form-data">
                <input type="hidden" name="csrfToken"
                    value="d5S5N9ZhbzjBcCUnl2VrQSt5qeC3LHydr-gwMJq05_JjaGVja291dC1mcm1sbFAweUJmS2NoZWNrb3V0LWZybTE3ODUwNzI2NDA"><input
                    type="hidden" name="frm_name" value="checkout-frm"><input type="radio" hidden="" id="step1"
                    name="step" value="step1" checked=""><input type="radio" hidden="" id="step2" name="step"
                    value="step2">
                <div class="checkout__progress" aria-valuenow="1" aria-valuemin="1" aria-valuemax="2"
                    role="progressbar">
                    <div class="progress__step progress__step--active" data-step="1">
                        <div class="progress__step-content">
                            <div class="progress__icon-wrapper">
                                <svg class="icon icon--options" view-box="0 0 24 24" aria-label="Options" role="img">
                                    <use href="/public/assets/img/icons-sprite.svg#options"></use>
                                </svg><span class="progress__step-number">1</span>
                            </div>
                            <div class="progress__text-wrapper">
                                <span class="progress__step-label">Step 1</span><span
                                    class="progress__step-title">Options</span><span
                                    class="progress__step-description">Choose checkout type</span>
                            </div>
                        </div>
                        <div class="progress__connector" aria-hidden="true"></div>
                    </div>
                    <div class="progress__step" data-step="2">
                        <div class="progress__step-content">
                            <div class="progress__icon-wrapper">
                                <svg class="icon icon--address" view-box="0 0 24 24" aria-label="Address" role="img">
                                    <use href="/public/assets/img/icons-sprite.svg#address"></use>
                                </svg><span class="progress__step-number">2</span>
                            </div>
                            <div class="progress__text-wrapper">
                                <span class="progress__step-label">Step 2</span><span
                                    class="progress__step-title">Address</span><span
                                    class="progress__step-description">Enter shipping address</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="checkout__step checkout__step--active" data-step="1" id="checkout-step-step1"
                    aria-label="Options Step" role="tabpanel">
                    <div class="checkout__step-content">
                        <div class="checkout__left">
                            <div class="checkout__left">
                                <div class="checkout-options">
                                    <div class="options__header">
                                        <h3 class="options__title">How would you like to checkout?</h3>
                                        <p class="options__subtitle">Choose the option that works best for you.</p>
                                    </div>
                                    <div class="checkout-options__group">
                                        <div class="option-wrapper">
                                            <input type="radio" id="checkoutType-guest" name="checkoutType"
                                                value="guest" checked=""><label
                                                class="option-card option-card--selected option-card"
                                                for="checkoutType-guest"><span class="options__content"><span
                                                        class="options__icon">🛒</span><span
                                                        class="options__title">Continue as guest</span><span
                                                        class="options__description">No account needed. Fastest way to
                                                        place your order.</span></span></label>
                                        </div>
                                        <div class="option-wrapper">
                                            <input type="radio" id="checkoutType-login" name="checkoutType"
                                                value="login"><label class="option-card option-card"
                                                for="checkoutType-login"><span class="options__content"><span
                                                        class="options__icon">👤</span><span class="options__title">Sign
                                                        in or create an account</span><span
                                                        class="options__description">Track orders, save addresses and
                                                        get faster checkout later.</span></span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <aside class="checkout__right order-summary">
                            <div class="shopping-cart__summary">
                                <h4 class="title">Order Summary</h4>
                                <div class="shopping-cart__summary--content">
                                    <div class="subtotal">
                                        <div class="subtotal__coupon">
                                            <div class="subtotal__coupon--code">
                                                <p class="discount-title">Discount code</p>
                                                <div class="discount-box"><input type="text" class="discount-box__input"
                                                        name="discount-code" placeholder="discount code..."></div>
                                            </div>
                                            <div class="subtotal__coupon--code">
                                                <p class="promo-title">Promo code</p>
                                                <div class="discount-box"><input type="text" class="discount-box__input"
                                                        name="promo-code" placeholder="promo code..."></div>
                                            </div>
                                            <button class="btn btn--md btn--primary subtotal__coupon--apply"
                                                type="button"><span class="btn__label">Apply</span></button>
                                        </div>
                                        <div class="subtotal__price">
                                            <div class="subtotal__price--items">
                                                <h6 class="subtotal__price--items-title">Subtotal</h6>
                                                <span class="subtotal__price--items-value">18.300,00 €</span>
                                            </div>
                                            <div class="subtotal__price--taxes">
                                                <div class="taxes-text">
                                                    <p class="taxes-text__title">Estimated Tax</p>
                                                    <p class="taxes-text__value">3.050,00 €</p>
                                                </div>
                                                <div class="taxes-text">
                                                    <p class="taxes-text__title">Estimated shipping &amp; Handling</p>
                                                    <p class="taxes-text__value">0,00 €</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="total-price">
                                        <h6 class="title">Total</h6>
                                        <h6 class="value">21.350,00 €</h6>
                                    </div>
                                    <div class="summary-trust">
                                        <div class="summary-trust__item">
                                            <svg class="icon trust" view-box="0 0 24 24" aria-label="Trust" role="img">
                                                <use href="/public/assets/img/icons-sprite.svg#icon-trust"></use>
                                            </svg><span>Secure SSL checkout</span>
                                        </div>
                                        <div class="summary-trust__item">
                                            <svg class="icon trust" view-box="0 0 24 24" aria-label="Delivery"
                                                role="img">
                                                <use href="/public/assets/img/icons-sprite.svg#icon-delivery"></use>
                                            </svg><span>Fast delivery</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>
                    <div class="checkout__step-nav checkout__step-nav"><label class="btn btn--checkout-next btn--md"
                            aria-controls="checkout-step-step2" role="button" for="step2">Continue to Address</label>
                    </div>
                </div>
                <div class="checkout__step" data-step="2" id="checkout-step-step2" aria-label="Address Step"
                    role="tabpanel">
                    <div class="checkout__step-content">
                        <div class="checkout__left">
                            <div class="checkout__address-section">
                                <div class="address-selection">
                                    <h3 class="address-selection__title">Shipping &amp; Billing Addresses</h3>
                                    <div class="address-grid">
                                        <div class="address-section address-section--shipping">
                                            <div class="address-section__header">
                                                <span class="address-section__title"><span class="icon">📦</span>
                                                    Shipping Address</span><span
                                                    class="address-section__badge">Required</span>
                                            </div>
                                            <div class="address-list" id="shippingList">
                                                <label class="address-item address-item--selected"><input type="radio"
                                                        name="shippingAddress" value="1" checked=""><span
                                                        class="address-item-content"><span class="address-item-header">
                                                            <h4>Sarah Johnson</h4>
                                                        </span><span class="address-details"><span
                                                                class="address-line">742 Evergreen Terrace, Apt
                                                                4B</span><span class="address-line">Springfield, IL,
                                                                62701</span></span></span><span
                                                        class="address-actions"><button
                                                            class="btn btn--sm btn--outline btn--icon-only icon-btn"
                                                            href="#addressModal" aria-label="Edit address"
                                                            data-address-id="1" data-address-type="shipping"
                                                            type="link"><svg class="icon icon--edit"
                                                                view-box="0 0 24 24" aria-label="Edit address"
                                                                role="img">
                                                                <use
                                                                    href="/public/assets/img/icons-sprite.svg#icon-edit">
                                                                </use>
                                                            </svg></button><button
                                                            class="btn btn--sm btn--outline btn--icon-only icon-btn delete"
                                                            aria-label="Delete address" data-address-id="1"
                                                            data-address-type="shipping" type="button"><svg
                                                                class="icon icon--delete" view-box="0 0 24 24"
                                                                aria-label="Delete address" role="img">
                                                                <use
                                                                    href="/public/assets/img/icons-sprite.svg#icon-trash">
                                                                </use>
                                                            </svg></button></span></label><label
                                                    class="address-item"><input type="radio" name="shippingAddress"
                                                        value="2"><span class="address-item-content"><span
                                                            class="address-item-header">
                                                            <h4>Sarah Johnson</h4>
                                                        </span><span class="address-details"><span
                                                                class="address-line">1000 Innovation Drive, Suite
                                                                300</span><span class="address-line">Chicago, IL,
                                                                60607</span></span></span><span
                                                        class="address-actions"><button
                                                            class="btn btn--sm btn--outline btn--icon-only icon-btn"
                                                            href="#addressModal" aria-label="Edit address"
                                                            data-address-id="2" data-address-type="shipping"
                                                            type="link"><svg class="icon icon--edit"
                                                                view-box="0 0 24 24" aria-label="Edit address"
                                                                role="img">
                                                                <use
                                                                    href="/public/assets/img/icons-sprite.svg#icon-edit">
                                                                </use>
                                                            </svg></button><button
                                                            class="btn btn--sm btn--outline btn--icon-only icon-btn delete"
                                                            aria-label="Delete address" data-address-id="2"
                                                            data-address-type="shipping" type="button"><svg
                                                                class="icon icon--delete" view-box="0 0 24 24"
                                                                aria-label="Delete address" role="img">
                                                                <use
                                                                    href="/public/assets/img/icons-sprite.svg#icon-trash">
                                                                </use>
                                                            </svg></button></span></label><label
                                                    class="address-item"><input type="radio" name="shippingAddress"
                                                        value="3"><span class="address-item-content"><span
                                                            class="address-item-header">
                                                            <h4>Sarah Johnson</h4>
                                                        </span><span class="address-details"><span
                                                                class="address-line">45 Ocean View Blvd</span><span
                                                                class="address-line">Miami Beach, FL,
                                                                33139</span></span></span><span
                                                        class="address-actions"><button
                                                            class="btn btn--sm btn--outline btn--icon-only icon-btn"
                                                            href="#addressModal" aria-label="Edit address"
                                                            data-address-id="3" data-address-type="shipping"
                                                            type="link"><svg class="icon icon--edit"
                                                                view-box="0 0 24 24" aria-label="Edit address"
                                                                role="img">
                                                                <use
                                                                    href="/public/assets/img/icons-sprite.svg#icon-edit">
                                                                </use>
                                                            </svg></button><button
                                                            class="btn btn--sm btn--outline btn--icon-only icon-btn delete"
                                                            aria-label="Delete address" data-address-id="3"
                                                            data-address-type="shipping" type="button"><svg
                                                                class="icon icon--delete" view-box="0 0 24 24"
                                                                aria-label="Delete address" role="img">
                                                                <use
                                                                    href="/public/assets/img/icons-sprite.svg#icon-trash">
                                                                </use>
                                                            </svg></button></span></label>
                                            </div>
                                        </div>
                                        <div class="address-section address-section--billing">
                                            <div class="address-section__header">
                                                <span class="address-section__title"><span class="icon">💳</span>
                                                    Billing Address</span>
                                                <div class="input-field same-as-shipping-toggle"><label
                                                        class="input-field__checkbox input-field__checkbox--single"><input
                                                            type="checkbox" class="input-field__checkbox-input"
                                                            id="billingSameAsShipping" name="billingSameAsShipping"
                                                            placeholder=" "><span
                                                            class="input-field__checkbox-custom"></span><span
                                                            class="input-field__checkbox-label">Same as shipping
                                                            address</span></label></div>
                                            </div>
                                            <div class="address-list" id="billingList">
                                                <label class="address-item"><input type="radio" name="billingAddress"
                                                        value="1"><span class="address-item-content"><span
                                                            class="address-item-header">
                                                            <h4>Sarah Johnson</h4>
                                                        </span><span class="address-details"><span
                                                                class="address-line">742 Evergreen Terrace, Apt
                                                                4B</span><span class="address-line">Springfield, IL,
                                                                62701</span></span></span><span
                                                        class="address-actions"><button
                                                            class="btn btn--sm btn--outline btn--icon-only icon-btn"
                                                            href="#addressModal" aria-label="Edit address"
                                                            data-address-id="1" data-address-type="billing"
                                                            type="link"><svg class="icon icon--edit"
                                                                view-box="0 0 24 24" aria-label="Edit address"
                                                                role="img">
                                                                <use
                                                                    href="/public/assets/img/icons-sprite.svg#icon-edit">
                                                                </use>
                                                            </svg></button><button
                                                            class="btn btn--sm btn--outline btn--icon-only icon-btn delete"
                                                            aria-label="Delete address" data-address-id="1"
                                                            data-address-type="billing" type="button"><svg
                                                                class="icon icon--delete" view-box="0 0 24 24"
                                                                aria-label="Delete address" role="img">
                                                                <use
                                                                    href="/public/assets/img/icons-sprite.svg#icon-trash">
                                                                </use>
                                                            </svg></button></span></label><label
                                                    class="address-item address-item--selected"><input type="radio"
                                                        name="billingAddress" value="2" checked=""><span
                                                        class="address-item-content"><span class="address-item-header">
                                                            <h4>Sarah Johnson</h4>
                                                        </span><span class="address-details"><span
                                                                class="address-line">1000 Innovation Drive, Suite
                                                                300</span><span class="address-line">Chicago, IL,
                                                                60607</span></span></span><span
                                                        class="address-actions"><button
                                                            class="btn btn--sm btn--outline btn--icon-only icon-btn"
                                                            href="#addressModal" aria-label="Edit address"
                                                            data-address-id="2" data-address-type="billing"
                                                            type="link"><svg class="icon icon--edit"
                                                                view-box="0 0 24 24" aria-label="Edit address"
                                                                role="img">
                                                                <use
                                                                    href="/public/assets/img/icons-sprite.svg#icon-edit">
                                                                </use>
                                                            </svg></button><button
                                                            class="btn btn--sm btn--outline btn--icon-only icon-btn delete"
                                                            aria-label="Delete address" data-address-id="2"
                                                            data-address-type="billing" type="button"><svg
                                                                class="icon icon--delete" view-box="0 0 24 24"
                                                                aria-label="Delete address" role="img">
                                                                <use
                                                                    href="/public/assets/img/icons-sprite.svg#icon-trash">
                                                                </use>
                                                            </svg></button></span></label>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn--md btn--secondary add-address-btn" href="#addressModal"
                                        type="link"><span class="btn__icon"><svg class="icon" view-box="0 0 24 24"
                                                aria-label="Add New" role="img">
                                                <use href="/public/assets/img/icons-sprite.svg#icon-plus"></use>
                                            </svg></span><span class="btn__label">Add New Address</span></button>
                                </div>
                            </div>
                        </div>
                        <aside class="checkout__right order-summary">
                            <div class="shopping-cart__summary">
                                <h4 class="title">Order Summary</h4>
                                <div class="shopping-cart__summary--content">
                                    <div class="subtotal">
                                        <div class="subtotal__coupon">
                                            <div class="subtotal__coupon--code">
                                                <p class="discount-title">Discount code</p>
                                                <div class="discount-box"><input type="text" class="discount-box__input"
                                                        name="discount-code" placeholder="discount code..."></div>
                                            </div>
                                            <div class="subtotal__coupon--code">
                                                <p class="promo-title">Promo code</p>
                                                <div class="discount-box"><input type="text" class="discount-box__input"
                                                        name="promo-code" placeholder="promo code..."></div>
                                            </div>
                                            <button class="btn btn--md btn--primary subtotal__coupon--apply"
                                                type="button"><span class="btn__label">Apply</span></button>
                                        </div>
                                        <div class="subtotal__price">
                                            <div class="subtotal__price--items">
                                                <h6 class="subtotal__price--items-title">Subtotal</h6>
                                                <span class="subtotal__price--items-value">18.300,00 €</span>
                                            </div>
                                            <div class="subtotal__price--taxes">
                                                <div class="taxes-text">
                                                    <p class="taxes-text__title">Estimated Tax</p>
                                                    <p class="taxes-text__value">3.050,00 €</p>
                                                </div>
                                                <div class="taxes-text">
                                                    <p class="taxes-text__title">Estimated shipping &amp; Handling</p>
                                                    <p class="taxes-text__value">0,00 €</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="total-price">
                                        <h6 class="title">Total</h6>
                                        <h6 class="value">21.350,00 €</h6>
                                    </div>
                                    <div class="summary-trust">
                                        <div class="summary-trust__item">
                                            <svg class="icon trust" view-box="0 0 24 24" aria-label="Trust" role="img">
                                                <use href="/public/assets/img/icons-sprite.svg#icon-trust"></use>
                                            </svg><span>Secure SSL checkout</span>
                                        </div>
                                        <div class="summary-trust__item">
                                            <svg class="icon trust" view-box="0 0 24 24" aria-label="Delivery"
                                                role="img">
                                                <use href="/public/assets/img/icons-sprite.svg#icon-delivery"></use>
                                            </svg><span>Fast delivery</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>
                    <div class="checkout__step-nav checkout__step-nav checkout__step-nav">
                        <label class="btn btn--checkout-back btn--md" aria-controls="checkout-step-step1" role="button"
                            for="step1">Back</label><button class="btn btn--lg btn--checkout-submit" type="submit"><span
                                class="btn__label">Place Order</span></button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();