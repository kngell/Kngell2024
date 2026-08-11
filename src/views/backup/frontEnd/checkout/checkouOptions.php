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