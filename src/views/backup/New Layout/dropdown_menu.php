 <div class="input-dropdown">
     <div class="input-dropdown__container">
         <div class="input-dropdown__left-group">
             <div class="icon-container">
                 <svg class="icon cancel" aria-label="Cancel" role="img">
                     <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cancel">
                     </use>
                 </svg>
             </div>
             <select name="" id="input-dropdown__select" class="input-dropdown__select">
                 <option value="" class="input-dropdown__option">Select Position</option>
                 <option value="top" class="input-dropdown__option">Top</option>
                 <option value="middle" class="input-dropdown__option">Middle</option>
                 <option value="bottom" class="input-dropdown__option">Bottom</option>
             </select>
         </div>
         <div class="icon-container">
             <svg class="icon cancel" aria-label="Cancel" role="img">
                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                 </use>
             </svg>
         </div>
     </div>
     <small class="input-dropdown__helper">Helper Message</small>
 </div>


 <!-- Without left icon, no error -->
 <div class="input-dropdown">
     <div class="input-dropdown__container">
         <div class="input-dropdown__left-group">
             <select class="input-dropdown__select">
                 <option value="">Select Position</option>
                 <option value="top">Top</option>
                 <option value="middle">Middle</option>
                 <option value="bottom">Bottom</option>
             </select>
         </div>
         <div class="icon-container">
             <svg class="icon">
                 <use href="img/icons-sprite.svg#icon-arrow-down"></use>
             </svg>
         </div>
     </div>
     <small class="input-dropdown__helper">Helper Message</small>
 </div>

 <!-- With left icon, no error -->
 <div class="input-dropdown">
     <div class="input-dropdown__container">
         <div class="input-dropdown__left-group">
             <div class="icon-container">
                 <svg class="icon">
                     <use href="img/icons-sprite.svg#icon-cancel"></use>
                 </svg>
             </div>
             <select class="input-dropdown__select">
                 <option value="">Select Position</option>
                 <option value="top">Top</option>
                 <option value="middle">Middle</option>
                 <option value="bottom">Bottom</option>
             </select>
         </div>
         <div class="icon-container">
             <svg class="icon">
                 <use href="img/icons-sprite.svg#icon-arrow-down"></use>
             </svg>
         </div>
     </div>
     <small class="input-dropdown__helper">Helper Message</small>
 </div>

 <!-- With error state (helper text will appear) -->
 <div class="input-dropdown input-dropdown--error">
     <div class="input-dropdown__container">
         <div class="input-dropdown__left-group">
             <div class="icon-container">
                 <svg class="icon">
                     <use href="img/icons-sprite.svg#icon-cancel"></use>
                 </svg>
             </div>
             <select class="input-dropdown__select">
                 <option value="">Select Position</option>
                 <option value="top">Top</option>
                 <option value="middle">Middle</option>
                 <option value="bottom">Bottom</option>
             </select>
         </div>
         <div class="icon-container">
             <svg class="icon">
                 <use href="img/icons-sprite.svg#icon-arrow-down"></use>
             </svg>
         </div>
     </div>
     <small class="input-dropdown__helper">This field is required</small>
 </div>


 <div class="input-dropdown">
     <div class="input-dropdown__container">
         <div class="input-dropdown__left-group">
             <select name="" id="input-dropdown__select" class="input-dropdown__select">
                 <option value="" class="input-dropdown__option" selected disabled>Select Position</option>
                 <option value="top" class="input-dropdown__option">Top</option>
                 <option value="middle" class="input-dropdown__option">Middle</option>
                 <option value="bottom" class="input-dropdown__option">Bottom</option>
             </select>
         </div>
         <div class="icon-container">
             <svg class="icon cancel" aria-label="Cancel" role="img">
                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down"></use>
             </svg>
         </div>
     </div>
     <small class="input-dropdown__helper">Helper Message</small>
 </div>

 <div class="input-dropdown">
     <div class="input-dropdown__container">
         <div class="input-dropdown__left-group">
             <select name="" id="input-dropdown__select" class="input-dropdown__select">
                 <button>
                     <selectedcontent></selectedcontent>
                 </button>
                 <option value="" class="input-dropdown__option">Select Position</option>
                 <option value="top" class="input-dropdown__option">Top</option>
                 <option value="middle" class="input-dropdown__option">Middle</option>
                 <option value="bottom" class="input-dropdown__option">Bottom</option>
             </select>
         </div>
         <div class="icon-container">
             <svg class="icon cancel" aria-label="Cancel" role="img">
                 <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down">
                 </use>
             </svg>
         </div>
     </div>
     <small class="input-dropdown__helper">Helper Message</small>
 </div>