<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Custom-------->
<?= $this->css('css/frontend/ecommerce/pages/markup') ?>
<style>
/* ============================================
   ORDER REVIEW - PROFESSIONAL STYLES
   ============================================ */

/* ---------- Base & Reset ---------- */
.order-review {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1.5rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    color: #1a1a2e;
    background: #f8f9fc;
}

.visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}

/* ---------- Typography ---------- */
.order-review__title {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: #1a1a2e;
    letter-spacing: -0.5px;
}

.order-review__subtitle {
    font-size: 1rem;
    color: #6b7280;
    margin: 0.25rem 0 0;
}

/* ---------- Header ---------- */
.order-review__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #e5e7eb;
}

.order-review__header-content {
    flex: 1;
}

.order-review__order-number {
    display: inline-block;
    margin-top: 0.5rem;
    padding: 0.25rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #4f46e5;
    background: #eef2ff;
    border-radius: 9999px;
    letter-spacing: 0.5px;
}

.order-review__security-badges {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    padding-top: 0.25rem;
}

.security-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #065f46;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 9999px;
}

/* ---------- Sections ---------- */
.order-review__section {
    background: #ffffff;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
    transition: box-shadow 0.2s ease;
}

.order-review__section:hover {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05), 0 2px 4px rgba(0, 0, 0, 0.03);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #f3f4f6;
}

.section-header__title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1a1a2e;
    margin: 0;
}

.section-header__edit {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #4f46e5;
    text-decoration: none;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    transition: background 0.15s ease;
}

.section-header__edit:hover {
    background: #eef2ff;
    color: #4338ca;
}

.section-header__edit svg {
    flex-shrink: 0;
}

/* ---------- Order Items ---------- */
.order-items {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.order-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: #fafbfc;
    border-radius: 8px;
    border: 1px solid #f3f4f6;
    transition: border-color 0.15s ease;
}

.order-item:hover {
    border-color: #e5e7eb;
}

.order-item__image-wrapper {
    flex-shrink: 0;
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    background: #f3f4f6;
}

.order-item__image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.order-item__details {
    flex: 1;
    min-width: 0;
}

.order-item__name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1a1a2e;
    margin: 0 0 0.25rem;
}

.order-item__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1rem;
    font-size: 0.8rem;
    color: #6b7280;
}

.order-item__sku {
    color: #9ca3af;
}

.order-item__variant {
    background: #f3f4f6;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
}

.order-item__quantity {
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: #6b7280;
}

.order-item__qty-value {
    font-weight: 600;
    color: #1a1a2e;
}

.order-item__pricing {
    flex-shrink: 0;
    text-align: right;
    padding-left: 1rem;
}

.order-item__price {
    display: block;
    font-size: 1.025rem;
    font-weight: 700;
    color: #1a1a2e;
}

.order-item__unit-price {
    display: block;
    font-size: 0.7rem;
    color: #9ca3af;
    margin-top: 0.125rem;
}

.order-item__unit-price--free {
    color: #059669;
    font-weight: 500;
}

.order-items__summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    margin-top: 0.5rem;
    border-top: 1px solid #f3f4f6;
    font-size: 0.875rem;
    color: #6b7280;
}

.order-items__summary-total {
    font-size: 1rem;
}

.order-items__summary-total strong {
    color: #1a1a2e;
}

/* ---------- Info Grid ---------- */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
}

/* ---------- Info Cards ---------- */
.info-card {
    background: #fafbfc;
    border-radius: 8px;
    padding: 1.25rem;
    border: 1px solid #f3f4f6;
    transition: border-color 0.15s ease;
}

.info-card:hover {
    border-color: #e5e7eb;
}

.info-card--highlight {
    background: #f5f3ff;
    border-color: #ddd6fe;
}

.info-card__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.info-card__title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin: 0;
}

.info-card__edit {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: #4f46e5;
    text-decoration: none;
}

.info-card__edit:hover {
    color: #4338ca;
}

.info-card__body {
    margin-bottom: 0.5rem;
}

.info-card__footer {
    padding-top: 0.5rem;
    border-top: 1px solid #f3f4f6;
}

.info-card__badge {
    display: inline-block;
    padding: 0.125rem 0.5rem;
    font-size: 0.65rem;
    font-weight: 500;
    border-radius: 9999px;
}

.info-card__badge--verified {
    color: #065f46;
    background: #ecfdf5;
}

.info-card__badge--info {
    color: #1e40af;
    background: #eff6ff;
}

.info-card__badge--secure {
    color: #7c3aed;
    background: #f5f3ff;
}

/* ---------- Address ---------- */
.info-card__address {
    font-style: normal;
    font-size: 0.875rem;
    line-height: 1.6;
    color: #374151;
}

.info-card__recipient {
    font-weight: 600;
    display: block;
}

.info-card__company {
    color: #6b7280;
    display: block;
}

.info-card__street,
.info-card__city-state,
.info-card__country {
    display: block;
}

.info-card__phone,
.info-card__email {
    display: block;
    font-size: 0.8rem;
    color: #6b7280;
}

/* ---------- Shipping Method ---------- */
.shipping-method {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.shipping-method__icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.shipping-method__details {
    flex: 1;
}

.shipping-method__name {
    display: block;
    font-weight: 600;
    font-size: 0.875rem;
    color: #1a1a2e;
}

.shipping-method__delivery {
    display: block;
    font-size: 0.8rem;
    color: #6b7280;
}

.shipping-method__duration {
    display: block;
    font-size: 0.7rem;
    color: #9ca3af;
}

.shipping-method__price {
    font-weight: 700;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.shipping-method__price--free {
    color: #059669;
}

.shipping-method__tracking {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid #f3f4f6;
    font-size: 0.75rem;
    color: #6b7280;
}

.shipping-method__tracking-number {
    font-weight: 500;
    color: #374151;
}

/* ---------- Payment ---------- */
.payment-method {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.payment-method__card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
}

.payment-method__icon {
    flex-shrink: 0;
}

.payment-method__details {
    flex: 1;
}

.payment-method__type {
    display: block;
    font-weight: 600;
    font-size: 0.875rem;
    color: #1a1a2e;
}

.payment-method__number {
    display: block;
    font-size: 0.8rem;
    color: #6b7280;
    letter-spacing: 1px;
}

.payment-method__expiry {
    display: block;
    font-size: 0.7rem;
    color: #9ca3af;
}

.payment-method__status {
    font-size: 0.7rem;
    font-weight: 500;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
}

.payment-method__status--valid {
    color: #065f46;
    background: #ecfdf5;
}

.payment-method__billing {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid #f3f4f6;
    font-size: 0.75rem;
    color: #6b7280;
}

.payment-method__billing-address {
    color: #374151;
}

/* ---------- Payment Summary ---------- */
.payment-summary {
    margin: 0;
}

.payment-summary__row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.25rem 0;
    font-size: 0.875rem;
    color: #6b7280;
}

.payment-summary__row dd {
    margin: 0;
    font-weight: 500;
    color: #1a1a2e;
}

.payment-summary__value--free {
    color: #059669;
}

.payment-summary__value--discount {
    color: #dc2626;
}

.payment-summary__row--discount {
    color: #dc2626;
}

.payment-summary__row--discount dd {
    color: #dc2626;
}

.payment-summary__tooltip {
    cursor: help;
    font-size: 0.7rem;
    color: #9ca3af;
}

.payment-summary__divider {
    height: 1px;
    background: #e5e7eb;
    margin: 0.5rem 0;
}

.payment-summary__row--total {
    padding: 0.5rem 0 0.25rem;
    font-size: 1.125rem;
}

.payment-summary__row--total dt,
.payment-summary__row--total dd {
    color: #1a1a2e;
}

.payment-summary__row--currency {
    justify-content: flex-end;
    font-size: 0.7rem;
    color: #9ca3af;
    padding-top: 0;
}

/* ---------- Consent ---------- */
.consent-section {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.consent-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.consent-group--highlight {
    padding: 0.75rem 1rem;
    background: #fffbeb;
    border-radius: 6px;
    border: 1px solid #fde68a;
}

.consent-group__notice {
    margin: 0;
    font-size: 0.8rem;
    color: #92400e;
}

.checkbox-field {
    display: flex;
    align-items: flex-start;
    gap: 0.625rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: #374151;
    line-height: 1.5;
}

.checkbox-field input[type="checkbox"] {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}

.checkbox-field__custom {
    flex-shrink: 0;
    width: 1.125rem;
    height: 1.125rem;
    margin-top: 0.125rem;
    border: 2px solid #d1d5db;
    border-radius: 4px;
    background: #fff;
    transition: all 0.15s ease;
    position: relative;
}

.checkbox-field input[type="checkbox"]:checked+.checkbox-field__custom {
    background: #4f46e5;
    border-color: #4f46e5;
}

.checkbox-field input[type="checkbox"]:checked+.checkbox-field__custom::after {
    content: '';
    position: absolute;
    left: 4px;
    top: 1px;
    width: 6px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.checkbox-field input[type="checkbox"]:focus-visible+.checkbox-field__custom {
    outline: 2px solid #4f46e5;
    outline-offset: 2px;
}

.checkbox-field__label {
    flex: 1;
}

.checkbox-field__label a {
    color: #4f46e5;
    text-decoration: none;
}

.checkbox-field__label a:hover {
    text-decoration: underline;
}

.required {
    color: #dc2626;
    font-weight: 600;
}

.error-message {
    font-size: 0.75rem;
    color: #dc2626;
    padding-left: 1.75rem;
    min-height: 1.25rem;
}

/* ---------- Actions ---------- */
.order-review__actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 2px solid #e5e7eb;
}

.order-review__actions-left,
.order-review__actions-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* ---------- Buttons ---------- */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
    font-family: inherit;
}

.btn--back {
    background: #f3f4f6;
    color: #374151;
}

.btn--back:hover {
    background: #e5e7eb;
}

.btn--outline {
    background: transparent;
    color: #4f46e5;
    border: 2px solid #e5e7eb;
}

.btn--outline:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.btn--primary {
    background: #4f46e5;
    color: #fff;
    padding: 0.75rem 2rem;
    min-width: 200px;
}

.btn--primary:hover {
    background: #4338ca;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.btn--primary:active {
    transform: translateY(0);
}

.btn--primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn__text {
    display: inline;
}

.btn__amount {
    display: inline;
    font-weight: 700;
    opacity: 0.9;
}

.btn__loading {
    display: none;
    align-items: center;
    gap: 0.5rem;
}

.btn__loading[hidden] {
    display: none;
}

.btn__loading:not([hidden]) {
    display: inline-flex;
}

.spinner {
    width: 1rem;
    height: 1rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.order-review__secure-notice {
    font-size: 0.75rem;
    color: #6b7280;
    margin: 0.25rem 0 0;
    text-align: center;
    width: 100%;
}

/* ---------- Footer ---------- */
.order-review__footer {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
    text-align: center;
}

.order-review__footer-text {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0.25rem 0;
}

.order-review__footer-text a {
    color: #4f46e5;
    text-decoration: none;
}

.order-review__footer-text a:hover {
    text-decoration: underline;
}

.order-review__footer-text--small {
    font-size: 0.75rem;
    color: #9ca3af;
}

/* ---------- Responsive ---------- */
@media (max-width: 640px) {
    .order-review {
        padding: 1rem;
    }

    .order-review__header {
        flex-direction: column;
    }

    .order-review__title {
        font-size: 1.5rem;
    }

    .order-review__security-badges {
        width: 100%;
    }

    .order-item {
        flex-wrap: wrap;
        padding: 0.75rem;
    }

    .order-item__image-wrapper {
        width: 60px;
        height: 60px;
    }

    .order-item__pricing {
        width: 100%;
        text-align: left;
        padding-left: 0;
        padding-top: 0.5rem;
        border-top: 1px solid #f3f4f6;
    }

    .order-item__price {
        display: inline;
    }

    .order-item__unit-price {
        display: inline;
        margin-left: 0.5rem;
    }

    .order-review__section {
        padding: 1rem;
    }

    .order-review__actions {
        flex-direction: column;
        align-items: stretch;
    }

    .order-review__actions-left,
    .order-review__actions-right {
        justify-content: center;
        width: 100%;
    }

    .btn--primary {
        width: 100%;
        min-width: unset;
    }

    .payment-method__card {
        flex-wrap: wrap;
    }
}
</style>
<?php $this->end(); ?>

<?php $this->start('body'); ?>
<main class="main" id="main">
    <!-- Checkout Section -->

    <section class="container checkout" aria-label="Checkout Process">
        <form class="checkout__form" id="checkoutForm" novalidate autocomplete="on">
            <!-- Hidden radio buttons for step navigation - MUST be direct siblings of steps -->
            <input type="radio" name="step" id="step1" value="options" checked hidden>
            <input type="radio" name="step" id="step2" value="address" hidden>
            <input type="radio" name="step" id="step3" value="shipping" hidden>
            <input type="radio" name="step" id="step4" value="payment" hidden>
            <input type="radio" name="step" id="step5" value="review" hidden>

            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= $this->token() ?>" hidden>

            <!-- Progress Bar -->
            <div class="checkout__progress" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="5">
                <div class="progress__step" data-step="1">
                    <div class="progress__step-content">
                        <div class="progress__icon-wrapper">
                            <svg class="icon icon--options" aria-label="Options" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-user-check"></use>
                            </svg>
                            <span class="progress__step-number">1</span>
                        </div>
                        <div class="progress__text-wrapper">
                            <span class="progress__step-label">Step 1</span>
                            <span class="progress__step-title">Options</span>
                        </div>
                    </div>
                    <div class="progress__connector" aria-hidden="true"></div>
                </div>
                <div class="progress__step" data-step="2">
                    <div class="progress__step-content">
                        <div class="progress__icon-wrapper">
                            <svg class="icon icon--address" aria-label="Address" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-address"></use>
                            </svg>
                            <span class="progress__step-number">2</span>
                        </div>
                        <div class="progress__text-wrapper">
                            <span class="progress__step-label">Step 2</span>
                            <span class="progress__step-title">Address</span>
                        </div>
                    </div>
                    <div class="progress__connector" aria-hidden="true"></div>
                </div>
                <div class="progress__step" data-step="3">
                    <div class="progress__step-content">
                        <div class="progress__icon-wrapper">
                            <svg class="icon icon--shipping" aria-label="Shipping" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-shipping"></use>
                            </svg>
                            <span class="progress__step-number">3</span>
                        </div>
                        <div class="progress__text-wrapper">
                            <span class="progress__step-label">Step 3</span>
                            <span class="progress__step-title">Shipping</span>
                        </div>
                    </div>
                    <div class="progress__connector" aria-hidden="true"></div>
                </div>
                <div class="progress__step" data-step="4">
                    <div class="progress__step-content">
                        <div class="progress__icon-wrapper">
                            <svg class="icon icon--payment" aria-label="Payment" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-payment"></use>
                            </svg>
                            <span class="progress__step-number">4</span>
                        </div>
                        <div class="progress__text-wrapper">
                            <span class="progress__step-label">Step 4</span>
                            <span class="progress__step-title">Payment</span>
                        </div>
                    </div>
                    <div class="progress__connector" aria-hidden="true"></div>
                </div>
                <div class="progress__step" data-step="5">
                    <div class="progress__step-content">
                        <div class="progress__icon-wrapper">
                            <svg class="icon icon--review" aria-label="Review" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-review"></use>
                            </svg>
                            <span class="progress__step-number">5</span>
                        </div>
                        <div class="progress__text-wrapper">
                            <span class="progress__step-label">Step 5</span>
                            <span class="progress__step-title">Review</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================================ -->
            <!-- STEP 1: CHECKOUT OPTIONS          -->
            <!-- ================================ -->
            <div class="checkout__step" data-step="1" role="tabpanel" id="checkout-step-options"
                aria-label="Checkout Options Step">
                <div class="checkout__step-content">
                    <div class="checkout__left">
                        <div class="checkout-options" role="radiogroup" aria-label="Checkout Type">
                            <h3 class="checkout-options__title">How would you like to checkout?</h3>
                            <p class="checkout-options__subtitle">Choose the experience that fits your needs best.</p>
                            <input type="radio" name="checkoutType" id="checkoutGuest" value="guest" checked hidden>
                            <input type="radio" name="checkoutType" id="checkoutLogin" value="login" hidden>
                            <div class="checkout-options__group">
                                <label for="checkoutGuest" class="radio-card radio-card--guest">
                                    <span class="radio-card__content">
                                        <span class="radio-card__title">Continue as guest</span>
                                        <span class="radio-card__description">No account needed. Fastest way to place
                                            your order.</span>
                                    </span>
                                </label>
                                <label for="checkoutLogin" class="radio-card radio-card--login">
                                    <span class="radio-card__content">
                                        <span class="radio-card__title">Sign in or create an account</span>
                                        <span class="radio-card__description">Track orders, save addresses and get
                                            faster checkout later.</span>
                                    </span>
                                </label>
                            </div>

                            <div class="checkout-options__benefits">
                                <h4 class="checkout-options__benefits-title">Why sign in?</h4>
                                <ul class="benefits-list">
                                    <li class="benefit-item">
                                        <span class="benefit-item__icon">✓</span>
                                        <span class="benefit-item__text">Save your delivery addresses for faster future
                                            orders.</span>
                                    </li>
                                    <li class="benefit-item">
                                        <span class="benefit-item__icon">✓</span>
                                        <span class="benefit-item__text">Track your orders and get real-time delivery
                                            updates.</span>
                                    </li>
                                    <li class="benefit-item">
                                        <span class="benefit-item__icon">✓</span>
                                        <span class="benefit-item__text">Access exclusive offers and faster customer
                                            support.</span>
                                    </li>
                                    <li class="benefit-item">
                                        <span class="benefit-item__icon">✓</span>
                                        <span class="benefit-item__text">Keep your payment methods and preferences
                                            securely saved.</span>
                                    </li>
                                </ul>
                            </div>
                            </input>
                        </div>

                        <aside class="checkout__right order-summary" aria-label="Order Summary">
                            <h3 class="order-summary__title">What you get</h3>
                            <div class="order-summary__content">
                                <div class="order-summary__security">
                                    <span class="security-badge">🔒 Secure and flexible</span>
                                    <span class="security-badge__text">Choose guest checkout for speed or sign in for a
                                        more
                                        personalised experience.</span>
                                </div>
                                <div class="order-summary__prices">
                                    <div class="price-row">
                                        <span class="price-row__label">Delivery flexibility</span>
                                        <span class="price-row__value">Flexible</span>
                                    </div>
                                    <div class="price-row price-row--secondary">
                                        <span class="price-row__label">Account benefits</span>
                                        <span class="price-row__value">Included</span>
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>
                    <aside class="checkout__right order-summary" aria-label="Order Summary">
                        <h3 class="order-summary__title">Order Summary</h3>
                        <div class="order-summary__content">
                            <div class="order-summary__coupons">
                                <div class="coupon-field">
                                    <label for="discountCode" class="coupon-field__label">Discount Code</label>
                                    <div class="coupon-field__wrapper">
                                        <input type="text" name="discountCode" id="discountCode"
                                            class="coupon-field__input" placeholder="Enter discount code">
                                        <button type="button" class="coupon-field__apply">Apply</button>
                                    </div>
                                </div>
                            </div>

                            <div class="order-summary__prices">
                                <div class="price-row">
                                    <span class="price-row__label">Subtotal</span>
                                    <span class="price-row__value">€18,300.00</span>
                                </div>
                                <div class="price-row price-row--secondary">
                                    <span class="price-row__label">Estimated Tax</span>
                                    <span class="price-row__value">€3,050.00</span>
                                </div>
                                <div class="price-row price-row--secondary">
                                    <span class="price-row__label">Shipping</span>
                                    <span class="price-row__value">€0.00</span>
                                </div>
                                <div class="price-row price-row--total">
                                    <span class="price-row__label">Total</span>
                                    <span class="price-row__value">€21,350.00</span>
                                </div>
                            </div>

                            <div class="order-summary__items">
                                <div class="cart-item">
                                    <div class="cart-item__image">
                                        <img src="<?= $this->asset('img/ecommerce/product-img.png') ?>" alt="Product"
                                            loading="lazy">
                                    </div>
                                    <div class="cart-item__details">
                                        <span class="cart-item__name">Apple iPhone 14 Pro Max</span>
                                        <span class="cart-item__variant">128GB, Space Black</span>
                                        <span class="cart-item__quantity">Qty: 1</span>
                                    </div>
                                    <div class="cart-item__price">$1,399.00</div>
                                </div>
                            </div>

                            <div class="order-summary__security">
                                <span class="security-badge">🔒 Secure Checkout</span>
                                <span class="security-badge__text">Your information is encrypted</span>
                            </div>
                        </div>
                    </aside>

                </div>
                <div class="checkout__step-nav">
                    <label for="step2" class="btn btn--primary btn--next" role="button"
                        aria-controls="checkout-step-address">Continue to address</label>
                </div>
            </div>
            <!-- ================================ -->
            <!-- STEP 2: ADDRESS                   -->
            <!-- ================================ -->
            <div class="checkout__step" data-step="2" role="tabpanel" id="checkout-step-address"
                aria-label="Address Step">
                <div class="checkout__step-content">
                    <div class="checkout__left">
                        <div class="address-section">
                            <h3 class="address-section__title">Shipping Address</h3>

                            <div class="address-section__saved" role="radiogroup" aria-label="Saved Addresses">
                                <div class="address-card address-card--selected">
                                    <div class="address-card__radio">
                                        <input type="radio" name="shippingAddress" id="address1" value="1" checked>
                                    </div>
                                    <div class="address-card__content">
                                        <div class="address-card__header">
                                            <span class="address-card__name">Home</span>
                                            <span class="address-card__tag">Default</span>
                                        </div>
                                        <address class="address-card__address">
                                            2118 Thornridge Cir.<br>
                                            Syracuse, CT 35624<br>
                                            United States
                                        </address>
                                        <div class="address-card__contact">
                                            <span class="address-card__phone">(209) 555-0104</span>
                                        </div>
                                    </div>
                                    <div class="address-card__actions">
                                        <button type="button" class="address-card__action address-card__action--edit"
                                            data-modal="addressModal" aria-label="Edit address">
                                            <svg class="icon icon--edit" aria-hidden="true">
                                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-edit">
                                                </use>
                                            </svg>
                                        </button>
                                        <button type="button" class="address-card__action address-card__action--delete"
                                            aria-label="Delete address">
                                            <svg class="icon icon--delete" aria-hidden="true">
                                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel">
                                                </use>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="address-card">
                                    <div class="address-card__radio">
                                        <input type="radio" name="shippingAddress" id="address2" value="2">
                                    </div>
                                    <div class="address-card__content">
                                        <div class="address-card__header">
                                            <span class="address-card__name">Office</span>
                                            <span class="address-card__tag address-card__tag--office">Office</span>
                                        </div>
                                        <address class="address-card__address">
                                            2715 Ash Dr.<br>
                                            San Jose, SD 83475<br>
                                            United States
                                        </address>
                                        <div class="address-card__contact">
                                            <span class="address-card__phone">(704) 555-0127</span>
                                        </div>
                                    </div>
                                    <div class="address-card__actions">
                                        <button type="button" class="address-card__action address-card__action--edit"
                                            data-modal="addressModal" aria-label="Edit address">
                                            <svg class="icon icon--edit" aria-hidden="true">
                                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-edit">
                                                </use>
                                            </svg>
                                        </button>
                                        <button type="button" class="address-card__action address-card__action--delete"
                                            aria-label="Delete address">
                                            <svg class="icon icon--delete" aria-hidden="true">
                                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel">
                                                </use>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn--outline btn--full address-section__add"
                                data-modal="addressModal">
                                <svg class="icon icon--plus" aria-hidden="true" width="20" height="20">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-plus-solid"></use>
                                </svg>
                                <span>Add New Address</span>
                            </button>

                            <div class="billing-section">
                                <h4 class="billing-section__title">Billing Address</h4>
                                <div class="billing-section__toggle">
                                    <label class="checkbox-field">
                                        <input type="checkbox" name="billingSameAsShipping" id="billingSameAsShipping"
                                            checked>
                                        <span class="checkbox-field__label">Same as shipping address</span>
                                    </label>
                                </div>
                                <div class="billing-section__fields" hidden>
                                    <div class="input-field">
                                        <input type="text" name="billingAddressLine1" id="billingAddressLine1"
                                            class="input-field__input" placeholder=" " autocomplete="address-line1">
                                        <label for="billingAddressLine1" class="input-field__label">Address Line
                                            1</label>
                                    </div>
                                    <div class="input-field">
                                        <input type="text" name="billingCity" id="billingCity"
                                            class="input-field__input" placeholder=" " autocomplete="address-level2">
                                        <label for="billingCity" class="input-field__label">City</label>
                                    </div>
                                    <div class="input-field-group">
                                        <div class="input-field">
                                            <select name="billingState" id="billingState" class="input-field__select">
                                                <option value="">Select State</option>
                                                <option value="CA">California</option>
                                                <option value="CT">Connecticut</option>
                                                <option value="SD">South Dakota</option>
                                            </select>
                                            <label for="billingState" class="input-field__label">State</label>
                                        </div>
                                        <div class="input-field">
                                            <input type="text" name="billingZipCode" id="billingZipCode"
                                                class="input-field__input" placeholder=" " autocomplete="postal-code">
                                            <label for="billingZipCode" class="input-field__label">Zip Code</label>
                                        </div>
                                    </div>
                                    <div class="input-field">
                                        <select name="billingCountry" id="billingCountry" class="input-field__select">
                                            <option value="">Select Country</option>
                                            <option value="US" selected>United States</option>
                                            <option value="CA">Canada</option>
                                        </select>
                                        <label for="billingCountry" class="input-field__label">Country</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <aside class="checkout__right order-summary" aria-label="Order Summary">
                        <h3 class="order-summary__title">Order Summary</h3>
                        <div class="order-summary__content">
                            <div class="order-summary__coupons">
                                <div class="coupon-field">
                                    <label for="discountCode" class="coupon-field__label">Discount Code</label>
                                    <div class="coupon-field__wrapper">
                                        <input type="text" name="discountCode" id="discountCode"
                                            class="coupon-field__input" placeholder="Enter discount code">
                                        <button type="button" class="coupon-field__apply">Apply</button>
                                    </div>
                                </div>
                            </div>

                            <div class="order-summary__prices">
                                <div class="price-row">
                                    <span class="price-row__label">Subtotal</span>
                                    <span class="price-row__value">€18,300.00</span>
                                </div>
                                <div class="price-row price-row--secondary">
                                    <span class="price-row__label">Estimated Tax</span>
                                    <span class="price-row__value">€3,050.00</span>
                                </div>
                                <div class="price-row price-row--secondary">
                                    <span class="price-row__label">Shipping</span>
                                    <span class="price-row__value">€0.00</span>
                                </div>
                                <div class="price-row price-row--total">
                                    <span class="price-row__label">Total</span>
                                    <span class="price-row__value">€21,350.00</span>
                                </div>
                            </div>

                            <div class="order-summary__items">
                                <div class="cart-item">
                                    <div class="cart-item__image">
                                        <img src="<?= $this->asset('img/ecommerce/product-img.png') ?>" alt="Product"
                                            loading="lazy">
                                    </div>
                                    <div class="cart-item__details">
                                        <span class="cart-item__name">Apple iPhone 14 Pro Max</span>
                                        <span class="cart-item__variant">128GB, Space Black</span>
                                        <span class="cart-item__quantity">Qty: 1</span>
                                    </div>
                                    <div class="cart-item__price">$1,399.00</div>
                                </div>
                            </div>

                            <div class="order-summary__security">
                                <span class="security-badge">🔒 Secure Checkout</span>
                                <span class="security-badge__text">Your information is encrypted</span>
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="checkout__step-nav">
                    <label for="step1" class="btn btn--outline btn--back" role="button"
                        aria-controls="checkout-step-options">Back</label>
                    <label for="step3" class="btn btn--primary btn--next" role="button"
                        aria-controls="checkout-step-shipping">Next: Shipping</label>
                </div>
            </div>

            <!-- ================================ -->
            <!-- STEP 3: SHIPPING METHOD          -->
            <!-- ================================ -->
            <div class="checkout__step" data-step="3" role="tabpanel" id="checkout-step-shipping"
                aria-label="Shipping Step">
                <div class="checkout__step-content">
                    <div class="checkout__left">
                        <div class="shipping-section">
                            <h3 class="shipping-section__title">Choose Shipping Method</h3>
                            <p class="shipping-section__subtitle">Estimated delivery times based on your location
                            </p>

                            <div class="shipping-section__methods" role="radiogroup" aria-label="Shipping Methods">
                                <label class="shipping-method shipping-method--selected">
                                    <input type="radio" name="shippingMethod" value="standard" checked>
                                    <div class="shipping-method__content">
                                        <div class="shipping-method__info">
                                            <span class="shipping-method__name">Standard Shipping</span>
                                            <span class="shipping-method__description">5-7 business days</span>
                                        </div>
                                        <div class="shipping-method__cost">Free</div>
                                        <div class="shipping-method__delivery">
                                            <span class="shipping-method__date">Expected: Oct 17, 2023</span>
                                        </div>
                                    </div>
                                </label>

                                <label class="shipping-method">
                                    <input type="radio" name="shippingMethod" value="express">
                                    <div class="shipping-method__content">
                                        <div class="shipping-method__info">
                                            <span class="shipping-method__name">Express Shipping</span>
                                            <span class="shipping-method__description">2-3 business days</span>
                                        </div>
                                        <div class="shipping-method__cost">$8.50</div>
                                        <div class="shipping-method__delivery">
                                            <span class="shipping-method__date">Expected: Oct 10, 2023</span>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            <div class="gift-section">
                                <h4 class="gift-section__title">Gift Options</h4>
                                <label class="checkbox-field">
                                    <input type="checkbox" name="isGift" id="isGift" value="1">
                                    <span class="checkbox-field__label">This order is a gift</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <aside class="checkout__right order-summary" aria-label="Order Summary">
                        <h3 class="order-summary__title">Order Summary</h3>
                        <div class="order-summary__content">
                            <div class="order-summary__prices">
                                <div class="price-row">
                                    <span class="price-row__label">Subtotal</span>
                                    <span class="price-row__value">€18,300.00</span>
                                </div>
                                <div class="price-row price-row--secondary">
                                    <span class="price-row__label">Estimated Tax</span>
                                    <span class="price-row__value">€3,050.00</span>
                                </div>
                                <div class="price-row price-row--secondary">
                                    <span class="price-row__label">Shipping</span>
                                    <span class="price-row__value">€0.00</span>
                                </div>
                                <div class="price-row price-row--total">
                                    <span class="price-row__label">Total</span>
                                    <span class="price-row__value">€21,350.00</span>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="checkout__step-nav">
                    <label for="step2" class="btn btn--outline btn--back" role="button"
                        aria-controls="checkout-step-address">Back</label>
                    <label for="step4" class="btn btn--primary btn--next" role="button"
                        aria-controls="checkout-step-payment">Next: Payment</label>
                </div>
            </div>

            <!-- ================================ -->
            <!-- STEP 4: PAYMENT                  -->
            <!-- ================================ -->
            <div class="checkout__step" data-step="4" role="tabpanel" id="checkout-step-payment"
                aria-label="Payment Step">
                <div class="checkout__step-content">
                    <div class="checkout__left">
                        <div class="payment-section">
                            <h3 class="payment-section__title">Payment Method</h3>

                            <div class="payment-section__tabs">
                                <input type="radio" name="paymentMethod" id="paymentCredit" value="credit" checked
                                    hidden>
                                <input type="radio" name="paymentMethod" id="paymentPaypal" value="paypal" hidden>
                                <input type="radio" name="paymentMethod" id="paymentBank" value="bank" hidden>

                                <div class="tabs__labels" role="tablist" aria-label="Payment methods">
                                    <label for="paymentCredit" class="tab__label tab__label--active" data-tab="credit"
                                        role="tab" aria-controls="payment-panel-credit" aria-selected="true">
                                        <svg class="icon icon--credit" aria-hidden="true" width="20" height="20">
                                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-credit-card">
                                            </use>
                                        </svg>
                                        Credit Card
                                    </label>

                                    <label for="paymentPaypal" class="tab__label" data-tab="paypal" role="tab"
                                        aria-controls="payment-panel-paypal">
                                        <svg class="icon icon--paypal" aria-hidden="true" width="20" height="20">
                                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-paypal">
                                            </use>
                                        </svg>
                                        PayPal
                                    </label>

                                    <label for="paymentBank" class="tab__label" data-tab="bank" role="tab"
                                        aria-controls="payment-panel-bank">
                                        <svg class="icon icon--bank" aria-hidden="true" width="20" height="20">
                                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-bank-transfer">
                                            </use>
                                        </svg>
                                        Bank Transfer
                                    </label>
                                </div>

                                <div class="tab__content" data-tab="credit" id="payment-panel-credit" role="tabpanel">
                                    <div class="credit-card-form">
                                        <div class="credit-card-form__cards">
                                            <img src="/public/assets/img/ecommerce/visa.png" alt="Visa"
                                                class="credit-card-form__card-icon">
                                            <img src="/public/assets/img/ecommerce/mastercard.png" alt="Mastercard"
                                                class="credit-card-form__card-icon">
                                        </div>

                                        <div class="input-field">
                                            <input type="text" name="cardName" id="cardName" class="input-field__input"
                                                placeholder=" " autocomplete="cc-name" required>
                                            <label for="cardName" class="input-field__label">Cardholder Name</label>
                                        </div>

                                        <div class="input-field">
                                            <input type="text" name="cardNumber" id="cardNumber"
                                                class="input-field__input" placeholder=" " autocomplete="cc-number"
                                                inputmode="numeric" required>
                                            <label for="cardNumber" class="input-field__label">Card Number</label>
                                        </div>

                                        <div class="input-field-group">
                                            <div class="input-field">
                                                <input type="text" name="cardExpiry" id="cardExpiry"
                                                    class="input-field__input" placeholder=" " autocomplete="cc-exp"
                                                    required>
                                                <label for="cardExpiry" class="input-field__label">Expiry
                                                    (MM/YY)</label>
                                            </div>
                                            <div class="input-field">
                                                <input type="text" name="cardCvv" id="cardCvv"
                                                    class="input-field__input" placeholder=" " autocomplete="cc-csc"
                                                    inputmode="numeric" required>
                                                <label for="cardCvv" class="input-field__label">CVV</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab__content" data-tab="paypal" id="payment-panel-paypal" role="tabpanel">
                                    <div class="paypal-form">
                                        <div class="paypal-form__hero">
                                            <div class="paypal-form__brand">
                                                <span class="paypal-form__badge">PayPal</span>
                                                <h4 class="paypal-form__title">Pay securely with your PayPal account
                                                </h4>
                                            </div>
                                            <p class="paypal-form__description">
                                                You&apos;ll be redirected to PayPal to review the order and confirm
                                                the
                                                payment in a secure checkout flow.
                                            </p>
                                        </div>

                                        <div class="paypal-form__summary">
                                            <div class="paypal-form__row">
                                                <span>Order total</span>
                                                <strong>€21,350.00</strong>
                                            </div>
                                            <div class="paypal-form__row">
                                                <span>Processing</span>
                                                <strong>Instant</strong>
                                            </div>
                                        </div>

                                        <div class="paypal-form__actions">
                                            <button type="button" class="btn btn--outline">Learn more</button>
                                            <button type="button" class="btn btn--primary">Continue with
                                                PayPal</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab__content" data-tab="bank" id="payment-panel-bank" role="tabpanel">
                                    <div class="bank-transfer-form">
                                        <div class="bank-transfer-form__hero">
                                            <div class="bank-transfer-form__icon">🏦</div>
                                            <h4 class="bank-transfer-form__title">Bank transfer</h4>
                                            <p class="bank-transfer-form__description">
                                                Choose this option if you prefer to complete the payment directly
                                                from
                                                your bank account.
                                            </p>
                                        </div>

                                        <div class="bank-transfer-form__details">
                                            <div class="bank-transfer-form__row">
                                                <span>Beneficiary</span>
                                                <strong>KNGELL ECOM LTD</strong>
                                            </div>
                                            <div class="bank-transfer-form__row">
                                                <span>IBAN</span>
                                                <strong>DE89 3704 0044 0532 0130 00</strong>
                                            </div>
                                            <div class="bank-transfer-form__row">
                                                <span>Reference</span>
                                                <strong>Order #24578</strong>
                                            </div>
                                        </div>

                                        <div class="bank-transfer-form__note">
                                            <p>We will send you the payment confirmation once the transfer is
                                                completed.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <aside class="checkout__right order-summary" aria-label="Order Summary">
                        <h3 class="order-summary__title">Order Summary</h3>
                        <div class="order-summary__content">
                            <div class="order-summary__prices">
                                <div class="price-row">
                                    <span class="price-row__label">Subtotal</span>
                                    <span class="price-row__value">€18,300.00</span>
                                </div>
                                <div class="price-row price-row--secondary">
                                    <span class="price-row__label">Estimated Tax</span>
                                    <span class="price-row__value">€3,050.00</span>
                                </div>
                                <div class="price-row price-row--total">
                                    <span class="price-row__label">Total</span>
                                    <span class="price-row__value">€21,350.00</span>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="checkout__step-nav">
                    <label for="step3" class="btn btn--outline btn--back" role="button"
                        aria-controls="checkout-step-shipping">Back</label>
                    <label for="step5" class="btn btn--primary btn--next" role="button"
                        aria-controls="checkout-step-review">Review Order</label>
                </div>
            </div>

            <!-- ================================ -->
            <!-- STEP 5: REVIEW                   -->
            <!-- ================================ -->
            <div class="checkout__step" data-step="5" role="tabpanel" id="checkout-step-review"
                aria-label="Review Step">
                <div class="checkout__step-content checkout__step-content--full">
                    <!-- ============================================
     ORDER REVIEW SECTION - PROFESSIONAL VERSION
     ============================================ -->
                    <div class="order-review" role="main" aria-label="Order Review">

                        <!-- HEADER -->
                        <header class="order-review__header">
                            <div class="order-review__header-content">
                                <h1 class="order-review__title">Review Your Order</h1>
                                <p class="order-review__subtitle">Please verify all information before placing your
                                    order</p>
                                <span class="order-review__order-number">Order #ORD-2024-00842</span>
                            </div>
                            <div class="order-review__security-badges">
                                <span class="security-badge" role="img" aria-label="Secure checkout">
                                    🔒 Secure Checkout
                                </span>
                                <span class="security-badge">256-bit SSL Encrypted</span>
                                <span class="security-badge">🔐 PCI Compliant</span>
                            </div>
                        </header>

                        <!-- ORDER ITEMS -->
                        <section class="order-review__section" aria-labelledby="items-heading">
                            <div class="section-header">
                                <h2 id="items-heading" class="section-header__title">Order Items (3)</h2>
                                <a href="/cart" class="section-header__edit" aria-label="Edit cart items">Edit Cart</a>
                            </div>

                            <div class="order-items" role="list">
                                <!-- Item 1 -->
                                <article class="order-item" role="listitem">
                                    <div class="order-item__image-wrapper">
                                        <img src="https://via.placeholder.com/80x80/4F46E5/FFFFFF?text=Product"
                                            alt="Premium Wireless Headphones - Black" class="order-item__image"
                                            loading="lazy">
                                    </div>
                                    <div class="order-item__details">
                                        <h3 class="order-item__name">Premium Wireless Headphones Pro</h3>
                                        <div class="order-item__meta">
                                            <span class="order-item__sku">SKU: WH-1000XM5</span>
                                            <span class="order-item__variant">Color: Black</span>
                                            <span class="order-item__variant">Size: One Size</span>
                                        </div>
                                        <div class="order-item__quantity">
                                            <span class="order-item__qty-label">Qty:</span>
                                            <span class="order-item__qty-value">1</span>
                                        </div>
                                    </div>
                                    <div class="order-item__pricing">
                                        <span class="order-item__price">€6,100.00</span>
                                        <span class="order-item__unit-price">€6,100.00 each</span>
                                    </div>
                                </article>

                                <!-- Item 2 -->
                                <article class="order-item" role="listitem">
                                    <div class="order-item__image-wrapper">
                                        <img src="https://via.placeholder.com/80x80/7C3AED/FFFFFF?text=Product"
                                            alt="Smart Watch Series 8 - Silver" class="order-item__image"
                                            loading="lazy">
                                    </div>
                                    <div class="order-item__details">
                                        <h3 class="order-item__name">Smart Watch Series 8</h3>
                                        <div class="order-item__meta">
                                            <span class="order-item__sku">SKU: SW-8-SIL</span>
                                            <span class="order-item__variant">Color: Silver</span>
                                            <span class="order-item__variant">Band: Sport Loop</span>
                                        </div>
                                        <div class="order-item__quantity">
                                            <span class="order-item__qty-label">Qty:</span>
                                            <span class="order-item__qty-value">2</span>
                                        </div>
                                    </div>
                                    <div class="order-item__pricing">
                                        <span class="order-item__price">€12,200.00</span>
                                        <span class="order-item__unit-price">€6,100.00 each</span>
                                    </div>
                                </article>

                                <!-- Item 3 -->
                                <article class="order-item" role="listitem">
                                    <div class="order-item__image-wrapper">
                                        <img src="https://via.placeholder.com/80x80/EC4899/FFFFFF?text=Product"
                                            alt="Portable Charger - 20000mAh" class="order-item__image" loading="lazy">
                                    </div>
                                    <div class="order-item__details">
                                        <h3 class="order-item__name">Portable Power Bank 20000mAh</h3>
                                        <div class="order-item__meta">
                                            <span class="order-item__sku">SKU: PB-20K</span>
                                            <span class="order-item__variant">Color: Space Gray</span>
                                        </div>
                                        <div class="order-item__quantity">
                                            <span class="order-item__qty-label">Qty:</span>
                                            <span class="order-item__qty-value">1</span>
                                        </div>
                                    </div>
                                    <div class="order-item__pricing">
                                        <span class="order-item__price">€0.00</span>
                                        <span class="order-item__unit-price order-item__unit-price--free">Free
                                            Gift</span>
                                    </div>
                                </article>
                            </div>

                            <!-- Order Summary Bar -->
                            <div class="order-items__summary">
                                <span class="order-items__summary-text">
                                    <strong>3 items</strong> in your cart
                                </span>
                                <span class="order-items__summary-total">
                                    Subtotal: <strong>€18,300.00</strong>
                                </span>
                            </div>
                        </section>

                        <!-- DELIVERY INFORMATION -->
                        <section class="order-review__section" aria-labelledby="delivery-heading">
                            <div class="section-header">
                                <h2 id="delivery-heading" class="section-header__title">Delivery Information</h2>
                            </div>

                            <div class="info-grid">
                                <!-- Shipping Address -->
                                <div class="info-card">
                                    <div class="info-card__header">
                                        <h3 class="info-card__title">Shipping Address</h3>
                                        <a href="/checkout/step2" class="info-card__edit"
                                            aria-label="Edit shipping address">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                            Edit
                                        </a>
                                    </div>
                                    <div class="info-card__body">
                                        <address class="info-card__address">
                                            <span class="info-card__recipient">John Doe</span>
                                            <span class="info-card__company">Acme Corporation</span>
                                            <span class="info-card__street">2118 Thornridge Cir.</span>
                                            <span class="info-card__city-state">Syracuse, CT 35624</span>
                                            <span class="info-card__country">United States</span>
                                            <span class="info-card__phone">+1 (555) 123-4567</span>
                                            <span class="info-card__email">john.doe@example.com</span>
                                        </address>
                                    </div>
                                    <div class="info-card__footer">
                                        <span class="info-card__badge info-card__badge--verified">✓ Verified
                                            Address</span>
                                    </div>
                                </div>

                                <!-- Shipping Method -->
                                <div class="info-card">
                                    <div class="info-card__header">
                                        <h3 class="info-card__title">Shipping Method</h3>
                                        <a href="/checkout/step3" class="info-card__edit"
                                            aria-label="Edit shipping method">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                            Edit
                                        </a>
                                    </div>
                                    <div class="info-card__body">
                                        <div class="shipping-method">
                                            <div class="shipping-method__icon" aria-hidden="true">📦</div>
                                            <div class="shipping-method__details">
                                                <span class="shipping-method__name">Standard Shipping</span>
                                                <span class="shipping-method__delivery">
                                                    Estimated delivery: <strong>December 15-17, 2024</strong>
                                                </span>
                                                <span class="shipping-method__duration">3-5 business days</span>
                                            </div>
                                            <span
                                                class="shipping-method__price shipping-method__price--free">Free</span>
                                        </div>
                                        <div class="shipping-method__tracking">
                                            <span class="shipping-method__tracking-label">Tracking:</span>
                                            <span class="shipping-method__tracking-number">Will be provided upon
                                                shipment</span>
                                        </div>
                                    </div>
                                    <div class="info-card__footer">
                                        <span class="info-card__badge info-card__badge--info">🚚 Ships from US
                                            warehouse</span>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- PAYMENT INFORMATION -->
                        <section class="order-review__section" aria-labelledby="payment-heading">
                            <div class="section-header">
                                <h2 id="payment-heading" class="section-header__title">Payment Information</h2>
                            </div>

                            <div class="info-grid">
                                <!-- Payment Method -->
                                <div class="info-card">
                                    <div class="info-card__header">
                                        <h3 class="info-card__title">Payment Method</h3>
                                        <a href="/checkout/step4" class="info-card__edit"
                                            aria-label="Edit payment method">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                            Edit
                                        </a>
                                    </div>
                                    <div class="info-card__body">
                                        <div class="payment-method">
                                            <div class="payment-method__card">
                                                <span class="payment-method__icon" aria-hidden="true">
                                                    <svg width="40" height="28" viewBox="0 0 40 28" fill="none"
                                                        role="img" aria-label="Visa">
                                                        <rect width="40" height="28" rx="4" fill="#1434CB" />
                                                        <path d="M8 19H12L14 9H10L8 19Z" fill="white" />
                                                        <path d="M18 19H22L24 9H20L18 19Z" fill="white" />
                                                        <path d="M28 19H32L34 9H30L28 19Z" fill="white" />
                                                    </svg>
                                                </span>
                                                <div class="payment-method__details">
                                                    <span class="payment-method__type">Visa Credit Card</span>
                                                    <span class="payment-method__number">•••• •••• •••• 4242</span>
                                                    <span class="payment-method__expiry">Expires: 12/2026</span>
                                                </div>
                                                <span class="payment-method__status payment-method__status--valid">✓
                                                    Valid</span>
                                            </div>
                                        </div>
                                        <div class="payment-method__billing">
                                            <span class="payment-method__billing-label">Billing Address:</span>
                                            <span class="payment-method__billing-address">Same as shipping
                                                address</span>
                                        </div>
                                    </div>
                                    <div class="info-card__footer">
                                        <span class="info-card__badge info-card__badge--secure">🔒 Secure payment
                                            processing</span>
                                    </div>
                                </div>

                                <!-- Payment Summary -->
                                <div class="info-card info-card--highlight">
                                    <div class="info-card__header">
                                        <h3 class="info-card__title">Payment Summary</h3>
                                    </div>
                                    <div class="info-card__body">
                                        <dl class="payment-summary">
                                            <div class="payment-summary__row">
                                                <dt>Subtotal</dt>
                                                <dd>€18,300.00</dd>
                                            </div>
                                            <div class="payment-summary__row">
                                                <dt>Shipping</dt>
                                                <dd class="payment-summary__value--free">Free</dd>
                                            </div>
                                            <div class="payment-summary__row">
                                                <dt>Estimated Tax <span class="payment-summary__tooltip"
                                                        title="Tax is estimated and may vary based on your location">ⓘ</span>
                                                </dt>
                                                <dd>€3,050.00</dd>
                                            </div>
                                            <div class="payment-summary__row payment-summary__row--discount">
                                                <dt>Discount</dt>
                                                <dd class="payment-summary__value--discount">-€500.00</dd>
                                            </div>
                                            <div class="payment-summary__divider"></div>
                                            <div class="payment-summary__row payment-summary__row--total">
                                                <dt><strong>Total</strong></dt>
                                                <dd><strong>€20,850.00</strong></dd>
                                            </div>
                                            <div class="payment-summary__row payment-summary__row--currency">
                                                <dd>EUR (€)</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- TERMS & CONSENT -->
                        <section class="order-review__section" aria-labelledby="consent-heading">
                            <div class="consent-section">
                                <h2 id="consent-heading" class="visually-hidden">Terms and Consent</h2>

                                <div class="consent-group">
                                    <!-- Terms Checkbox -->
                                    <label class="checkbox-field">
                                        <input type="checkbox" name="termsAccepted" id="termsAccepted" required
                                            aria-describedby="terms-error">
                                        <span class="checkbox-field__custom" aria-hidden="true"></span>
                                        <span class="checkbox-field__label">
                                            I agree to the
                                            <a href="/terms" target="_blank" rel="noopener noreferrer">Terms &amp;
                                                Conditions</a>
                                            and
                                            <a href="/privacy" target="_blank" rel="noopener noreferrer">Privacy
                                                Policy</a>
                                            <span class="required" aria-hidden="true">*</span>
                                        </span>
                                    </label>
                                    <div id="terms-error" class="error-message" role="alert" aria-live="polite"></div>
                                </div>

                                <div class="consent-group">
                                    <label class="checkbox-field">
                                        <input type="checkbox" name="marketingConsent" id="marketingConsent">
                                        <span class="checkbox-field__custom" aria-hidden="true"></span>
                                        <span class="checkbox-field__label">
                                            Yes, I would like to receive order updates and exclusive offers via email
                                        </span>
                                    </label>
                                </div>

                                <div class="consent-group">
                                    <label class="checkbox-field">
                                        <input type="checkbox" name="gdprConsent" id="gdprConsent" required
                                            aria-describedby="gdpr-error">
                                        <span class="checkbox-field__custom" aria-hidden="true"></span>
                                        <span class="checkbox-field__label">
                                            I confirm that I have read and understand the
                                            <a href="/privacy" target="_blank" rel="noopener noreferrer">GDPR Privacy
                                                Notice</a>
                                            <span class="required" aria-hidden="true">*</span>
                                        </span>
                                    </label>
                                    <div id="gdpr-error" class="error-message" role="alert" aria-live="polite"></div>
                                </div>

                                <div class="consent-group consent-group--highlight">
                                    <p class="consent-group__notice">
                                        <span aria-hidden="true">ℹ️</span>
                                        By placing this order, you agree to our terms and confirm that all information
                                        provided is accurate.
                                    </p>
                                </div>
                            </div>
                        </section>

                        <!-- ACTIONS -->
                        <div class="order-review__actions">
                            <div class="order-review__actions-left">
                                <a href="/checkout/step4" class="btn btn--back" aria-label="Go back to previous step">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" aria-hidden="true">
                                        <path d="M19 12H5M12 19l-7-7 7-7" />
                                    </svg>
                                    Back
                                </a>
                                <a href="/cart" class="btn btn--outline">Edit Cart</a>
                            </div>

                            <div class="order-review__actions-right">
                                <button type="submit" class="btn btn--primary btn--submit" id="placeOrderBtn">
                                    <span class="btn__text">Place Order</span>
                                    <span class="btn__amount">€20,850.00</span>
                                    <span class="btn__loading" hidden>
                                        <span class="spinner" aria-hidden="true"></span>
                                        Processing...
                                    </span>
                                </button>
                                <p class="order-review__secure-notice">
                                    <span aria-hidden="true">🔒</span>
                                    Your payment is secure. We never store your credit card details.
                                </p>
                            </div>
                        </div>

                        <!-- FOOTER NOTE -->
                        <footer class="order-review__footer">
                            <p class="order-review__footer-text">
                                Need help? <a href="/contact">Contact our support team</a> or call +1 (555) 123-4567
                            </p>
                            <p class="order-review__footer-text order-review__footer-text--small">
                                Order confirmation will be sent to john.doe@example.com
                            </p>
                        </footer>
                    </div>
                </div>
            </div>
        </form>
    </section>

    <!-- ================================ -->
    <!-- ADDRESS MODAL                    -->
    <!-- ================================ -->
    <section class="modal-section" aria-label="Address Modal">
        <input type="checkbox" id="addressModalToggle" class="modal-section__toggle" hidden>
        <div class="modal-section__overlay" role="dialog" aria-modal="true">
            <div class="modal-section__content">
                <div class="modal-section__header">
                    <h2 id="modalTitle">Add/Edit Address</h2>
                    <label for="addressModalToggle" class="modal-section__close" aria-label="Close modal">
                        <svg class="icon icon--close" aria-hidden="true" width="24" height="24">
                            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-close"></use>
                        </svg>
                    </label>
                </div>

                <form class="address-form" id="addressForm" novalidate>
                    <div class="address-form__fields">
                        <div class="input-field">
                            <input type="text" name="addressFullName" id="addressFullName" class="input-field__input"
                                placeholder=" " required>
                            <label for="addressFullName" class="input-field__label">Full Name</label>
                        </div>

                        <div class="input-field">
                            <input type="text" name="addressLine1" id="addressLine1" class="input-field__input"
                                placeholder=" " required>
                            <label for="addressLine1" class="input-field__label">Address Line 1</label>
                        </div>

                        <div class="input-field">
                            <input type="text" name="addressCity" id="addressCity" class="input-field__input"
                                placeholder=" " required>
                            <label for="addressCity" class="input-field__label">City</label>
                        </div>

                        <div class="input-field-group">
                            <div class="input-field">
                                <select name="addressState" id="addressState" class="input-field__select">
                                    <option value="">Select State</option>
                                    <option value="CA">California</option>
                                    <option value="CT">Connecticut</option>
                                    <option value="SD">South Dakota</option>
                                </select>
                                <label for="addressState" class="input-field__label">State</label>
                            </div>

                            <div class="input-field">
                                <input type="text" name="addressZipCode" id="addressZipCode" class="input-field__input"
                                    placeholder=" " required>
                                <label for="addressZipCode" class="input-field__label">Zip Code</label>
                            </div>
                        </div>

                        <div class="input-field">
                            <select name="addressCountry" id="addressCountry" class="input-field__select">
                                <option value="">Select Country</option>
                                <option value="US" selected>United States</option>
                                <option value="CA">Canada</option>
                            </select>
                            <label for="addressCountry" class="input-field__label">Country</label>
                        </div>

                        <div class="input-field">
                            <input type="tel" name="addressPhone" id="addressPhone" class="input-field__input"
                                placeholder=" " required>
                            <label for="addressPhone" class="input-field__label">Phone Number</label>
                        </div>
                    </div>

                    <div class="address-form__actions">
                        <label for="addressModalToggle" class="btn btn--outline">Cancel</label>
                        <button type="submit" class="btn btn--primary">Save Address</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</main>
<?php $this->end(); ?>

<?php $this->start('footer') ?>
<!----------Custom--------->
<?= $this->js('path') ?>

<?php $this->end(); ?>