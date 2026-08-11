<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<style>
/* ===== RESET & BASE ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
    background: #f7f9fc;
    color: #1a1e2b;
    line-height: 1.5;
    padding: 2rem 1rem;
    display: flex;
    justify-content: center;
}

.container {
    max-width: 1200px;
    width: 100%;
}

/* ===== TYPOGRAPHY ===== */
h3,
h4,
h6 {
    font-weight: 600;
    letter-spacing: -0.01em;
}

/* ===== BUTTONS ===== */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-weight: 500;
    font-size: 0.95rem;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    border: 1px solid transparent;
    cursor: pointer;
    transition: 0.2s ease;
    background: transparent;
    color: inherit;
    text-decoration: none;
}

.btn--primary {
    background: #1a1e2b;
    color: #fff;
    border-color: #1a1e2b;
}

.btn--primary:hover {
    background: #2d3348;
    border-color: #2d3348;
}

.btn--outline {
    background: transparent;
    color: #1a1e2b;
    border-color: #d0d5dd;
}

.btn--outline:hover {
    background: #f0f2f5;
    border-color: #b0b8c4;
}

.btn--secondary {
    background: #eef2f6;
    color: #1a1e2b;
    border-color: #d0d5dd;
}

.btn--secondary:hover {
    background: #e2e8f0;
}

.btn--success {
    background: #0b7e4b;
    color: #fff;
    border-color: #0b7e4b;
}

.btn--success:hover {
    background: #0a6a3e;
}

.btn--md {
    padding: 0.6rem 1.5rem;
}

.btn--lg {
    padding: 0.8rem 2.5rem;
    font-size: 1.05rem;
}

.btn--full {
    width: 100%;
    justify-content: center;
}

.btn--icon-only {
    padding: 0.3rem 0.5rem;
    border: none;
    background: transparent;
    color: #5b677b;
    border-radius: 6px;
}

.btn--icon-only:hover {
    background: #eef2f6;
    color: #1a1e2b;
}

/* ===== CHECKOUT LAYOUT ===== */
.checkout__body {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
    padding: 2rem;
}

/* ===== PROGRESS BAR ===== */
.checkout__progress {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2.5rem;
    padding: 0 0.5rem;
}

.progress__step {
    display: flex;
    align-items: center;
    flex: 1;
    position: relative;
}

.progress__step-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background: #f1f4f9;
    padding: 0.5rem 1rem 0.5rem 0.75rem;
    border-radius: 40px;
    transition: 0.2s;
    width: 100%;
}

.progress__step--active .progress__step-content {
    background: #1a1e2b;
    color: #fff;
}

.progress__icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    flex-shrink: 0;
    position: relative;
}

.progress__step--active .progress__icon-wrapper {
    background: rgba(255, 255, 255, 0.2);
}

.progress__step-number {
    font-size: 0.75rem;
    font-weight: 600;
    position: absolute;
}

.progress__step--active .progress__step-number {
    color: #fff;
}

.progress__step:not(.progress__step--active) .progress__step-number {
    color: #5b677b;
}

.progress__text-wrapper {
    display: flex;
    flex-direction: column;
    line-height: 1.3;
    flex: 1;
}

.progress__step-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    opacity: 0.6;
}

.progress__step--active .progress__step-label {
    opacity: 0.7;
}

.progress__step-title {
    font-weight: 600;
    font-size: 0.9rem;
}

.progress__connector {
    flex: 1;
    height: 2px;
    background: #dde1e8;
    margin: 0 0.5rem;
    min-width: 1rem;
}

.progress__step--active+.progress__connector {
    background: #1a1e2b;
}

/* ===== CHECKOUT STEPS - CSS-ONLY TOGGLES ===== */
/* Hide all steps by default */
.checkout__step {
    display: none;
}

/* Show step when its radio is checked */
#step1:checked~.checkout__step[data-step="1"],
#step2:checked~.checkout__step[data-step="2"] {
    display: block;
    animation: fadeUp 0.25s ease;
}

/* Progress step active states */
#step1:checked~.checkout__progress .progress__step[data-step="1"],
#step2:checked~.checkout__progress .progress__step[data-step="2"] {
    background: #1a1e2b;
    color: #fff;
}

/* Hide the radio inputs */
.step-radio {
    display: none;
}

@keyframes fadeUp {
    0% {
        opacity: 0;
        transform: translateY(10px);
    }

    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.checkout__step-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
    margin-top: 1rem;
}

@media (max-width: 900px) {
    .checkout__step-content {
        grid-template-columns: 1fr;
    }
}

/* ===== OPTIONS ===== */
.checkout-options__group {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-top: 1.5rem;
}

.option-card {
    display: block;
    border: 2px solid #e4e8ef;
    border-radius: 16px;
    padding: 1.5rem;
    cursor: pointer;
    transition: 0.2s;
    background: #fafcff;
}

.option-card:hover {
    border-color: #b8c2d4;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

/* Selected state via radio */
.checkout-options__group input[type="radio"] {
    display: none;
}

.checkout-options__group input[type="radio"]:checked+.option-card {
    border-color: #1a1e2b;
    background: #f5f7fb;
    box-shadow: 0 4px 12px rgba(26, 30, 43, 0.08);
}

.options__content {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.options__icon {
    font-size: 2rem;
}

.options__title {
    font-weight: 600;
    font-size: 1.05rem;
}

.options__description {
    color: #4d5668;
    font-size: 0.9rem;
}

/* ===== ADDRESS SELECTION ===== */
.address-selection {
    margin-top: 0.5rem;
}

.address-selection__subtitle {
    color: #4d5668;
    margin-bottom: 1rem;
}

.address-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

@media (max-width: 700px) {
    .address-grid {
        grid-template-columns: 1fr;
    }
}

.address-section {
    background: #fafcff;
    border-radius: 12px;
    padding: 1rem;
    border: 2px solid #e4e8ef;
}

.address-section--shipping {
    border-color: #d1fae5;
}

.address-section--billing {
    border-color: #dbeafe;
}

.address-section__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e4e8ef;
}

.address-section__title {
    font-size: 0.95rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.address-section__title .icon {
    font-size: 1.2rem;
}

.address-section__badge {
    font-size: 0.65rem;
    background: #e4e8ef;
    padding: 0.1rem 0.6rem;
    border-radius: 20px;
    font-weight: 500;
    color: #4d5668;
}

.address-section__add-btn {
    font-size: 0.8rem;
    padding: 0.2rem 0.6rem;
    border: 1px dashed #b8c2d4;
    border-radius: 6px;
    background: transparent;
    color: #4d5668;
    cursor: pointer;
    transition: 0.2s;
}

.address-section__add-btn:hover {
    border-color: #1a1e2b;
    color: #1a1e2b;
    background: #f0f2f5;
}

/* Address list - CSS only selection */
.address-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    min-height: 60px;
}

.address-item {
    border: 2px solid #e4e8ef;
    border-radius: 10px;
    padding: 0.75rem;
    background: #fff;
    transition: 0.2s;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    cursor: pointer;
    position: relative;
}

.address-item:hover {
    border-color: #b8c2d4;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

/* Selected state via radio */
.address-item input[type="radio"] {
    display: none;
}

.address-item input[type="radio"]:checked+.address-item-content {
    /* The parent gets the selected class via the label */
}

.address-item:has(input[type="radio"]:checked) {
    border-color: #1a1e2b;
    background: #f5f7fb;
    box-shadow: 0 2px 12px rgba(26, 30, 43, 0.1);
}

.address-item:has(input[type="radio"]:checked)::after {
    content: '✓';
    position: absolute;
    top: -6px;
    right: -6px;
    background: #1a1e2b;
    color: #fff;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 700;
}

.address-section--shipping .address-item:has(input[type="radio"]:checked) {
    border-color: #0b7e4b;
}

.address-section--shipping .address-item:has(input[type="radio"]:checked)::after {
    background: #0b7e4b;
}

.address-section--billing .address-item:has(input[type="radio"]:checked) {
    border-color: #2563eb;
}

.address-section--billing .address-item:has(input[type="radio"]:checked)::after {
    background: #2563eb;
}

.address-item-content {
    flex: 1;
    min-width: 0;
}

.address-item-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.15rem;
}

.address-item-header h4 {
    font-size: 0.85rem;
    font-weight: 600;
    margin: 0;
}

.address-tag {
    font-size: 0.6rem;
    padding: 0.05rem 0.4rem;
    border-radius: 20px;
    font-weight: 500;
    background: #e4e8ef;
    color: #4d5668;
}

.address-tag--default {
    background: #1a1e2b;
    color: #fff;
}

.address-details {
    font-size: 0.8rem;
    color: #4d5668;
    line-height: 1.4;
}

.address-details .address-line {
    display: block;
}

.address-actions {
    display: flex;
    gap: 0.1rem;
    flex-shrink: 0;
}

.icon-btn {
    background: transparent;
    border: none;
    padding: 0.2rem 0.3rem;
    border-radius: 4px;
    cursor: pointer;
    color: #5b677b;
    transition: 0.2s;
    font-size: 0.75rem;
}

.icon-btn:hover {
    background: #eef2f6;
    color: #1a1e2b;
}

.icon-btn.delete:hover {
    background: #fee2e2;
    color: #dc2626;
}

.icon-btn svg {
    width: 0.9rem;
    height: 0.9rem;
    display: block;
}

.empty-address {
    color: #6f7a8f;
    font-size: 0.85rem;
    padding: 1rem 0;
    text-align: center;
}

/* ===== ORDER SUMMARY ===== */
.order-summary {
    background: #f5f7fb;
    border-radius: 16px;
    padding: 1.5rem;
    align-self: start;
    position: sticky;
    top: 1rem;
}

.order-summary .title {
    font-size: 1.1rem;
    margin-bottom: 1rem;
}

.subtotal__price {
    border-top: 1px solid #dde1e8;
    padding-top: 0.8rem;
    margin-top: 0.8rem;
}

.subtotal__price--items,
.taxes-text {
    display: flex;
    justify-content: space-between;
    padding: 0.3rem 0;
}

.taxes-text {
    font-size: 0.9rem;
    color: #4d5668;
}

.total-price {
    display: flex;
    justify-content: space-between;
    font-weight: 700;
    font-size: 1.15rem;
    border-top: 2px solid #dde1e8;
    padding-top: 1rem;
    margin-top: 0.5rem;
}

/* ===== STEP NAV ===== */
.checkout__step-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e4e8ef;
    gap: 1rem;
}

/* ===== MODAL (CSS-Only) ===== */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 1rem;
    visibility: hidden;
    opacity: 0;
    transition: 0.2s;
    pointer-events: none;
}

.modal-overlay:target {
    visibility: visible;
    opacity: 1;
    pointer-events: auto;
}

.modal {
    background: #fff;
    border-radius: 24px;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    padding: 2rem;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    transform: scale(0.96);
    transition: 0.2s;
}

.modal-overlay:target .modal {
    transform: scale(1);
}

.modal-close-btn {
    float: right;
    background: transparent;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #5b677b;
    padding: 0.25rem;
    line-height: 1;
    text-decoration: none;
}

.modal-close-btn:hover {
    color: #1a1e2b;
}

.modal-header__title {
    font-size: 1.3rem;
    margin-bottom: 0.25rem;
}

.modal-header__subtitle {
    color: #4d5668;
}

.modal-body {
    margin: 1.5rem 0;
}

.modal-footer .buttons {
    display: flex;
    justify-content: flex-end;
    gap: 0.8rem;
}

/* ===== FORM FIELDS ===== */
.form-row {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    margin-bottom: 0.8rem;
}

.form-row.horizontal {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.8rem;
}

@media (max-width: 500px) {
    .form-row.horizontal {
        grid-template-columns: 1fr;
    }
}

.input-field {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.input-field__body {
    position: relative;
}

.input-field__input-container {
    position: relative;
}

.input-field__input,
.input-field__select {
    width: 100%;
    padding: 0.7rem 0.9rem;
    border: 1.5px solid #d0d5dd;
    border-radius: 10px;
    font-size: 0.95rem;
    background: #fff;
    transition: 0.2s;
    font-family: inherit;
    appearance: none;
    -webkit-appearance: none;
}

.input-field__input:focus,
.input-field__select:focus {
    border-color: #1a1e2b;
    outline: none;
    box-shadow: 0 0 0 3px rgba(26, 30, 43, 0.1);
}

.input-field__label {
    position: absolute;
    left: 0.9rem;
    top: 0.7rem;
    font-size: 0.95rem;
    color: #6f7a8f;
    pointer-events: none;
    transition: 0.15s ease;
    background: #fff;
    padding: 0 0.25rem;
}

.input-field__input:focus+.input-field__label,
.input-field__input:not(:placeholder-shown)+.input-field__label,
.input-field__select:focus+.input-field__label,
.input-field__select:not(:placeholder-shown)+.input-field__label {
    transform: translateY(-0.65rem) scale(0.85);
    color: #1a1e2b;
}

.input-field__select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%235b677b' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    padding-right: 2.5rem;
}

.checkbox-row {
    margin-bottom: 0.5rem;
}

.input-field__checkbox {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    cursor: pointer;
    font-size: 0.95rem;
}

.input-field__checkbox-input {
    width: 1.1rem;
    height: 1.1rem;
    accent-color: #1a1e2b;
    cursor: pointer;
}

.highlighted {
    background: #f5f7fb;
    padding: 0.8rem 1rem;
    border-radius: 12px;
    margin-top: 0.5rem;
}

/* ===== UTILITY ===== */
.text-center {
    text-align: center;
}

.mt-1 {
    margin-top: 0.5rem;
}
</style>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">

    <div class="container checkout__body">

        <!-- ===== PROGRESS ===== -->
        <div class="checkout__progress" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="2">
            <div class="progress__step progress__step--active" data-step="1">
                <div class="progress__step-content">
                    <div class="progress__icon-wrapper">
                        <span class="progress__step-number">1</span>
                    </div>
                    <div class="progress__text-wrapper">
                        <span class="progress__step-label">Step 1</span>
                        <span class="progress__step-title">Checkout Type</span>
                    </div>
                </div>
                <div class="progress__connector" aria-hidden="true"></div>
            </div>
            <div class="progress__step" data-step="2">
                <div class="progress__step-content">
                    <div class="progress__icon-wrapper">
                        <span class="progress__step-number">2</span>
                    </div>
                    <div class="progress__text-wrapper">
                        <span class="progress__step-label">Step 2</span>
                        <span class="progress__step-title">Address</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== FORM ===== -->
        <form id="checkoutForm" method="POST">
            <input type="hidden" name="csrfToken" value="test">
            <input type="hidden" name="frm_name" value="checkout-frm">

            <!-- Step radios (hidden) -->
            <input type="radio" class="step-radio" id="step1" name="step" value="step1" checked>
            <input type="radio" class="step-radio" id="step2" name="step" value="step2">

            <!-- Step 1: Options -->
            <div class="checkout__step" data-step="1" role="tabpanel">
                <div class="checkout__step-content">
                    <div class="checkout__left">
                        <div class="checkout-options">
                            <h3 style="margin-bottom:0.25rem;">How would you like to checkout?</h3>
                            <p style="color:#4d5668; margin-bottom:1rem;">Choose the option that works best for you.</p>

                            <div class="checkout-options__group">
                                <input type="radio" id="checkoutType-guest" name="checkoutType" value="guest" checked>
                                <label class="option-card" for="checkoutType-guest">
                                    <span class="options__content">
                                        <span class="options__icon">🛒</span>
                                        <span class="options__title">Continue as guest</span>
                                        <span class="options__description">No account needed. Fastest way to place your
                                            order.</span>
                                    </span>
                                </label>

                                <input type="radio" id="checkoutType-login" name="checkoutType" value="login">
                                <label class="option-card" for="checkoutType-login">
                                    <span class="options__content">
                                        <span class="options__icon">👤</span>
                                        <span class="options__title">Sign in or create an account</span>
                                        <span class="options__description">Track orders, save addresses and get faster
                                            checkout later.</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <aside class="order-summary">
                        <h4 class="title">Order Summary</h4>
                        <div class="subtotal__price">
                            <div class="subtotal__price--items">
                                <span>Subtotal</span>
                                <span><strong>€18,300.00</strong></span>
                            </div>
                            <div class="taxes-text">
                                <span>Estimated Tax</span>
                                <span>€3,050.00</span>
                            </div>
                            <div class="taxes-text">
                                <span>Shipping</span>
                                <span>€0.00</span>
                            </div>
                        </div>
                        <div class="total-price">
                            <span>Total</span>
                            <span>€21,350.00</span>
                        </div>
                    </aside>
                </div>

                <div class="checkout__step-nav">
                    <span></span>
                    <label class="btn btn--primary btn--md" for="step2">Continue →</label>
                </div>
            </div>

            <!-- Step 2: Address -->
            <div class="checkout__step" data-step="2" role="tabpanel">
                <div class="checkout__step-content">
                    <div class="checkout__left">
                        <div class="address-selection">
                            <h3 style="margin-bottom:0.25rem;">Select Addresses</h3>
                            <p class="address-selection__subtitle">Choose a shipping address and a billing address for
                                your order.</p>

                            <div class="address-grid">
                                <!-- Shipping Address Section -->
                                <div class="address-section address-section--shipping">
                                    <div class="address-section__header">
                                        <span class="address-section__title">
                                            <span class="icon">📦</span> Shipping Address
                                            <span class="address-section__badge">Required</span>
                                        </span>
                                        <a href="#addressModal" class="address-section__add-btn">+ Add</a>
                                    </div>
                                    <div class="address-list" id="shippingList">
                                        <!-- Shipping addresses -->
                                        <label class="address-item address-item--selected">
                                            <input type="radio" name="shippingAddress" value="1" checked>
                                            <span class="address-item-content">
                                                <span class="address-item-header">
                                                    <h4>Sarah Johnson</h4>
                                                    <span class="address-tag address-tag--default">Default</span>
                                                </span>
                                                <span class="address-details">
                                                    <span class="address-line">742 Evergreen Terrace, Apt 4B</span>
                                                    <span class="address-line">Springfield, IL 62701</span>
                                                </span>
                                            </span>
                                            <span class="address-actions">
                                                <a href="#addressModal" class="icon-btn" aria-label="Edit">
                                                    <svg viewBox="0 0 24 24">
                                                        <path
                                                            d="M17 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h10zm-8 12l-2 4 4-2 8-8-2-2-8 8z"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </a>
                                                <button class="icon-btn delete" aria-label="Delete" type="button">
                                                    <svg viewBox="0 0 24 24">
                                                        <path
                                                            d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </label>

                                        <label class="address-item">
                                            <input type="radio" name="shippingAddress" value="2">
                                            <span class="address-item-content">
                                                <span class="address-item-header">
                                                    <h4>Sarah Johnson</h4>
                                                    <span class="address-tag">Work</span>
                                                </span>
                                                <span class="address-details">
                                                    <span class="address-line">1000 Innovation Drive, Suite 300</span>
                                                    <span class="address-line">Chicago, IL 60607</span>
                                                </span>
                                            </span>
                                            <span class="address-actions">
                                                <a href="#addressModal" class="icon-btn" aria-label="Edit">
                                                    <svg viewBox="0 0 24 24">
                                                        <path
                                                            d="M17 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h10zm-8 12l-2 4 4-2 8-8-2-2-8 8z"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </a>
                                                <button class="icon-btn delete" aria-label="Delete" type="button">
                                                    <svg viewBox="0 0 24 24">
                                                        <path
                                                            d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </label>

                                        <label class="address-item">
                                            <input type="radio" name="shippingAddress" value="3">
                                            <span class="address-item-content">
                                                <span class="address-item-header">
                                                    <h4>Sarah Johnson</h4>
                                                    <span class="address-tag">Vacation</span>
                                                </span>
                                                <span class="address-details">
                                                    <span class="address-line">45 Ocean View Blvd</span>
                                                    <span class="address-line">Miami Beach, FL 33139</span>
                                                </span>
                                            </span>
                                            <span class="address-actions">
                                                <a href="#addressModal" class="icon-btn" aria-label="Edit">
                                                    <svg viewBox="0 0 24 24">
                                                        <path
                                                            d="M17 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h10zm-8 12l-2 4 4-2 8-8-2-2-8 8z"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </a>
                                                <button class="icon-btn delete" aria-label="Delete" type="button">
                                                    <svg viewBox="0 0 24 24">
                                                        <path
                                                            d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Billing Address Section -->
                                <div class="address-section address-section--billing">
                                    <div class="address-section__header">
                                        <span class="address-section__title">
                                            <span class="icon">💳</span> Billing Address
                                            <span class="address-section__badge">Required</span>
                                        </span>
                                        <a href="#addressModal" class="address-section__add-btn">+ Add</a>
                                    </div>
                                    <div class="address-list" id="billingList">
                                        <!-- Billing addresses -->
                                        <label class="address-item">
                                            <input type="radio" name="billingAddress" value="1">
                                            <span class="address-item-content">
                                                <span class="address-item-header">
                                                    <h4>Sarah Johnson</h4>
                                                </span>
                                                <span class="address-details">
                                                    <span class="address-line">742 Evergreen Terrace, Apt 4B</span>
                                                    <span class="address-line">Springfield, IL 62701</span>
                                                </span>
                                            </span>
                                            <span class="address-actions">
                                                <a href="#addressModal" class="icon-btn" aria-label="Edit">
                                                    <svg viewBox="0 0 24 24">
                                                        <path
                                                            d="M17 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h10zm-8 12l-2 4 4-2 8-8-2-2-8 8z"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </a>
                                                <button class="icon-btn delete" aria-label="Delete" type="button">
                                                    <svg viewBox="0 0 24 24">
                                                        <path
                                                            d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </label>

                                        <label class="address-item address-item--selected">
                                            <input type="radio" name="billingAddress" value="2" checked>
                                            <span class="address-item-content">
                                                <span class="address-item-header">
                                                    <h4>Sarah Johnson</h4>
                                                    <span class="address-tag address-tag--default">Default</span>
                                                </span>
                                                <span class="address-details">
                                                    <span class="address-line">1000 Innovation Drive, Suite 300</span>
                                                    <span class="address-line">Chicago, IL 60607</span>
                                                </span>
                                            </span>
                                            <span class="address-actions">
                                                <a href="#addressModal" class="icon-btn" aria-label="Edit">
                                                    <svg viewBox="0 0 24 24">
                                                        <path
                                                            d="M17 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h10zm-8 12l-2 4 4-2 8-8-2-2-8 8z"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </a>
                                                <button class="icon-btn delete" aria-label="Delete" type="button">
                                                    <svg viewBox="0 0 24 24">
                                                        <path
                                                            d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </label>

                                        <label class="address-item">
                                            <input type="radio" name="billingAddress" value="3">
                                            <span class="address-item-content">
                                                <span class="address-item-header">
                                                    <h4>Sarah Johnson</h4>
                                                    <span class="address-tag">Vacation</span>
                                                </span>
                                                <span class="address-details">
                                                    <span class="address-line">45 Ocean View Blvd</span>
                                                    <span class="address-line">Miami Beach, FL 33139</span>
                                                </span>
                                            </span>
                                            <span class="address-actions">
                                                <a href="#addressModal" class="icon-btn" aria-label="Edit">
                                                    <svg viewBox="0 0 24 24">
                                                        <path
                                                            d="M17 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h10zm-8 12l-2 4 4-2 8-8-2-2-8 8z"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </a>
                                                <button class="icon-btn delete" aria-label="Delete" type="button">
                                                    <svg viewBox="0 0 24 24">
                                                        <path
                                                            d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <aside class="order-summary">
                        <h4 class="title">Order Summary</h4>
                        <div class="subtotal__price">
                            <div class="subtotal__price--items">
                                <span>Subtotal</span>
                                <span><strong>€18,300.00</strong></span>
                            </div>
                            <div class="taxes-text">
                                <span>Estimated Tax</span>
                                <span>€3,050.00</span>
                            </div>
                            <div class="taxes-text">
                                <span>Shipping</span>
                                <span>€0.00</span>
                            </div>
                        </div>
                        <div class="total-price">
                            <span>Total</span>
                            <span>€21,350.00</span>
                        </div>
                        <div style="margin-top:1rem;">
                            <button class="btn btn--success btn--full btn--lg" type="submit">Place Order</button>
                        </div>
                    </aside>
                </div>

                <div class="checkout__step-nav">
                    <label class="btn btn--outline btn--md" for="step1">← Back</label>
                    <span></span>
                </div>
            </div>
        </form>
    </div>

    <!-- ===== ADDRESS MODAL (CSS-Only) ===== -->
    <div class="modal-overlay" id="addressModal">
        <div class="modal" role="dialog" aria-modal="true">
            <a href="#" class="modal-close-btn" aria-label="Close modal">✕</a>
            <div class="modal-header">
                <h4 class="modal-header__title">Add New Address</h4>
                <p class="modal-header__subtitle">Enter the address details below.</p>
            </div>
            <div class="modal-body">
                <form id="addressForm">
                    <div class="form-row horizontal">
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="text" class="input-field__input" id="modalFname" placeholder=" ">
                                    <label class="input-field__label" for="modalFname">First Name</label>
                                </div>
                            </div>
                        </div>
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="text" class="input-field__input" id="modalLname" placeholder=" ">
                                    <label class="input-field__label" for="modalLname">Last Name</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="text" class="input-field__input" id="modalCompany" placeholder=" ">
                                    <label class="input-field__label" for="modalCompany">Company (Optional)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row horizontal">
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="tel" class="input-field__input" id="modalPhone" placeholder=" ">
                                    <label class="input-field__label" for="modalPhone">Phone</label>
                                </div>
                            </div>
                        </div>
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="email" class="input-field__input" id="modalEmail" placeholder=" ">
                                    <label class="input-field__label" for="modalEmail">Email</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="text" class="input-field__input" id="modalAddr1" placeholder=" ">
                                    <label class="input-field__label" for="modalAddr1">Address Line 1</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="text" class="input-field__input" id="modalAddr2" placeholder=" ">
                                    <label class="input-field__label" for="modalAddr2">Address Line 2 (Optional)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row horizontal">
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="text" class="input-field__input" id="modalCity" placeholder=" ">
                                    <label class="input-field__label" for="modalCity">City</label>
                                </div>
                            </div>
                        </div>
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <select class="input-field__select" id="modalState">
                                        <option value="">Select State</option>
                                        <option value="AL">Alabama</option>
                                        <option value="CA">California</option>
                                        <option value="FL">Florida</option>
                                        <option value="IL">Illinois</option>
                                        <option value="NY">New York</option>
                                        <option value="TX">Texas</option>
                                    </select>
                                    <label class="input-field__label" for="modalState">State</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row horizontal">
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="text" class="input-field__input" id="modalZip" placeholder=" ">
                                    <label class="input-field__label" for="modalZip">Postal Code</label>
                                </div>
                            </div>
                        </div>
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <select class="input-field__select" id="modalCountry">
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="GB">United Kingdom</option>
                                        <option value="DE">Germany</option>
                                        <option value="FR">France</option>
                                    </select>
                                    <label class="input-field__label" for="modalCountry">Country</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row checkbox-row">
                        <label class="input-field__checkbox">
                            <input type="checkbox" class="input-field__checkbox-input" id="modalDefaultShipping">
                            <span class="input-field__checkbox-label">Set as default shipping address</span>
                        </label>
                    </div>
                    <div class="form-row checkbox-row">
                        <label class="input-field__checkbox">
                            <input type="checkbox" class="input-field__checkbox-input" id="modalDefaultBilling">
                            <span class="input-field__checkbox-label">Set as default billing address</span>
                        </label>
                    </div>
                    <div class="form-row checkbox-row highlighted">
                        <label class="input-field__checkbox">
                            <input type="checkbox" class="input-field__checkbox-input" id="modalSaveAddress" checked>
                            <span class="input-field__checkbox-label">Save this address for future orders</span>
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="buttons">
                    <a href="#" class="btn btn--outline">Cancel</a>
                    <button class="btn btn--success" type="button">Save Address</button>
                </div>
            </div>
        </div>
    </div>

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>
<?php $this->end(); ?>