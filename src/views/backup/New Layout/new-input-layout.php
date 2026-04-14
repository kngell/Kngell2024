   <!-- Without icon - label starts at 16px, floats to -8px above border -->
   <div class="input-field">
       <div class="input-field__body">
           <input type="text" class="input-field__input" id="name1" placeholder=" " />
           <label for="name1" class="input-field__label">
               Full Name
           </label>
       </div>
   </div>

   <!-- With left icon - label starts at 44px, floats to 36px above -->
   <div class="input-field">
       <div class="input-field__wrapper">
           <input type="email" class="input-field__input has-left-icon" id="email1" placeholder=" " />
           <label for="email1" class="input-field__label has-left-icon">
               Email Address
               <span class="input-field__required">*</span>
           </label>
           <span class="input-field__icon-left">
               <svg class="icon" viewBox="0 0 24 24" width="20" height="20">
                   <path
                       d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" />
               </svg>
           </span>
       </div>
       <div class="input-field__footer">
           <span class="input-field__helper">We'll never share your email</span>
           <span class="input-field__footer-counter">0/100</span>
       </div>
   </div>

   <!-- With value (filled state) - label stays floated -->
   <div class="input-field">
       <div class="input-field__wrapper">
           <input type="text" class="input-field__input has-left-icon" id="username1" placeholder=" "
               value="john_doe" />
           <label for="username1" class="input-field__label has-left-icon">
               Username
           </label>
           <span class="input-field__icon-left">
               <svg viewBox="0 0 24 24" width="20" height="20">
                   <path
                       d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
               </svg>
           </span>
       </div>
   </div>

   <!-- With both icons and counter -->
   <div class="input-field">
       <div class="input-field__wrapper">
           <input type="text" class="input-field__input has-left-icon has-right-icon has-counter" id="username2"
               placeholder=" " value="john_doe" />
           <label for="username2" class="input-field__label has-left-icon">
               Username
           </label>
           <span class="input-field__icon-left">
               <svg viewBox="0 0 24 24" width="20" height="20">
                   <path
                       d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
               </svg>
           </span>
           <span class="input-field__icon-right">
               <svg viewBox="0 0 24 24" width="20" height="20">
                   <path
                       d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
               </svg>
           </span>
           <span class="input-field__counter has-right-icon">5/20</span>
       </div>
       <div class="input-field__footer">
           <span class="input-field__error">This field is required</span>
           <span class="input-field__footer-counter">0/255</span>
       </div>
   </div>
   <div class="input-field">
       <div class="input-field__wrapper">
           <input type="text" class="input-field__input" id="test-input" placeholder=" ">
           <label for="test-input" class="input-field__label">Test Label</label>
       </div>
   </div>


   <!-- Select with both left and right icons -->
   <div class="input-field">
       <div class="input-field__wrapper">
           <select class="input-field__select has-left-icon has-right-icon" id="select-with-both-icons">
               <button>
                   <selectedcontent></selectedcontent>
               </button>
               <option value="" class="input-field__option" selected disabled></option>
               <option value="1" class="input-field__option">Option 1</option>
               <option value="2" class="input-field__option">Option 2</option>
               <option value="3" class="input-field__option">Option 3</option>
           </select>

           <span class="input-field__icon-left">
               <svg viewBox="0 0 24 24" width="20" height="20">
                   <path
                       d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
               </svg>
           </span>
           <span class="input-field__icon-right">
               <svg viewBox="0 0 24 24" width="20" height="20">
                   <path d="M7 10l5 5 5-5z" />
               </svg>
           </span>
       </div>
       <div class="input-field__footer">
           <span class="input-field__helper">Please select an option</span>
           <span class="input-field__footer-counter">0/3</span>
       </div>
   </div>

   <!-- Select with left icon, right icon, and counter -->
   <div class="input-field">
       <div class="input-field__wrapper">
           <select class="input-field__select has-left-icon has-right-icon has-counter" id="select-with-all">
               <button>
                   <selectedcontent></selectedcontent>
               </button>
               <option value="" class="input-field__option" selected disabled></option>
               <option value="1" class="input-field__option">Option 1</option>
               <option value="2" class="input-field__option">Option 2</option>
               <option value="3" class="input-field__option">Option 3</option>
           </select>
           <label for="select-with-all" class="input-field__label has-left-icon">
               Choose option
           </label>
           <span class="input-field__icon-left">
               <svg viewBox="0 0 24 24" width="20" height="20">
                   <path
                       d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
               </svg>
           </span>
           <span class="input-field__icon-right">
               <svg viewBox="0 0 24 24" width="20" height="20">
                   <path d="M7 10l5 5 5-5z" />
               </svg>
           </span>
           <span class="input-field__counter">0/3</span>
       </div>
       <div class="input-field__footer">
           <span class="input-field__error">This field is required</span>
           <span class="input-field__footer-counter">0/3</span>
       </div>
   </div>

   <!-- Select with left icon only -->
   <div class="input-field">
       <div class="input-field__wrapper">
           <select class="input-field__select has-left-icon" id="select-left-icon">
               <button>
                   <selectedcontent></selectedcontent>
               </button>
               <option value="" class="input-field__option" selected disabled></option>
               <option value="1" class="input-field__option">Option 1</option>
               <option value="2" class="input-field__option">Option 2</option>
           </select>
           <label for="select-left-icon" class="input-field__label has-left-icon">
               Select with left icon
           </label>
           <span class="input-field__icon-left">
               <svg viewBox="0 0 24 24" width="20" height="20">
                   <path
                       d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
               </svg>
           </span>
       </div>
   </div>

   <!-- Select with right icon only (most common) -->
   <div class="input-field">
       <div class="input-field__wrapper">
           <select name="" id="input-dropdown__select" class="input-dropdown__select">
               <button>
                   <selectedcontent></selectedcontent>
               </button>
               <option value="" class="input-dropdown__option" selected disabled>Select
                   Position</option>
               <option value="top" class="input-dropdown__option">Top</option>
               <option value="middle" class="input-dropdown__option">Middle</option>
               <option value="bottom" class="input-dropdown__option">Bottom</option>
           </select>
           <label for="select-right-icon" class="input-field__label">
               Select a Position
           </label>
           <span class="input-field__icon-right">
               <svg class="icon cancel" aria-label="Cancel" role="img">
                   <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                   </use>
               </svg>
           </span>
       </div>
   </div>
   <!-- Select Config -->
   <!-- return[
            [
                'key' => 'position',
                'name' => 'position',
                'map' => 'position',
                // 'label' => 'Select an option',
                'type' => 'select',
                'options' => $this->getOptions(),
                'placeholder' => 'Select Position',
                // 'required' => true,
                // 'leftIcon' => 'icon-user',
                // 'leftIconAria' => 'User icon',
                'rightIcon' => 'icon-arrow-down',
                'rightIconAria' => 'Arrow down',
                'hint' => 'Please select an option',
                // 'counter' => '0/3',
            ],] -->

   <div class="input-field">
       <div class="input-field__wrapper">
           <textarea name="" id="username2" class="input-field__input has-left-icon has-right-icon has-counter">
            "john_doe"
        </textarea>
           <label for="username2" class="input-field__label has-left-icon">
               Username
           </label>
           <span class="input-field__icon-left">
               <svg viewBox="0 0 24 24" width="20" height="20">
                   <path
                       d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
               </svg>
           </span>
           <span class="input-field__icon-right">
               <svg viewBox="0 0 24 24" width="20" height="20">
                   <path
                       d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
               </svg>
           </span>
           <span class="input-field__counter has-right-icon">5/20</span>
       </div>
       <div class="input-field__footer">
           <span class="input-field__error">This field is required</span>
           <span class="input-field__footer-counter">0/255</span>
       </div>
   </div>


   <div class="input-field custom-select" data-required="true">
       <div class="input-field__wrapper">
           <div class="input-field__body">
               <button type="button" class="input-field__custom-select has-right-icon">
                   <span class="text">Search Product by name or Sku ...</span>
                   <span class="input-field__icon-right">
                       <svg class="icon cancel" aria-label="Cancel" role="img">
                           <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                           </use>
                       </svg>
                   </span>
               </button>

               <!-- Use input-field__label like other fields -->
               <label class="input-field__label">Product</label>

               <div class="input-field__custom-options">
                   <button type="button" class="search-group">
                       <div class="search-group__icon-container">
                           <svg class="icon cancel" aria-label="Cancel" role="img">
                               <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-search">
                               </use>
                           </svg>
                       </div>
                       <input type="text" class="search-group__input-search" placeholder="search" />
                   </button>
                   <ul class="option-list"></ul>
               </div>
           </div>
           <div class="input-field__footer">
               <span class="input-field__helper">Please select an option</span>
               <span class="input-field__footer-counter">0/3</span>
           </div>
           <input type="hidden" name="product_id" class="input-field__hidden-value" value="">
       </div>
   </div>


   <div class="input-field custom-select" data-required="true">
       <div class="input-field__body">
           <button type="button" class="input-field__custom-select has-right-icon">
               <span class="text">Search Product by name or Sku ...</span>
               <span class="input-field__icon-right">
                   <svg class="icon cancel" aria-label="Cancel" role="img">
                       <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                       </use>
                   </svg>
               </span>
           </button>

           <!-- Use input-field__label like other fields -->
           <label class="input-field__label">Product</label>

           <div class="input-field__custom-options">
               <button type="button" class="search-group">
                   <div class="search-group__icon-container">
                       <svg class="icon cancel" aria-label="Cancel" role="img">
                           <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-search">
                           </use>
                       </svg>
                   </div>
                   <input type="text" class="search-group__input-search" placeholder="search" />
               </button>
               <ul class="option-list"></ul>
           </div>
       </div>
       <div class="input-field__footer">
           <span class="input-field__helper">Please select an option</span>
           <span class="input-field__footer-counter">0/3</span>
       </div>
       <input type="hidden" name="product_id" class="input-field__hidden-value" value="">
   </div>

   <!-- Custom Select -->
   <div class="input-field custom-select" data-required="true">
       <!-- Control area (consistent with input-field__control) -->
       <div class="input-field__control">
           <!-- Main button (acts like input) -->
           <button type="button" class="input-field__custom-select has-right-icon">
               <span class="text placeholder">Search Product by name or Sku ...</span>
               <span class="input-field__icon-right">
                   <svg class="icon">
                       <use href="#icon-arrow-down"></use>
                   </svg>
               </span>
           </button>

           <!-- Floating label -->
           <label class="input-field__label">Product</label>

           <!-- Optional: Left icon -->
           <span class="input-field__icon-left">
               <svg class="icon">
                   <use href="#icon-search"></use>
               </svg>
           </span>

           <!-- Optional: Counter -->
           <span class="input-field__counter">0/1</span>

           <!-- Clear button (shown when has value) -->
           <button type="button" class="input-field__clear" style="display: none;">
               <svg class="icon">
                   <use href="#icon-cancel"></use>
               </svg>
           </button>
       </div>

       <!-- Dropdown (only for select/custom-select) -->
       <div class="input-field__dropdown">
           <!-- Search input (optional) -->
           <div class="search-group">
               <div class="search-group__icon-container">
                   <svg class="icon">
                       <use href="#icon-search"></use>
                   </svg>
               </div>
               <input type="text" class="search-group__input-search" placeholder="Search...">
           </div>

           <!-- Options list -->
           <ul class="option-list" role="listbox">
               <!-- Dynamic options -->
           </ul>
       </div>

       <!-- Footer (consistent across all field types) -->
       <div class="input-field__footer">
           <span class="input-field__helper">Please select an option</span>
           <span class="input-field__error"></span>
       </div>

       <!-- Hidden input for value -->
       <input type="hidden" name="product_id" class="input-field__hidden-value" value="">
   </div>

   <!-- Regular Input -->
   <div class="input-field" data-required="true">
       <!-- Control area (consistent with custom-select) -->
       <div class="input-field__control">
           <input type="text" class="input-field__input has-left-icon has-right-icon has-counter" id="username2"
               placeholder=" " value="john_doe" />

           <!-- Floating label -->
           <label for="username2" class="input-field__label has-left-icon">Username</label>

           <!-- Left icon -->
           <span class="input-field__icon-left">
               <svg viewBox="0 0 24 24" width="20" height="20">
                   <path
                       d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
               </svg>
           </span>

           <!-- Right icon -->
           <span class="input-field__icon-right">
               <svg viewBox="0 0 24 24" width="20" height="20">
                   <path
                       d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
               </svg>
           </span>

           <!-- Counter -->
           <span class="input-field__counter has-right-icon">5/20</span>
       </div>

       <!-- Footer (consistent across all field types) -->
       <div class="input-field__footer">
           <span class="input-field__error">This field is required</span>
           <span class="input-field__helper"></span>
       </div>
   </div>
   <!-- Textarea -->
   <div class="input-field">
       <div class="input-field__control">
           <textarea class="input-field__textarea has-left-icon has-right-icon has-counter" id="bio" placeholder=" ">John Doe is a software engineer...
            </textarea>

           <label for="bio" class="input-field__label has-left-icon">Biography</label>

           <span class="input-field__icon-left">
               <svg width="20" height="20">
                   <path d="M4 4h16v2H4V4zm0 4h16v2H4V8zm0 4h10v2H4v-2zm0 4h16v2H4v-2z" />
               </svg>
           </span>

           <span class="input-field__icon-right">
               <svg width="20" height="20">
                   <path
                       d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
               </svg>
           </span>

           <span class="input-field__counter has-right-icon">150/500</span>
       </div>

       <div class="input-field__footer">
           <span class="input-field__helper">Tell us about yourself</span>
           <span class="input-field__error"></span>
       </div>
   </div>

   <!-- Native Select -->
   <div class="input-field">
       <div class="input-field__control">
           <select class="input-field__select has-right-icon" id="position">
               <option value="" selected disabled>Select Position</option>
               <option value="top">Top</option>
               <option value="middle">Middle</option>
               <option value="bottom">Bottom</option>
           </select>

           <label for="position" class="input-field__label">Select a Position</label>

           <span class="input-field__icon-right">
               <svg class="icon" width="20" height="20">
                   <use href="#icon-arrow-down"></use>
               </svg>
           </span>
       </div>

       <div class="input-field__footer">
           <span class="input-field__helper">Choose your preferred position</span>
           <span class="input-field__error"></span>
       </div>
   </div>

   <!-- Radio -->
   <div class="input-field">
       <div class="input-field__control">
           <fieldset class="input-field__radio-group">
               <legend class="input-field__legend">Payment Method</legend>

               <label class="input-field__radio">
                   <input type="radio" name="payment" value="credit_card" class="input-field__radio-input">
                   <span class="input-field__radio-custom"></span>
                   <span class="input-field__radio-label">Credit Card</span>
               </label>

               <label class="input-field__radio">
                   <input type="radio" name="payment" value="paypal" class="input-field__radio-input">
                   <span class="input-field__radio-custom"></span>
                   <span class="input-field__radio-label">PayPal</span>
               </label>

               <label class="input-field__radio">
                   <input type="radio" name="payment" value="bank_transfer" class="input-field__radio-input">
                   <span class="input-field__radio-custom"></span>
                   <span class="input-field__radio-label">Bank Transfer</span>
               </label>
           </fieldset>
       </div>

       <div class="input-field__footer">
           <span class="input-field__helper">Select your preferred payment method</span>
           <span class="input-field__error"></span>
       </div>
   </div>

   <!-- Checkbox -->
   <div class="input-field">
       <div class="input-field__control">
           <fieldset class="input-field__checkbox-group">
               <legend class="input-field__legend">Interests</legend>

               <label class="input-field__checkbox">
                   <input type="checkbox" name="interests" value="technology" class="input-field__checkbox-input">
                   <span class="input-field__checkbox-custom"></span>
                   <span class="input-field__checkbox-label">Technology</span>
               </label>

               <label class="input-field__checkbox">
                   <input type="checkbox" name="interests" value="sports" class="input-field__checkbox-input">
                   <span class="input-field__checkbox-custom"></span>
                   <span class="input-field__checkbox-label">Sports</span>
               </label>

               <label class="input-field__checkbox">
                   <input type="checkbox" name="interests" value="music" class="input-field__checkbox-input">
                   <span class="input-field__checkbox-custom"></span>
                   <span class="input-field__checkbox-label">Music</span>
               </label>

               <label class="input-field__checkbox">
                   <input type="checkbox" name="interests" value="art" class="input-field__checkbox-input">
                   <span class="input-field__checkbox-custom"></span>
                   <span class="input-field__checkbox-label">Art</span>
               </label>
           </fieldset>

           <span class="input-field__counter">0/4</span>
       </div>

       <div class="input-field__footer">
           <span class="input-field__helper">Select all that apply</span>
           <span class="input-field__error"></span>
       </div>
   </div>
   <!-- Checkbox (Toggle/Switch) -->
   <div class="input-field">
       <div class="input-field__control">
           <label class="input-field__checkbox input-field__checkbox--switch">
               <input type="checkbox" name="notifications" class="input-field__checkbox-input">
               <span class="input-field__checkbox-custom"></span>
               <span class="input-field__checkbox-label">Enable notifications</span>
           </label>
       </div>

       <div class="input-field__footer">
           <span class="input-field__helper">Receive email notifications</span>
           <span class="input-field__error"></span>
       </div>
   </div>

   <!-- Custom Select (with search) -->
   <div class="input-field">
       <div class="input-field__control">
           <button type="button" class="input-field__custom-select has-right-icon has-value">
               <span class="text">Product Name (SKU-123)</span>
               <span class="input-field__icon-right">
                   <svg width="20" height="20">
                       <use href="#icon-arrow-down"></use>
                   </svg>
               </span>
           </button>

           <label class="input-field__label">Product</label>

           <span class="input-field__icon-left">
               <svg width="20" height="20">
                   <use href="#icon-search"></use>
               </svg>
           </span>

           <span class="input-field__counter">1/1</span>

           <button type="button" class="input-field__clear">
               <svg width="16" height="16">
                   <use href="#icon-cancel"></use>
               </svg>
           </button>
       </div>

       <div class="input-field__dropdown">
           <div class="search-group">
               <div class="search-group__icon-container">
                   <svg width="16" height="16">
                       <use href="#icon-search"></use>
                   </svg>
               </div>
               <input type="text" class="search-group__input-search" placeholder="Search...">
           </div>
           <ul class="input-field__options" role="listbox">
               <li class="input-field__option" data-value="1">Product 1 (SKU-001)</li>
               <li class="input-field__option" data-value="2">Product 2 (SKU-002)</li>
               <li class="input-field__option" data-value="3">Product 3 (SKU-003)</li>
           </ul>
       </div>

       <div class="input-field__footer">
           <span class="input-field__helper">Search and select a product</span>
           <span class="input-field__error"></span>
       </div>

       <input type="hidden" name="product_id" class="input-field__hidden-value" value="1">
   </div>