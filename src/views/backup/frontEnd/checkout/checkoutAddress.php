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

            <button type="button" class="btn btn--outline btn--full address-section__add" data-modal="addressModal">
                <svg class="icon icon--plus" aria-hidden="true" width="20" height="20">
                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-plus-solid"></use>
                </svg>
                <span>Add New Address</span>
            </button>

            <div class="billing-section">
                <h4 class="billing-section__title">Billing Address</h4>
                <div class="billing-section__toggle">
                    <label class="checkbox-field">
                        <input type="checkbox" name="billingSameAsShipping" id="billingSameAsShipping" checked>
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
                        <input type="text" name="billingCity" id="billingCity" class="input-field__input"
                            placeholder=" " autocomplete="address-level2">
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
                            <input type="text" name="billingZipCode" id="billingZipCode" class="input-field__input"
                                placeholder=" " autocomplete="postal-code">
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
                        <input type="text" name="discountCode" id="discountCode" class="coupon-field__input"
                            placeholder="Enter discount code">
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
                        <img src="<?= $this->asset('img/ecommerce/product-img.png') ?>" alt="Product" loading="lazy">
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