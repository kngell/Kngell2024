<div class="review-section">
    <div class="review-section__header">
        <h2 class="review-section__title">Review Your Order</h2>
        <p class="review-section__subtitle">Please verify all information before placing your order</p>
    </div>

    <!-- 1. NEW: Product / Cart Summary -->
    <div class="review-section__products">
        <h3 class="review-section__products-title">Order Items</h3>
        <ul class="product-summary-list">
            <li class="product-summary-item">
                <div class="product-summary-item__image-wrapper">
                    <img src="/assets/images/product-1.jpg" alt="Premium Widget" class="product-summary-item__image">
                </div>
                <div class="product-summary-item__details">
                    <h4 class="product-summary-item__name">Premium Widget</h4>
                    <p class="product-summary-item__variant">Color: Matte Black</p>
                    <p class="product-summary-item__qty">Qty: 2</p>
                </div>
                <div class="product-summary-item__price">€2,000.00</div>
            </li>
            <li class="product-summary-item">
                <div class="product-summary-item__image-wrapper">
                    <img src="/assets/images/product-2.jpg" alt="Standard Widget" class="product-summary-item__image">
                </div>
                <div class="product-summary-item__details">
                    <h4 class="product-summary-item__name">Standard Widget</h4>
                    <p class="product-summary-item__variant">Color: Silver</p>
                    <p class="product-summary-item__qty">Qty: 1</p>
                </div>
                <div class="product-summary-item__price">€500.00</div>
            </li>
        </ul>
    </div>

    <!-- 2. Grid for Addresses and Methods -->
    <div class="review-section__grid">

        <!-- Shipping Address -->
        <div class="review-card">
            <div class="review-card__header">
                <h4 class="review-card__title">Shipping Address</h4>
                <!-- 3. ADDED: aria-label for accessibility -->
                <label for="step2" class="review-card__edit" aria-label="Edit Shipping Address">Edit</label>
            </div>
            <div class="review-card__content">
                <address class="review-card__address">
                    John Doe<br>
                    2118 Thornridge Cir.<br>
                    Syracuse, CT 35624<br>
                    United States
                </address>
            </div>
        </div>

        <!-- 4. NEW: Billing Address -->
        <div class="review-card">
            <div class="review-card__header">
                <h4 class="review-card__title">Billing Address</h4>
                <label for="step2" class="review-card__edit" aria-label="Edit Billing Address">Edit</label>
            </div>
            <div class="review-card__content">
                <address class="review-card__address">
                    John Doe<br>
                    2118 Thornridge Cir.<br>
                    Syracuse, CT 35624<br>
                    United States
                </address>
            </div>
        </div>

        <!-- Shipping Method -->
        <div class="review-card">
            <div class="review-card__header">
                <h4 class="review-card__title">Shipping Method</h4>
                <label for="step3" class="review-card__edit" aria-label="Edit Shipping Method">Edit</label>
            </div>
            <div class="review-card__content">
                <span class="review-card__method">Standard Shipping (3-5 Business Days)</span>
                <span class="review-card__cost">Free</span>
            </div>
        </div>

        <!-- Payment Method -->
        <div class="review-card">
            <div class="review-card__header">
                <h4 class="review-card__title">Payment Method</h4>
                <label for="step4" class="review-card__edit" aria-label="Edit Payment Method">Edit</label>
            </div>
            <div class="review-card__content">
                <span class="review-card__method">Credit Card</span>
                <span class="review-card__card">•••• •••• •••• 4242</span>
                <span class="review-card__expiry">Exp: 12/28</span>
            </div>
        </div>
    </div>

    <!-- Consent Section -->
    <div class="review-section__consent">
        <div class="consent-group">
            <label class="checkbox-field">
                <input type="checkbox" name="termsAccepted" id="termsAccepted" required>
                <span class="checkbox-field__label">
                    I agree to the <a href="/terms">Terms &amp; Conditions</a> and <a href="/privacy">Privacy Policy</a>
                </span>
            </label>
        </div>
    </div>

    <!-- 5. UPDATED: Semantic Totals using <dl> -->
    <div class="review-section__total">
        <dl class="price-summary">
            <div class="price-summary__row">
                <dt>Subtotal (3 items)</dt>
                <dd>€4,500.00</dd>
            </div>
            <div class="price-summary__row">
                <dt>Shipping</dt>
                <dd>€0.00</dd>
            </div>
            <div class="price-summary__row">
                <dt>Estimated Tax</dt>
                <dd>€900.00</dd>
            </div>
            <div class="price-summary__row price-summary__row--total">
                <dt><strong>Total</strong></dt>
                <dd><strong>€5,400.00</strong></dd>
            </div>
        </dl>
    </div>

    <!-- Actions and Trust Signals -->
    <div class="review-section__actions-wrapper">
        <div class="review-section__actions">
            <label for="step4" class="btn btn--outline btn--back">Back</label>
            <button type="submit" class="btn btn--primary btn--submit">Place Order</button>
        </div>

        <!-- 6. NEW: Trust Signal -->
        <div class="review-section__trust-badge">
            <svg class="icon icon-lock" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <span>Secure, encrypted checkout</span>
        </div>
    </div>
</div>