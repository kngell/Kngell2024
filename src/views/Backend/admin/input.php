<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/backend/admin/pages/input') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
    <form action="" class='form span-all' id='Input-form'>
        <div class="input-show span-all">
            <div class="row-content">
                <div class="row">
                    <div class="input-wrapper">
                        <!-- Basic Text Input -->
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="text" class="input-field__input" id="firstname" placeholder=" ">
                                    <label for="firstname" class="input-field__label">First Name</label>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">Enter your first name</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>

                        <!-- Text Input with Left Icon -->
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__icon-left">
                                    <svg class="icon">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-user"></use>
                                    </svg>
                                </div>
                                <div class="input-field__input-container">
                                    <input type="text" class="input-field__input" id="username" placeholder=" ">
                                    <label for="username" class="input-field__label">Username</label>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">Choose a unique username</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>

                        <!-- Text Input with Right Icon & Counter -->
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="text" class="input-field__input" id="bio" placeholder=" "
                                        value="John Doe">
                                    <label for="bio" class="input-field__label">Biography</label>
                                </div>
                                <div class="input-field__right-container">
                                    <span class="input-field__counter">5/100</span>
                                    <div class="input-field__icon-right">
                                        <svg class="icon">
                                            <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">Tell us about yourself</span>
                                <span class="input-field__error"></span>
                                <span class="input-field__footer-counter">5/100</span>
                            </div>
                        </div>

                        <!-- Text Input with Both Icons -->
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__icon-left">
                                    <svg class="icon">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-search"></use>
                                    </svg>
                                </div>
                                <div class="input-field__input-container">
                                    <input type="text" class="input-field__input" id="search" placeholder=" ">
                                    <label for="search" class="input-field__label">Search</label>
                                </div>
                                <div class="input-field__right-container">
                                    <div class="input-field__icon-right">
                                        <svg class="icon">
                                            <use href="/public/assets/img/icons-sprite.svg#icon-filter"></use>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">Search products, categories...</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>
                    </div>
                    <div class="textarea-wrapper">
                        <!-- Basic Textarea -->
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <textarea class="input-field__textarea" id="description" placeholder=" "
                                        rows="4"></textarea>
                                    <label for="description" class="input-field__label">Description</label>
                                </div>
                                <div class="input-field__right-container">
                                    <span class="input-field__counter">0/500</span>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">Enter a detailed description</span>
                                <span class="input-field__error"></span>
                                <span class="input-field__footer-counter">0/500</span>
                            </div>
                        </div>

                        <!-- Textarea with Left Icon -->
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__icon-left">
                                    <svg class="icon">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-align-left"></use>
                                    </svg>
                                </div>
                                <div class="input-field__input-container">
                                    <textarea class="input-field__textarea" id="notes" placeholder=" "
                                        rows="3"></textarea>
                                    <label for="notes" class="input-field__label">Notes</label>
                                </div>
                                <div class="input-field__right-container">
                                    <span class="input-field__counter">0/200</span>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">Add any additional notes</span>
                                <span class="input-field__error"></span>
                                <span class="input-field__footer-counter">0/200</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="input-wrapper">
                        <!-- Native Select -->

                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__icon-left">
                                    <svg class="icon">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
                                    </svg>
                                </div>
                                <div class="input-field__input-container">
                                    <select class="input-field__select" id="category" required>
                                        <option value="" disabled selected></option>
                                        <option value="electronics">Electronics</option>
                                        <option value="clothing">Clothing</option>
                                        <option value="books">Books</option>
                                    </select>
                                    <label for="category" class="input-field__label">Category</label>
                                </div>


                                <div class="input-field__right-container">
                                    <div class="input-field__icon-right">
                                        <svg class="icon">
                                            <use href="/public/assets/img/icons-sprite.svg#icon-arrow-down"></use>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">Select a product category</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>
                        <!-- Native Select with Groups -->
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <select class="input-field__select" id="country">
                                        <option value="" disabled selected>Select your country</option>
                                        <optgroup label="Europe">
                                            <option value="fr">France</option>
                                            <option value="de">Germany</option>
                                            <option value="uk">United Kingdom</option>
                                        </optgroup>
                                        <optgroup label="Asia">
                                            <option value="jp">Japan</option>
                                            <option value="cn">China</option>
                                            <option value="in">India</option>
                                        </optgroup>
                                    </select>
                                    <label for="country" class="input-field__label">Country</label>
                                </div>
                                <div class="input-field__right-container">
                                    <div class="input-field__icon-right">
                                        <svg class="icon">
                                            <use href="/public/assets/img/icons-sprite.svg#icon-arrow-down"></use>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">Select your country of residence</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-wrapper custom">
                        <!-- Custom Select - Closed State -->
                        <div class="input-field custom-select" data-select-id="product-select">
                            <div class="input-field__body">
                                <div class="input-field__icon-left">
                                    <svg class="icon">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-edit"></use>
                                    </svg>
                                </div>
                                <div class="input-field__input-container">
                                    <button type="button" class="input-field__custom-select" id="product">
                                        <span class="text placeholder">Search Product by name or Sku...</span>
                                    </button>
                                    <label for="product" class="input-field__label">Product</label>
                                </div>
                                <div class="input-field__right-container">
                                    <div class="input-field__icon-right">
                                        <svg class="icon">
                                            <use href="/public/assets/img/icons-sprite.svg#icon-arrow-down"></use>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Dropdown -->
                            <div class="input-field__dropdown">
                                <div class="search-group">
                                    <div class="search-group__icon-container">
                                        <svg class="icon">
                                            <use href="/public/assets/img/icons-sprite.svg#icon-search"></use>
                                        </svg>
                                    </div>
                                    <input type="text" class="search-group__input-search"
                                        placeholder="Search products...">
                                </div>
                                <ul class="option-list">
                                    <li class="option-list__item" data-value="1">Product 1 (SKU-001)</li>
                                    <li class="option-list__item" data-value="2">Product 2 (SKU-002)</li>
                                    <li class="option-list__item" data-value="3">Product 3 (SKU-003)</li>
                                </ul>
                            </div>

                            <div class="input-field__footer">
                                <span class="input-field__helper">Search and select a product</span>
                                <span class="input-field__error"></span>
                            </div>

                            <input type="hidden" name="product_id" class="input-field__hidden-value" value="">
                        </div>

                        <!-- Custom Select - Open State -->
                        <!-- <div class="input-field custom-select is-open">
                        <div class="input-field__body">
                            <div class="input-field__icon-left">
                                <svg class="icon">
                                    <use href="/public/assets/img/icons-sprite.svg#icon-trash"></use>
                                </svg>
                            </div>
                            <div class="input-field__input-container">
                                <button type="button" class="input-field__custom-select has-value" id="product">
                                    <span class="text">Product 1 (SKU-001)</span>
                                </button>
                                <label for="product" class="input-field__label">Product</label>
                            </div>
                            <div class="input-field__right-container">
                                <div class="input-field__clear">
                                    <svg class="icon">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-close"></use>
                                    </svg>
                                </div>
                                <div class="input-field__icon-right">
                                    <svg class="icon">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-arrow-up"></use>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="input-field__dropdown is-open">
                            <div class="search-group">
                                <div class="search-group__icon-container">
                                    <svg class="icon">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-search"></use>
                                    </svg>
                                </div>
                                <input type="text" class="search-group__input-search" placeholder="Search products..."
                                    value="pro">
                            </div>
                            <ul class="option-list">
                                <li class="option-list__item selected" data-value="1">Product 1 (SKU-001)</li>
                                <li class="option-list__item" data-value="4">Product 4 (SKU-004)</li>
                                <li class="option-list__item" data-value="5">Product 5 (SKU-005)</li>
                            </ul>
                        </div>

                        <div class="input-field__footer">
                            <span class="input-field__helper">Product selected</span>
                            <span class="input-field__error"></span>
                        </div>

                        <input type="hidden" name="product_id" class="input-field__hidden-value" value="1">
                    </div> -->
                    </div>

                </div>
                <div class="row">
                    <div class="input-wrapper">
                        <!-- Radio Group -->
                        <div class="input-field">
                            <fieldset class="input-field__fieldset">
                                <legend class="input-field__legend">Payment Method</legend>

                                <div class="input-field__radio-options">
                                    <label for="credit-card" class="input-field__radio">
                                        <input type="radio" id="credit-card" name="payment" value="credit_card"
                                            class="input-field__radio-input">
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">Credit Card</span>
                                    </label>

                                    <label class="input-field__radio">
                                        <input type="radio" name="payment" value="paypal"
                                            class="input-field__radio-input">
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">PayPal</span>
                                    </label>

                                    <label class="input-field__radio">
                                        <input type="radio" name="payment" value="bank_transfer"
                                            class="input-field__radio-input">
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">Bank Transfer</span>
                                    </label>
                                </div>
                            </fieldset>

                            <div class="input-field__footer">
                                <span class="input-field__helper">Select your preferred payment method</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>

                        <!-- Radio Group - Horizontal Layout -->
                        <div class="input-field">
                            <fieldset class="input-field__fieldset">
                                <legend class="input-field__legend">Gender</legend>

                                <div class="input-field__radio-options horizontal">
                                    <label class="input-field__radio">
                                        <input type="radio" name="gender" value="male" class="input-field__radio-input">
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">Male</span>
                                    </label>

                                    <label class="input-field__radio">
                                        <input type="radio" name="gender" value="female"
                                            class="input-field__radio-input">
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">Female</span>
                                    </label>

                                    <label class="input-field__radio">
                                        <input type="radio" name="gender" value="other"
                                            class="input-field__radio-input">
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">Other</span>
                                    </label>
                                </div>
                            </fieldset>

                            <div class="input-field__footer">
                                <span class="input-field__helper">Select your gender</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>
                        <!-- Radio Group with Floating Label -->
                        <div class="input-field">
                            <fieldset class="input-field__fieldset">
                                <legend class="input-field__legend">Payment Method</legend>

                                <div class="input-field__radio-options">
                                    <label class="input-field__radio">
                                        <input type="radio" name="payment" value="credit_card"
                                            class="input-field__radio-input">
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">Credit Card</span>
                                    </label>

                                    <label class="input-field__radio">
                                        <input type="radio" name="payment" value="paypal"
                                            class="input-field__radio-input">
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">PayPal</span>
                                    </label>

                                    <label class="input-field__radio">
                                        <input type="radio" name="payment" value="bank_transfer"
                                            class="input-field__radio-input">
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">Bank Transfer</span>
                                    </label>
                                </div>
                            </fieldset>

                            <div class="input-field__footer">
                                <span class="input-field__helper">Select your payment method</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>

                        <!-- Radio Group - Horizontal Layout -->
                        <div class="input-field">
                            <fieldset class="input-field__fieldset">
                                <legend class="input-field__legend">Gender</legend>

                                <div class="input-field__radio-options horizontal">
                                    <label class="input-field__radio">
                                        <input type="radio" name="gender" value="male" class="input-field__radio-input">
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">Male</span>
                                    </label>

                                    <label class="input-field__radio">
                                        <input type="radio" name="gender" value="female"
                                            class="input-field__radio-input">
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">Female</span>
                                    </label>

                                    <label class="input-field__radio">
                                        <input type="radio" name="gender" value="other"
                                            class="input-field__radio-input">
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">Other</span>
                                    </label>
                                </div>
                            </fieldset>

                            <div class="input-field__footer">
                                <span class="input-field__helper">Select your gender</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>

                        <!-- Radio Group with Error State -->
                        <div class="input-field is-error">
                            <fieldset class="input-field__fieldset">
                                <legend class="input-field__legend">Preferred Contact</legend>

                                <div class="input-field__radio-options">
                                    <label class="input-field__radio">
                                        <input type="radio" name="contact" value="email"
                                            class="input-field__radio-input">
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">Email</span>
                                    </label>

                                    <label class="input-field__radio">
                                        <input type="radio" name="contact" value="phone"
                                            class="input-field__radio-input">
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">Phone</span>
                                    </label>
                                </div>
                            </fieldset>

                            <div class="input-field__footer">
                                <span class="input-field__helper">Choose how to contact you</span>
                                <span class="input-field__error">Please select a contact method</span>
                            </div>
                        </div>

                        <!-- Radio Group - Disabled -->
                        <div class="input-field is-disabled">
                            <fieldset class="input-field__fieldset">
                                <legend class="input-field__legend">Subscription</legend>

                                <div class="input-field__radio-options">
                                    <label class="input-field__radio">
                                        <input type="radio" name="subscription" value="monthly"
                                            class="input-field__radio-input" disabled>
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">Monthly</span>
                                    </label>

                                    <label class="input-field__radio">
                                        <input type="radio" name="subscription" value="yearly"
                                            class="input-field__radio-input" disabled>
                                        <span class="input-field__radio-custom"></span>
                                        <span class="input-field__radio-label">Yearly</span>
                                    </label>
                                </div>
                            </fieldset>

                            <div class="input-field__footer">
                                <span class="input-field__helper">Subscription options are currently unavailable</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="input-wrapper">
                        <!-- Checkbox Group -->
                        <div class="input-field">
                            <fieldset class="input-field__fieldset">
                                <legend class="input-field__legend">Interests</legend>

                                <div class="input-field__radio-options">
                                    <label class="input-field__checkbox">
                                        <input type="checkbox" name="interests" value="technology"
                                            class="input-field__checkbox-input">
                                        <span class="input-field__checkbox-custom"></span>
                                        <span class="input-field__checkbox-label">Technology</span>
                                    </label>

                                    <label class="input-field__checkbox">
                                        <input type="checkbox" name="interests" value="sports"
                                            class="input-field__checkbox-input">
                                        <span class="input-field__checkbox-custom"></span>
                                        <span class="input-field__checkbox-label">Sports</span>
                                    </label>

                                    <label class="input-field__checkbox">
                                        <input type="checkbox" name="interests" value="music"
                                            class="input-field__checkbox-input">
                                        <span class="input-field__checkbox-custom"></span>
                                        <span class="input-field__checkbox-label">Music</span>
                                    </label>

                                    <label class="input-field__checkbox">
                                        <input type="checkbox" name="interests" value="art"
                                            class="input-field__checkbox-input">
                                        <span class="input-field__checkbox-custom"></span>
                                        <span class="input-field__checkbox-label">Art</span>
                                    </label>
                                </div>

                                <div class="input-field__right-container">
                                    <span class="input-field__counter">0/4</span>
                                </div>
                            </fieldset>

                            <div class="input-field__footer">
                                <span class="input-field__helper">Select all that apply</span>
                                <span class="input-field__error"></span>
                                <span class="input-field__footer-counter">0/4</span>
                            </div>
                        </div>

                        <!-- Checkbox Group - Horizontal -->
                        <div class="input-field">
                            <fieldset class="input-field__fieldset">
                                <legend class="input-field__legend">Preferred Contact Method</legend>

                                <div class="input-field__radio-options horizontal">
                                    <label class="input-field__checkbox">
                                        <input type="checkbox" name="contact" value="email"
                                            class="input-field__checkbox-input">
                                        <span class="input-field__checkbox-custom"></span>
                                        <span class="input-field__checkbox-label">Email</span>
                                    </label>

                                    <label class="input-field__checkbox">
                                        <input type="checkbox" name="contact" value="sms"
                                            class="input-field__checkbox-input">
                                        <span class="input-field__checkbox-custom"></span>
                                        <span class="input-field__checkbox-label">SMS</span>
                                    </label>

                                    <label class="input-field__checkbox">
                                        <input type="checkbox" name="contact" value="whatsapp"
                                            class="input-field__checkbox-input">
                                        <span class="input-field__checkbox-custom"></span>
                                        <span class="input-field__checkbox-label">WhatsApp</span>
                                    </label>
                                </div>
                            </fieldset>

                            <div class="input-field__footer">
                                <span class="input-field__helper">Choose how to contact you</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="input-wrapper">
                        <!-- Single Checkbox -->
                        <!-- Standalone Checkbox (no body wrapper) -->
                        <div class="input-field">
                            <label class="input-field__checkbox input-field__checkbox--single">
                                <input type="checkbox" name="newsletter" class="input-field__checkbox-input">
                                <span class="input-field__checkbox-custom"></span>
                                <span class="input-field__checkbox-label">Subscribe to newsletter</span>
                            </label>

                            <div class="input-field__footer">
                                <span class="input-field__helper">Receive updates and offers</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>

                        <!-- Basic Toggle -->
                        <div class="input-field">
                            <label class="input-field__toggle">
                                <input type="checkbox" name="notifications" class="input-field__toggle-input">
                                <span class="input-field__toggle-slider"></span>
                                <span class="input-field__toggle-label">Enable notifications</span>
                            </label>

                            <div class="input-field__footer">
                                <span class="input-field__helper">Get real-time notifications</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>

                        <!-- Toggle with Left Label -->
                        <div class="input-field">
                            <label class="input-field__toggle input-field__toggle--label-left">
                                <span class="input-field__toggle-label">Dark mode</span>
                                <input type="checkbox" name="dark_mode" class="input-field__toggle-input">
                                <span class="input-field__toggle-slider"></span>
                            </label>

                            <div class="input-field__footer">
                                <span class="input-field__helper">Switch to dark theme</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>

                        <!-- Required Toggle -->
                        <div class="input-field is-required">
                            <label class="input-field__toggle">
                                <input type="checkbox" name="terms" class="input-field__toggle-input" required>
                                <span class="input-field__toggle-slider"></span>
                                <span class="input-field__toggle-label">I accept the terms and conditions</span>
                            </label>

                            <div class="input-field__footer">
                                <span class="input-field__helper">You must accept to continue</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>

                        <!-- Disabled Toggle -->
                        <div class="input-field is-disabled">
                            <label class="input-field__toggle">
                                <input type="checkbox" name="feature" class="input-field__toggle-input" disabled>
                                <span class="input-field__toggle-slider"></span>
                                <span class="input-field__toggle-label">Feature unavailable</span>
                            </label>

                            <div class="input-field__footer">
                                <span class="input-field__helper">This feature is coming soon</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>

                        <!-- Toggle with Error (e.g., must be enabled) -->
                        <div class="input-field is-error">
                            <label class="input-field__toggle">
                                <input type="checkbox" name="required_toggle" class="input-field__toggle-input">
                                <span class="input-field__toggle-slider"></span>
                                <span class="input-field__toggle-label">Enable two-factor authentication</span>
                            </label>

                            <div class="input-field__footer">
                                <span class="input-field__helper">Required for security</span>
                                <span class="input-field__error">Please enable two-factor authentication</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="input-wrapper">
                        <!-- Date Input -->
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__icon-left">
                                    <svg class="icon">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-calendar"></use>
                                    </svg>
                                </div>
                                <div class="input-field__input-container">
                                    <input type="date" class="input-field__input" id="birthdate" placeholder=" ">
                                    <label for="birthdate" class="input-field__label">Birth Date</label>
                                </div>
                                <div class="input-field__right-container">
                                    <div class="input-field__icon-right">
                                        <svg class="icon">
                                            <use href="/public/assets/img/icons-sprite.svg#icon-calendar"></use>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">Select your birth date</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="input-wrapper">
                        <!-- Number Input -->
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="number" class="input-field__input" id="quantity" placeholder=" "
                                        value="1" min="1" max="99">
                                    <label for="quantity" class="input-field__label">Quantity</label>
                                </div>
                                <div class="input-field__right-container">
                                    <div class="input-field__stepper">
                                        <button type="button" class="input-field__stepper-down">-</button>
                                        <button type="button" class="input-field__stepper-up">+</button>
                                    </div>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">Select quantity (1-99)</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="input-wrapper">
                        <!-- Password Input -->
                        <div class="input-field">
                            <div class="input-field__body">
                                <div class="input-field__icon-left">
                                    <svg class="icon">
                                        <use href="/public/assets/img/icons-sprite.svg#icon-lock"></use>
                                    </svg>
                                </div>
                                <div class="input-field__input-container">
                                    <input type="password" class="input-field__input" id="password" placeholder=" ">
                                    <label for="password" class="input-field__label">Password</label>
                                </div>
                                <div class="input-field__right-container">
                                    <button type="button" class="input-field__password-toggle">
                                        <svg class="icon icon-eye">
                                            <use href="/public/assets/img/icons-sprite.svg#icon-eye"></use>
                                        </svg>
                                        <svg class="icon icon-eye-off" style="display: none;">
                                            <use href="/public/assets/img/icons-sprite.svg#icon-eye-off"></use>
                                        </svg>
                                    </button>
                                    <div class="input-field__icon-right">
                                        <svg class="icon">
                                            <use href="/public/assets/img/icons-sprite.svg#icon-lock"></use>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">Enter a strong password</span>
                                <span class="input-field__error"></span>
                                <span class="input-field__footer-counter">8+ characters</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="input-wrapper">
                        <!-- Error State -->
                        <div class="input-field input-field--error">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="email" class="input-field__input" id="email-error" placeholder=" "
                                        value="invalid">
                                    <label for="email-error" class="input-field__label">Email Address</label>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">Enter a valid email</span>
                                <span class="input-field__error">Please enter a valid email address</span>
                            </div>
                        </div>

                        <!-- Disabled State -->
                        <div class="input-field input-field--disabled">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="text" class="input-field__input" id="disabled-field" placeholder=" "
                                        value="Disabled value" disabled>
                                    <label for="disabled-field" class="input-field__label">Disabled Field</label>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">This field is disabled</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>

                        <!-- Required Field -->
                        <div class="input-field input-field--required">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="text" class="input-field__input" id="fullname" placeholder=" "
                                        required>
                                    <label for="fullname" class="input-field__label">Full Name</label>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">Enter your full name</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>

                        <!-- Readonly State -->
                        <div class="input-field input-field--readonly">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="text" class="input-field__input" id="readonly-field" placeholder=" "
                                        value="Readonly value" readonly>
                                    <label for="readonly-field" class="input-field__label">Readonly Field</label>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">This field is read-only</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>

                        <!-- Success State -->
                        <div class="input-field input-field--success">
                            <div class="input-field__body">
                                <div class="input-field__input-container">
                                    <input type="text" class="input-field__input" id="success-field" placeholder=" "
                                        value="Valid value">
                                    <label for="success-field" class="input-field__label">Valid Field</label>
                                </div>
                                <div class="input-field__right-container">
                                    <div class="input-field__icon-right">
                                        <svg class="icon">
                                            <use href="/public/assets/img/icons-sprite.svg#icon-check"></use>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="input-field__footer">
                                <span class="input-field__helper">Field is valid</span>
                                <span class="input-field__error"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>




    <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/backend/pages/input') ?>

<?php $this->end();