<div class="shipping-section">
    <h3 class="shipping-section__title">Choose Shipping Method</h3>
    <p class="shipping-section__subtitle">Estimated delivery times based on your location
    </p>

    <div class="shipping-section__methods" role="radiogroup" aria-label="Shipping Methods">
        <label class="shipping-method shipping-method--selected">
            <input type="radio" name="shippingMethod" value="standard" checked="">
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