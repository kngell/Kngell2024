 <body>
     <div class="container dashboard">
         <header class="dashboard__header header"></header>
         <aside class="dashboard__aside aside"></aside>
         <main class="dashboard__main main"></main>
         <footer class="dasboard__footer foot"></footer>
     </div>
 </body>
 <main class="dashboard__main main">
     <div class="product span-all">
         <div class="product__header"></div>

         <div class="product__body">
             <form action="" method="post" class="product__body-frm" id="product-frm">

             </form>
         </div>
         <div class="product__footer buttons-group">

         </div>
     </div>
 </main>

 <form action="" method="post" class="product__body-frm" id="product-frm">
     <div class="product__body-frm--left">
         <div class="frm-section general-information">
             <h4 class="frm-section__title">
                 General Information
             </h4>
             <div class="frm-section__body">
                 <div class="input-box">
                     <input type="text" class="input-box__input" id="product-name"
                         placeholder="Type product name here..." value="Smartwatch E2">
                     <label for="product-name" class="input-box__label">Product Name</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="input-box">
                     <textarea name="product-description" id="product-description" class="input-box__textarea"
                         placeholder="Type product description here. . .">Smartwatch for men women notify you incoming calls, SMS notifications. when you connect the smartphone with fitness tracker. Connect fitness tracker with your phone, you will never miss a call and a message. The smart watches for android phones will vibrate to alert you if your phone receives any notifications. You can reject calls and view message directly from your watch. A best gift for family and friends</textarea>
                     <label for="product-description" class="input-box__label">Product Description</label>
                     <span class="input-box__hint-text"></span>
                 </div>
             </div>
         </div>
         <div class="frm-section media">
             <h4 class="frm-section__title">
                 Media
             </h4>
             <div class="frm-section__body">
                 <div class="input-box span-all">
                     <h6 class="input-box__media-title">Photo</h6>
                     <div class="input-box__media-upload">
                         <div class="media-preview">
                             <div class="media-preview__item">
                                 <div class="media-preview__item--img-container">
                                     <img src="<?= $this->asset('img/camera.png') ?>" alt="Product Image Camera"
                                         class="image">
                                 </div>
                                 <div class="media-preview__item--icon-container">
                                     <svg class="icon success" aria-label="Success" role="img">
                                         <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-success">
                                         </use>
                                     </svg>
                                 </div>
                                 <button class="media-preview__item--remove" type="button">
                                     <span class="btn__icon">
                                         <svg class="icon cancel" aria-label="Cancel" role="img">
                                             <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel">
                                             </use>
                                         </svg>
                                     </span>
                                 </button>

                             </div>
                             <div class="media-preview__item">
                                 <div class="media-preview__item--img-container">
                                     <img src="<?= $this->asset('img/features-1.png') ?>" alt="Product Image Features"
                                         class="image">
                                 </div>
                                 <div class="media-preview__item--icon-container">
                                     <svg class="icon success" aria-label="Success" role="img">
                                         <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-success">
                                         </use>
                                     </svg>
                                 </div>
                                 <button class="media-preview__item--remove" type="button">
                                     <span class="btn__icon">
                                         <svg class="icon cancel" aria-label="Cancel" role="img">
                                             <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel">
                                             </use>
                                         </svg>
                                     </span>
                                 </button>
                             </div>
                             <div class="media-preview__item">
                                 <div class="media-preview__item--img-container">
                                     <img src="<?= $this->asset('img/features-2.png') ?>" alt="Product Image Features"
                                         class="image">
                                 </div>
                                 <div class="media-preview__item--icon-container">
                                     <svg class="icon success" aria-label="Success" role="img">
                                         <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-success">
                                         </use>
                                     </svg>
                                 </div>
                                 <button class="media-preview__item--remove" type="button">
                                     <span class="btn__icon">
                                         <svg class="icon cancel" aria-label="Cancel" role="img">
                                             <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel">
                                             </use>
                                         </svg>
                                     </span>
                                 </button>
                             </div>
                         </div>
                         <input type="file" class="media-file" id="product-images" accept="image/*" multiple>
                         <div class="media-avatar  not-empty">
                             <svg class="icon media-photo" aria-label="Media Photo Avatar" role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-mediaphoto">
                                 </use>
                             </svg>
                         </div>
                         <span class="media-text">Drag and drop image here, or click
                             to
                             browse</span>
                         <label for="product-images" class="btn btn--secondary btn--md-compact">
                             <span class="btn__label">Add Image</span>
                         </label>
                     </div>

                 </div>
                 <div class="input-box span-all">
                     <h6 class="input-box__media-title">Video</h6>
                     <div class="input-box__media-upload">
                         <div class="media-preview">
                             <div class="media-preview__item">
                                 <div class="media-preview__item--img-container">
                                     <img src="<?= $this->asset('img/camera.png') ?>" alt="Product Image Camera"
                                         class="image">
                                 </div>
                                 <div class="media-preview__item--icon-container">
                                     <svg class="icon success" aria-label="Success" role="img">
                                         <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-success">
                                         </use>
                                     </svg>
                                 </div>
                                 <button class="media-preview__item--remove" type="button">
                                     <span class="btn__icon">
                                         <svg class="icon cancel" aria-label="Cancel" role="img">
                                             <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel">
                                             </use>
                                         </svg>
                                     </span>
                                 </button>

                             </div>

                         </div>
                         <input type="file" class="media-file" id="product-video" accept="image/*" multiple>
                         <div class="media-avatar not-empty">
                             <svg class="icon media-video" aria-label="Media Video Avatar" role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-mediavideo">
                                 </use>
                             </svg>
                         </div>
                         <span class="media-text">Drag and drop video here, or click add
                             video</span>
                         <label for="product-images" class="btn btn--secondary btn--md-compact">
                             <span class="btn__label">Add Video</span>
                         </label>
                     </div>
                 </div>
             </div>
         </div>
         <div class="frm-section pricing">
             <h4 class="frm-section__title span-all">
                 Pricing
             </h4>
             <div class="frm-section__body span-all">
                 <div class="input-box span-all">
                     <div class="input-box__input">
                         <span class="input-box__prefix">
                             <svg class="icon dollar" aria-label="Dollar" role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-dollar"></use>
                             </svg>
                         </span>
                         <div class="input-box__typing-area">
                             <input type="text" id="base-price" placeholder="Type base price here..."
                                 class="input-box__field" value="400.00" />
                         </div>

                     </div>
                     <label for="base-price" class="input-box__label">Base price</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="input-box">
                     <div class="input-box__input">
                         <!-- <span class="input-box__prefix">
                                </span> -->
                         <div class="input-box__typing-area">
                             <input type="text" id="discount-type" placeholder="Select discount type"
                                 class="input-box__field" value="No Discount" />
                         </div>
                         <span class="input-box__suffix"> <svg class="icon arrow-down" aria-label="Arrow down"
                                 role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                 </use>
                             </svg></span>
                     </div>
                     <label for="discount-type" class="input-box__label">Discount Type</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="input-box">
                     <input type="text" class="input-box__input" id="discount-percentage"
                         placeholder="Type discount precentage. . ." value="0%">
                     <label for="discount-percentage" class="input-box__label">Discount Precentage
                         (%)</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="input-box">
                     <div class="input-box__input">
                         <!-- <span class="input-box__prefix">
                                </span> -->
                         <div class="input-box__typing-area">
                             <input type="text" id="tax-class" placeholder="Select a tax class" class="input-box__field"
                                 value="Tax Free" />
                         </div>
                         <span class="input-box__suffix"> <svg class="icon arrow-down" aria-label="Arrow down"
                                 role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                 </use>
                             </svg></span>
                     </div>
                     <label for="tax-class" class="input-box__label">Tax Class</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="input-box">
                     <input type="text" class="input-box__input" id="vat-amount" placeholder="Type VAT amount here..."
                         value="0%">
                     <label for="vat-amount" class="input-box__label">VAT Amount (%)</label>
                     <span class="input-box__hint-text"></span>
                 </div>
             </div>
         </div>
         <div class="frm-section inventory">
             <h4 class="frm-section__title span-all">
                 Inventory
             </h4>
             <div class="frm-section__body span-all">
                 <div class="input-box">
                     <input type="text" class="input-box__input" id="sku" placeholder="Type SKU here..." value="302002">
                     <label for="sku" class="input-box__label">SKU</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="input-box">
                     <input type="text" class="input-box__input" id="stock-quantity"
                         placeholder="Type stock quantity here..." value="124">
                     <label for="stock-quantity" class="input-box__label">Stock Quantity</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="input-box">
                     <div class="input-box__input">
                         <!-- <span class="input-box__prefix">
                                </span> -->
                         <div class="input-box__typing-area">
                             <input type="text" id="stock-status" placeholder="Select stock status"
                                 class="input-box__field" value="active" />
                         </div>
                         <span class="input-box__suffix"> <svg class="icon arrow-down" aria-label="Arrow down"
                                 role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                 </use>
                             </svg></span>
                     </div>
                     <label for="stock-status" class="input-box__label">Stock Status</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="input-box">
                     <input type="text" class="input-box__input" id="bar-code" placeholder="Product barcode. . ."
                         value="0984939101123">
                     <label for="bar-code" class="input-box__label">Barcode</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="input-box span-all">
                     <input type="checkbox" name="allow-backorders" id="allow-backorders" class="input-box__input" />
                     <label for="allow-backorders" class="input-box__label">Allow Backorders</label>
                     <span class="input-box__hint-text"></span>
                 </div>
             </div>
         </div>
         <div class="frm-section variation">
             <h4 class="frm-section__title span-all">
                 Variation
             </h4>
             <div class="frm-section__body span-all">
                 <div class="input-box">
                     <div class="input-box__input">
                         <div class="input-box__typing-area">
                             <input type="text" id="variation-type" placeholder="Select a variation"
                                 class="input-box__field" />
                         </div>
                         <span class="input-box__suffix"> <svg class="icon arrow-down" aria-label="Arrow down"
                                 role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                 </use>
                             </svg></span>
                     </div>
                     <label for="variation-type" class="input-box__label">Variation Type</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="variation-group">
                     <div class="input-box">
                         <input type="text" class="input-box__input" id="variation" placeholder="Variation. . .">
                         <label for="variation" class="input-box__label">Variation</label>
                         <span class="input-box__hint-text"></span>
                     </div>
                     <label class="btn btn--icon-only btn--md  btn--danger-light">
                         <span class="btn__icon">
                             <svg class="icon cancel" aria-label="Cancel" role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel"></use>
                             </svg>
                         </span>
                     </label>
                 </div>
                 <div class="input-box">
                     <div class="input-box__input">
                         <div class="input-box__typing-area">
                             <input type="text" id="variation-type2" placeholder="Select a variation"
                                 class="input-box__field" />
                         </div>
                         <span class="input-box__suffix"> <svg class="icon arrow-down" aria-label="Arrow down"
                                 role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                 </use>
                             </svg></span>
                     </div>
                     <label for="variation-type2" class="input-box__label">Variation Type</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="variation-group">
                     <div class="input-box">
                         <input type="text" class="input-box__input" id="variation2" placeholder="Variation. . .">
                         <label for="variation2" class="input-box__label">Variation</label>
                         <span class="input-box__hint-text"></span>
                     </div>
                     <label class="btn btn--icon-only btn--md  btn--danger-light">
                         <span class="btn__icon">
                             <svg class="icon cancel" aria-label="Cancel" role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel"></use>
                             </svg>
                         </span>
                     </label>
                 </div>
                 <div class="button-container">
                     <label class="btn btn--secondary btn--md-compact btn--icon-left">
                         <span class="btn__icon">
                             <svg class="icon plus" aria-label="Add" role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-plus"></use>
                             </svg>
                         </span>
                         <span class="btn__label">Add Variant</span>
                     </label>
                 </div>
             </div>
         </div>
         <div class="frm-section shipping">
             <h4 class="frm-section__title span-all">
                 Shipping
             </h4>
             <div class="frm-section__body span-all">
                 <div class="input-box  blue-check span-all">
                     <input type="checkbox" class="input-box__input" id="is-physical-product">
                     <label for="is-physical-product" class="input-box__label">This is a physical
                         product</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="input-box">
                     <input type="text" class="input-box__input" id="product-weight" placeholder="Product weight...">
                     <label for="product-weight" class="input-box__label">Weight</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="input-box">
                     <input type="text" class="input-box__input" id="product-height" placeholder="Height (cm)...">
                     <label for="product-height" class="input-box__label">Height</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="input-box">
                     <input type="text" class="input-box__input" id="product-length" placeholder="Length (cm)...">
                     <label for="product-length" class="input-box__label">Length</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="input-box">
                     <input type="text" class="input-box__input" id="product-width" placeholder="Width (cm)...">
                     <label for="product-width" class="input-box__label">Width</label>
                     <span class="input-box__hint-text"></span>
                 </div>

             </div>
         </div>
     </div>
     <div class="product__body-frm--right">
         <div class="frm-section category">
             <h4 class="frm-section__title">
                 Category
             </h4>
             <div class="frm-section__body">
                 <div class="input-box">
                     <div class="input-box__input">
                         <div class="input-box__typing-area">
                             <input type="text" id="category" placeholder="Select a category" class="input-box__field"
                                 value="Watch" />
                         </div>
                         <span class="input-box__suffix"> <svg class="icon arrow-down" aria-label="Arrow down"
                                 role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                 </use>
                             </svg></span>
                     </div>
                     <label for="category" class="input-box__label">Category</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="input-box">
                     <div class="input-box__input">
                         <div class="input-box__typing-area">
                             <input type="text" id="sub-category" placeholder="Select a subcategory"
                                 class="input-box__field" value="Watch" />
                         </div>
                         <span class="input-box__suffix"> <svg class="icon arrow-down" aria-label="Arrow down"
                                 role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                 </use>
                             </svg></span>
                     </div>
                     <label for="sub-category" class="input-box__label">Subcategory</label>
                     <span class="input-box__hint-text"></span>
                 </div>
                 <div class="input-box">
                     <div class="input-box__input">
                         <div class="input-box__typing-area">
                             <input type="text" id="product-tag" placeholder="Select a product tag"
                                 class="input-box__field" />
                             <div class="tag-preview">
                                 <button type="button" class="btn btn--secondary btn--md-tags btn--icon-right">
                                     <span class="btn__icon"><svg class="icon cancel" aria-label="Cancel" role="img">
                                             <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel">
                                             </use>
                                         </svg></span>
                                     <span class="btn__label">Watch</span>
                                 </button>
                                 <button type="button" class="btn btn--secondary btn--md-tags btn--icon-right">
                                     <span class="btn__icon"><svg class="icon cancel" aria-label="Cancel" role="img">
                                             <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel">
                                             </use>
                                         </svg></span>
                                     <span class="btn__label">Gadget</span>
                                 </button>
                             </div>
                         </div>
                         <span class="input-box__suffix"> <svg class="icon arrow-down" aria-label="Arrow down"
                                 role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                 </use>
                             </svg></span>
                     </div>
                     <label for="product-tag" class="input-box__label">Product Tag</label>
                     <span class="input-box__hint-text"></span>
                 </div>
             </div>
         </div>
         <div class="frm-section status">
             <div class="title-group">
                 <h4 class="frm-section__title">
                     Status
                 </h4>
                 <span class="frm-section__draft">Draft</span>
             </div>
             <div class="frm-section__body span-all">
                 <div class="input-box">
                     <div class="input-box__input">
                         <div class="input-box__typing-area">
                             <input type="text" id="product-status" placeholder="Select a status"
                                 class="input-box__field" />
                         </div>
                         <span class="input-box__suffix"> <svg class="icon arrow-down" aria-label="Arrow down"
                                 role="img">
                                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                                 </use>
                             </svg></span>
                     </div>
                     <label for="product-status" class="input-box__label">Status</label>
                     <span class="input-box__hint-text"></span>
                 </div>

             </div>
         </div>
     </div>
 </form>

 <!-- Select Box -->
 <div class="input-box">
     <div class="input-box__container">
         <select id="discount-type" name="discount-type" class="input-box__select">
             <option desabled selected>Select discount type</option>
             <option value="none">No Discount</option>
             <option value="percent">Percentage</option>
             <option value="fixed">Fixed Amount</option>
             <option value="seasonal">Seasonal Offer</option>
         </select>
         <span class="input-box__suffix">
             <svg class="icon arrow-down" aria-label="Arrow- Down" role="img">
                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down"></use>
             </svg>
         </span>
     </div>
     <label for="base-price" class="input-box__label">Discount Type</label>
     <span class="input-box__hint-text"></span>
 </div>

 <!-- Input box icon left -->
 <div class="input-box">
     <div class="input-box__container">
         <span class="input-box__prefix">
             <svg class="icon" role="img" aria-label="Prefix">
                 <use href="/public/assets/img/icons-sprite.svg#icon-arrow-down"></use>
             </svg>
         </span>
         <input type="text" class="input-box__input is-invalid" id="product-frm-price-6" name="price"
             placeholder="Type base price here...">
         <div class="input-box__hint-text invalid-feedback">Base Price is required.</div>
     </div><label class="input-box__label" for="product-frm-price-6">Base Price</label>
 </div>

 <!-- Select Box Currency -->
 <div class="input-box">
     <div class="input-box__container">
         <span class="input-box__prefix--currency">
             <select class="input-box__select">
                 <option value="USD">USD</option>
                 <option value="EUR">EUR</option>
                 <option value="GBP">GBP</option>
             </select>
         </span>
         <input type="number" class="input-box__input" id="product-frm-price-6" name="price" placeholder="0.00"
             step="0.01">
         <div class="input-box__hint-text invalid-feedback">Base Price is required.</div>
     </div>
     <label class="input-box__label" for="product-frm-price-6">Base Price</label>
 </div>

 <!-- Select Box Currency Enhenced: Using combo container for better borders -->
 <div class="input-box">
     <div class="input-box__container input-box__container--currency-combo">
         <span class="input-box__prefix input-box__prefix--currency">
             <select class="input-box__select">
                 <option value="USD">USD</option>
                 <option value="EUR">EUR</option>
                 <option value="GBP">GBP</option>
             </select>
         </span>
         <input type="number" class="input-box__input" id="product-frm-price-6" name="price" placeholder="0.00"
             step="0.01">
     </div>
     <label class="input-box__label" for="product-frm-price-6">Base Price</label>
     <div class="input-box__hint-text invalid-feedback">Base Price is required.</div>
 </div>

 <div class="input-box__container--currency-combo">
     <span class="input-box__prefix--currency"><select class="input-box__select" name="currency">
             <option value="USD">USD</option>
             <option value="EUR">EUR</option>
             <option value="GBP">GBP</option>
         </select>
     </span>
     <input type="number" class="input-box__input is-invalid" id="product-frm-base_price-6" name="price"
         placeholder="0.00">

 </div>
 <div class="input-box__hint-text invalid-feedback">Base Price is required.</div>
 <!-- Checkbox layout -->
 <div class="input-box span-all">
     <input type="checkbox" name="allow-backorders" id="allow-backorders" class="input-box__input" />
     <label for="allow-backorders" class="input-box__label">Allow Backorders</label>
     <span class="input-box__hint-text"></span>
 </div>

 <div class="input-box">
     <div class="input-box__container">
         <select id="product-tag" name="product-tag" class="input-box__select">
             <option desabled selected>Select product tag</option>
             <option value="none">Watch</option>
             <option value="percent">Gadget</option>
             <option value="fixed">Fixed Amount</option>
             <option value="seasonal">Seasonal Offer</option>
         </select>
         <span class="input-box__suffix">
             <svg class="icon arrow-down" aria-label="Arrow- Down" role="img">
                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                 </use>
             </svg>
         </span>
         <div class="tag-preview">

         </div>
     </div>

     <label for="base-price" class="input-box__label">ProductTags</label>
     <span class="input-box__hint-text"></span>
 </div>

 <div class="tag-preview">
     <button type="button" class="btn btn--secondary btn--md-tags btn--icon-right">
         <span class="btn__icon"><svg class="icon cancel" aria-label="Cancel" role="img">
                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel">
                 </use>
             </svg></span>
         <span class="btn__label">Watch</span>
     </button>
     <button type="button" class="btn btn--secondary btn--md-tags btn--icon-right">
         <span class="btn__icon"><svg class="icon cancel" aria-label="Cancel" role="img">
                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel">
                 </use>
             </svg></span>
         <span class="btn__label">Gadget</span>
     </button>
 </div>

 <div class="product__footer buttons-group">
     <div class="completeness">
         <span class="completeness__text">
             Product completion:
         </span>
         <div class="completeness__progress-container">
             <div class="completeness-progress">
                 <div class="completeness-progress--bar" style="width: 70%;"></div>
             </div>
             <span class="completeness-percentage">70%</span>
         </div>

     </div>
     <div class="buttons">
         <button class="btn btn--outlined btn--md-compact btn--icon-left">
             <span class="btn__icon"><svg class="icon cancel" aria-label="Cancel" role="img">
                     <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel"></use>
                 </svg></span>
             <span class="btn__label">Cancel</span>
         </button>
         <button class="btn btn--primary btn--md-compact btn--icon-left" form="product-frm">
             <span class="btn__icon"><svg class="icon plus" aria-label="Plus" role="img">
                     <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-plus"></use>
                 </svg></span>
             <span class="btn__label">Add product</span>
         </button>
     </div>
 </div>

 <div class="product__body">
     <?=$message ?? ''?>
     <?= $product_form ?? '' ?>
 </div>
 <?php
 $inputBox1 = [
     'key' => 'discount_type',
     'name' => 'discount-type',
     'label' => 'Discount Type',
     'type' => 'select',
     'options' => [
         '' => '-- Select discount type --',
         'no-discount' => 'No Discount',
         'percent' => 'percent',
         'fixed_amount' => 'fixed Amount',
         'free_shipping' => 'free Shipping',
     ],
     'suffixIcon' => 'icon-arrow-down',
     'hint' => 'Choose the discount type',
 ];
                                     $inputBox2 = [
                                         'key' => 'discount_percentage',
                                         'name' => 'discount-percentage',
                                         'label' => 'Discount Percentage (%)',
                                         'placeholder' => 'Type discount percentage...',
                                         'type' => 'text',
                                     ];
                                     ?>