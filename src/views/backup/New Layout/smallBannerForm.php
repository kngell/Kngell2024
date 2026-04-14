 <form action="" class="small-banner-frm">
     <div class="small-banner-frm__left">
         <div class="core-configuration">
             <div class="core-configuration__header">
                 <div class="core-configuration__header-left">
                     <div class="icon-container">
                         <svg class="icon cancel" aria-label="Cancel" role="img">
                             <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-edit2"></use>
                         </svg>
                     </div>
                     <h6 class="title">
                         Basic Information
                     </h6>
                 </div>
                 <span class="core-configuration__header-right">* Required
                 </span>

             </div>
             <div class="core-configuration__body">
                 <div class="form-grid">
                     <div class="input-field">
                         <div class="input-field__wrapper">
                             <select class="input-field__select has-right-icon" id="my-select">
                                 <button>
                                     <selectedcontent></selectedcontent>
                                 </button>
                                 <option value="" class="input-field__option" selected disabled>Select
                                     Position</option>
                                 <option value="top" class="input-field__option">Top</option>
                                 <option value="middle" class="input-field__option">Middle</option>
                                 <option value="bottom" class="input-field__option">Bottom</option>
                             </select>
                             <span class="input-field__icon-right">
                                 <svg class="icon cancel" aria-label="Cancel" role="img">
                                     <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                     </use>
                                 </svg>
                             </span>
                         </div>
                         <div class="input-field__footer">
                             <span class="input-field__helper">Helper text</span>
                         </div>
                     </div>
                     <div class="input-field">
                         <div class="input-field__body">
                             <input type="text" class="input-field__input" id="name1" placeholder=" " />
                             <label for="name1" class="input-field__label">
                                 Page Target
                             </label>
                         </div>
                         <span class="input-field__counter has-right-icon">0/50</span>
                     </div>
                 </div>
                 <div class="input-field">
                     <div class="input-field__body">
                         <input type="text" class="input-field__input" id="name1" placeholder=" " />
                         <label for="name1" class="input-field__label">
                             Sort Order
                         </label>
                     </div>
                 </div>
             </div>
         </div>
         <div class="product-relationship">
             <div class="product-relationship__header">
                 <div class="product-relationship__header-left">
                     <div class="icon-container">
                         <svg class="icon cancel" aria-label="Cancel" role="img">
                             <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-link"></use>
                         </svg>
                     </div>
                     <h6 class="title">
                         Product Relationship
                     </h6>
                 </div>
             </div>
             <div class="product-relationship__body">
                 <div class="product-row">
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
                 </div>
                 <div class="product-card">
                     <div class="product-card__left">
                         <div class="img-container">
                             <img src="#" class="image" alt="">
                         </div>
                         <div class="data-container">
                             <h6 class="title">
                                 Product Name
                             </h6>
                             <span class="sku">PS0001</span>
                             <span class="short-description">Short description of the product.</span>
                         </div>
                     </div>
                     <div class="product-card__right">
                         <button type="button" class="btn btn--icon-only">
                             <svg class="icon cancel" aria-label="Cancel" role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-close"></use>
                             </svg>
                         </button>
                     </div>
                 </div>
             </div>
         </div>
         <div class="custom-override">
             <div class="custom-override__header">
                 <div class="custom-override__header-left">
                     <div class="icon-container">
                         <svg class="icon cancel" aria-label="Cancel" role="img">
                             <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-edit"></use>
                         </svg>
                     </div>
                     <h6 class="title">
                         Custom Content Override
                     </h6>
                 </div>
             </div>
             <div class="custom-override__body">
                 <div class="column">
                     <div class="input-field">
                         <div class="input-field__body">
                             <input type="text" class="input-field__input" id="name1" placeholder=" " />
                             <label for="name1" class="input-field__label">
                                 Custom Title
                             </label>
                         </div>
                     </div>
                     <div class="input-field">
                         <div class="input-field__body">
                             <input type="text" class="input-field__input" id="name1" placeholder=" " />
                             <label for="name1" class="input-field__label">
                                 Custom Subtitle
                             </label>
                         </div>
                     </div>
                     <div class="input-field">
                         <div class="input-field__body">
                             <input type="text" class="input-field__input" id="name1" placeholder=" " />
                             <label for="name1" class="input-field__label">
                                 Button Text
                             </label>
                         </div>
                     </div>
                 </div>
                 <div class="column">
                     <div class="input-field">
                         <div class="input-field__body">
                             <input type="text" class="input-field__input" id="name1" placeholder=" " />
                             <label for="name1" class="input-field__label">
                                 Custom Description
                             </label>
                         </div>
                     </div>
                     <div class="input-field">
                         <div class="input-field__body">
                             <input type="text" class="input-field__input" id="name1" placeholder=" " />
                             <label for="name1" class="input-field__label">
                                 Button Link
                             </label>
                         </div>
                     </div>

                 </div>
             </div>
         </div>
     </div>
     <div class="small-banner-frm__right media">
         <div class="media-section">
             <div class="media-section__header">
                 <div class="media-section__header-left">
                     <div class="icon-container">
                         <svg class="icon cancel" aria-label="Cancel" role="img">
                             <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-image"></use>
                         </svg>
                     </div>
                     <h6 class="title">
                         Media
                     </h6>
                 </div>
                 <span class="media-section__header-right">* Required
                 </span>
             </div>
             <div class="media-section__body">
                 <div class="upload-single" data-state="empty" data-mode="single">
                     <div class="upload-single__icon">
                         <svg>
                             <use href="#icon-upload"></use>
                         </svg>
                     </div>
                     <div class="upload-single__text">
                         <span class="upload-single__main-text">Drag & drop or click to upload</span>
                         <span class="upload-single__hint-text">PNG, JPG, GIF • Max 5MB</span>
                     </div>
                     <input type="file" accept="image/*">
                 </div>
                 <div class="input-field">
                     <div class="input-field__body">
                         <input type="text" class="input-field__input" id="name1" placeholder=" " />
                         <label for="name1" class="input-field__label">
                             Alt Text
                         </label>
                     </div>
                 </div>
             </div>
         </div>
         <div class="display-settings">
             <div class="display-settings__header">
                 <div class="display-settings__header-left">
                     <div class="icon-container">
                         <svg class="icon cancel" aria-label="Cancel" role="img">
                             <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-settings"></use>
                         </svg>
                     </div>
                     <h6 class="title">
                         Display Settings
                     </h6>
                 </div>
             </div>
             <div class="display-settings__body">
                 <div class="options">
                     <div class="options-box selected" data-option="light" role="button">
                         <input type="radio" id="theme-light" style="display: none" name="theme_preference"
                             value="light" checked>
                         <span class="options-box__title">Light Mode</span>
                         <span class="options-box__description">Light theme for bright environments</span>
                     </div>

                     <div class="options-box" data-option="dark" role="button">
                         <input type="radio" id="theme-dark" style="display: none" name="theme_preference" value="dark">
                         <span class="options-box__title">Dark Mode</span>
                         <span class="options-box__description">Dark theme for reduced eye strain</span>
                     </div>
                     <input type="hidden" name="theme_preference" value="light">
                 </div>
                 <div class="status-row">
                     <div class="status-row__left">
                         <p class="title">Active Status</p>
                         <p class="descr">Show Banner on site</p>
                     </div>
                     <div class="status-row__right toggle-switch">
                         <input type="checkbox" id="toggle-1">
                         <label for="toggle-1" class="toggle">
                             <span class="track"></span>
                             <span class="knob"></span>
                         </label>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </form>