    <div class="frontend-input">
        <!-- Text Input with Floating Label -->
        <div class="form-element">
            <div class="form-element__body">
                <div class="form-element__input-container">
                    <input type="text" id="email" class="form-element__input" placeholder=" " required>
                    <label for="email" class="form-element__label">Email Address</label>
                </div>
            </div>
            <div class="form-element__footer">
                <span class="form-element__helper">We'll never share your email</span>
                <span class="form-element__error">Please enter a valid email</span>
            </div>
        </div>

        <!-- Text Input with Left Icon -->
        <div class="form-element">
            <div class="form-element__body">
                <div class="form-element__icon-left">
                    <svg>
                        <use href="#icon-user"></use>
                    </svg>
                </div>
                <div class="form-element__input-container">
                    <input type="text" id="name" class="form-element__input" placeholder=" " required>
                    <label for="name" class="form-element__label">Full Name</label>
                </div>
            </div>
        </div>

        <!-- Textarea -->
        <div class="form-element">
            <div class="form-element__body">
                <div class="form-element__input-container">
                    <textarea id="notes" class="form-element__textarea" placeholder=" " rows="4"></textarea>
                    <label for="notes" class="form-element__label">Order Notes</label>
                </div>
            </div>
        </div>

        <!-- Select -->
        <div class="form-element">
            <div class="form-element__body">
                <div class="form-element__input-container">
                    <select id="country" class="form-element__select" required>
                        <option value="">Select Country</option>
                        <option value="US">United States</option>
                        <option value="CA">Canada</option>
                    </select>
                    <label for="country" class="form-element__label">Country</label>
                </div>
            </div>
        </div>

        <!-- Radio Group -->
        <div class="form-element">
            <fieldset class="form-element__fieldset">
                <legend class="form-element__legend">Shipping Method</legend>
                <div class="form-element__radio-options">
                    <label class="form-element__radio">
                        <input type="radio" name="shipping" value="standard" class="form-element__radio-input" checked>
                        <span class="form-element__radio-custom"></span>
                        <span class="form-element__radio-label">Standard Shipping</span>
                    </label>
                    <label class="form-element__radio">
                        <input type="radio" name="shipping" value="express" class="form-element__radio-input">
                        <span class="form-element__radio-custom"></span>
                        <span class="form-element__radio-label">Express Shipping</span>
                    </label>
                </div>
            </fieldset>
        </div>

        <!-- Checkbox Group -->
        <div class="form-element">
            <fieldset class="form-element__fieldset">
                <legend class="form-element__legend">Preferences</legend>
                <div class="form-element__checkbox-options">
                    <label class="form-element__checkbox">
                        <input type="checkbox" name="newsletter" class="form-element__checkbox-input">
                        <span class="form-element__checkbox-custom"></span>
                        <span class="form-element__checkbox-label">Subscribe to newsletter</span>
                    </label>
                    <label class="form-element__checkbox">
                        <input type="checkbox" name="offers" class="form-element__checkbox-input">
                        <span class="form-element__checkbox-custom"></span>
                        <span class="form-element__checkbox-label">Receive special offers</span>
                    </label>
                </div>
            </fieldset>
        </div>

        <!-- Toggle -->
        <div class="form-element">
            <label class="form-element__toggle">
                <input type="checkbox" class="form-element__toggle-input">
                <span class="form-element__toggle-slider"></span>
                <span class="form-element__toggle-label">Same as billing address</span>
            </label>
        </div>
    </div>