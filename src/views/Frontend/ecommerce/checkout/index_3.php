<main class="main" id="main">
    <section class="checkout-steps">
        <form class="container checkout-form" id="checkoutForm">
            <input type="radio" name="step" id="step1" checked hidden>
            <input type="radio" name="step" id="step2" hidden>
            <input type="radio" name="step" id="step3" hidden>

            <nav class="progress-bar" aria-label="Checkout Progress">
                <div class="progress-step progress-step--active" data-step="1">
                    <div class="progress-step__content">
                        <span class="progress-step__icon-wrapper">
                            <svg class="progress-step__icon" aria-hidden="true">
                                <use href="..."></use>
                            </svg>
                        </span>
                        <div class="progress-step__meta">
                            <h6 class="progress-step__title">Step 1</h6>
                            <p class="progress-step__desc">Address</p>
                        </div>
                    </div>
                    <div class="progress-step__line"></div>
                </div>
            </nav>

            <div class="form-step form-step--address form-step--active" data-step="1">
                <div class="form-step__layout">

                    <div class="form-step__main-content">
                        <h4 class="form-step__title">Select Delivery Address</h4>

                        <div class="address-grid">
                            <div class="address-card">
                                <div class="address-card__header">
                                    <input type="radio" name="shipping-address" id="address-1" checked
                                        class="address-card__input">
                                    <label for="address-1" class="address-card__label">
                                        <span class="address-card__name">2118 Thornridge</span>
                                        <span class="badge badge--home">Home</span>
                                    </label>
                                </div>
                                <div class="address-card__body">
                                    <p class="address-card__text">2118 Thornridge Cir. Syracuse, Connecticut 35624</p>
                                    <p class="address-card__phone"><strong>Contact:</strong> (209) 555-0104</p>
                                </div>
                                <div class="address-card__actions">
                                    <label for="modalToggle" class="address-card__action-btn" aria-label="Edit address">
                                        <svg class="icon">
                                            <use href="...#icon-edit"></use>
                                        </svg>
                                    </label>
                                    <button type="button" class="address-card__action-btn" aria-label="Delete address">
                                        <svg class="icon">
                                            <use href="...#icon-cancel"></use>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <label for="modalToggle" class="btn-add-address">
                            <svg class="icon">
                                <use href="...#icon-plus-solid"></use>
                            </svg>
                            <span>Add New Address</span>
                        </label>
                    </div>

                    <aside class="order-summary">
                        <h4 class="order-summary__title">Order Summary</h4>

                        <div class="coupon-box">
                            <div class="coupon-box__field">
                                <label for="discount-code" class="coupon-box__label">Discount Code</label>
                                <input type="text" id="discount-code" name="discount-code" class="coupon-box__input"
                                    placeholder="discount code...">
                            </div>
                            <button class="btn btn--secondary coupon-box__btn" type="button">Apply</button>
                        </div>

                        <div class="order-summary__totals">
                            <div class="order-summary__row">
                                <span>Subtotal</span>
                                <span>18.300,00 €</span>
                            </div>
                            <div class="order-summary__row">
                                <span>Estimated Tax</span>
                                <span>3.050,00 €</span>
                            </div>
                            <hr class="order-summary__divider">
                            <div class="order-summary__row order-summary__row--grand">
                                <strong>Total</strong>
                                <strong>21.350,00 €</strong>
                            </div>
                        </div>
                    </aside>

                </div>

                <div class="form-step__actions">
                    <label for="step2" class="btn btn--primary" role="button" tabindex="0">Next</label>
                </div>
            </div>

        </form>
    </section>

    <section class="modals-container">
        <input type="checkbox" id="modalToggle" class="modal-toggle" hidden>
        <div class="modal-overlay" id="addressModal">
            <div class="modal-box" role="dialog" aria-labelledby="modalTitle" aria-modal="true">
                <label for="modalToggle" class="modal-box__close" aria-label="Close">&times;</label>

                <div class="address-form">
                    <h2 id="modalTitle" class="address-form__title">Add/Edit Address</h2>

                    <div class="floating-input">
                        <input type="text" id="fullName" name="fullName" class="floating-input__field"
                            placeholder=" " />
                        <label for="fullName" class="floating-input__label">Full Name</label>
                        <span class="floating-input__error" id="fullNameError"></span>
                    </div>
                    <div class="address-form__actions">
                        <label for="modalToggle" class="btn btn--outline">Cancel</label>
                        <button type="button" class="btn btn--primary" id="saveAddressBtn">Save Address</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>