   <div class="product-list__search-and-filter">
       <form class="search-form">
           <button type="submit" class="search-form__btn">
               <svg class="icon search" aria-label="Search" role="img">
                   <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-search">
                   </use>
               </svg>
           </button>
           <input type="text" name="search" id="search-form--input-id" class="search-form__input"
               placeholder="Search product. . .">
       </form>
       <div class="right">
           <button class="right__date-picker">
               <span class="icon-container">
                   <svg class="icon calendar" aria-label="Calendar" role="img">
                       <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-calendar">
                       </use>
                   </svg>
               </span>
               <span class="icon-text">Select Dates</span>
           </button>
           <button class="right__filter">
               <span class="icon-container">
                   <svg class="icon slider" aria-label="Slider" role="img">
                       <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-slider">
                       </use>
                   </svg>
               </span>
               <span class="icon-text">Filters</span>
           </button>
       </div>
   </div>