   <div class="input-box span-all">
       <h6 class="input-box__media-title">Photo</h6>
       <div class="input-box__media-upload">
           <div class="media-preview">
               <div class="media-preview__item">
                   <div class="media-preview__item--img-container">
                       <img src="<?= $this->asset('img/camera.png') ?>" alt="Product Image Camera" class="image">
                   </div>
                   <div class="media-preview__item--icon-container">
                       <svg class="icon success" aria-label="Success" role="img">
                           <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-success">
                           </use>
                       </svg>
                   </div>
                   <button class="media-preview__item--remove">
                       <span class="btn__icon" type="button">
                           <svg class="icon cancel" aria-label="Cancel" role="img">
                               <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel">
                               </use>
                           </svg>
                       </span>
                   </button>
               </div>
               <div class="media-preview__item">
                   <div class="media-preview__item--img-container">
                       <img src="<?= $this->asset('img/features-1.png') ?>" alt="Product Image Features" class="image">
                   </div>
                   <div class="media-preview__item--icon-container">
                       <svg class="icon success" aria-label="Success" role="img">
                           <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-success">
                           </use>
                       </svg>
                   </div>
                   <button class="media-preview__item--remove">
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
                       <img src="<?= $this->asset('img/features-2.png') ?>" alt="Product Image Features" class="image">
                   </div>
                   <div class="media-preview__item--icon-container">
                       <svg class="icon success" aria-label="Success" role="img">
                           <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-success">
                           </use>
                       </svg>
                   </div>
                   <button class="media-preview__item--remove">
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
           <div class="media-avatar">
               <svg class="icon media-photo" aria-label="Media Photo Avatar" role="img">
                   <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-mediaphoto"></use>
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