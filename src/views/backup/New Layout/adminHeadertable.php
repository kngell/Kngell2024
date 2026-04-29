 <div class="title span-all">
     <div class="title-left">
         <h4 class="title-left__text">Product</h4>
         <nav class="title-left__breadcrumbs">
             <ul class="breadcrumbs-list">
                 <li class="breadcrumbs-list__item">
                     <a href="#" class="breadcrumbs-list__item--link">Dashboard</a>
                 </li>
                 <li class="breadcrumbs-list__item">
                     <a href="#" class="breadcrumbs-list__item--link active">Product List</a>
                 </li>
             </ul>
         </nav>
     </div>
     <div class="title-right">
         <button class="btn btn--secondary btn--md-compact btn--icon-left">
             <span class="btn__icon">
                 <svg class="icon export" aria-label="Export" role="img">
                     <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-export"></use>
                 </svg>
             </span>
             <span class="btn__label">Export</span>
         </button>
         <button class="btn btn--primary btn--md-compact btn--icon-left">
             <span class="btn__icon">
                 <svg class="icon plus" aria-label="Plus" role="img">
                     <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-plus"></use>
                 </svg>
             </span>
             <span class="btn__label">Add Product</span>
         </button>
     </div>
 </div>