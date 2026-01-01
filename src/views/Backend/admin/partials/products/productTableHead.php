 <thead class="table__head">
     <tr class="table__head--row">
         <th scope="col" class="table__head--row-cell">
             <div class="header-cell">
                 <div class="header-cell__top-row">
                     <!-- Checkbox for select all -->
                     <div class="checkbox-box">
                         <span id="select-all-label" class="visually-hidden">Select all products</span>
                         <input type="checkbox" id="select-all" class="checkbox-box__input"
                             aria-labelledby="select-all-label" />
                         <label for="select-all" class="checkbox-box__label">products</label>
                     </div>
                     <!-- Separate dropdown for advanced selection -->
                     <div class="dropdown-container">
                         <button class="dropdown-container__btn" aria-expanded="false"
                             aria-controls="advanced-selection">
                             <?= $icon ?>
                         </button>

                         <div id="advanced-selection" class="dropdown-container__dropdown" hidden>
                             <!-- Advanced selection options -->
                         </div>
                     </div>
                 </div>
                 <span class="header-cell__hint-text"></span>
             </div>
         </th>
         <th scope="col" class="table__head--row-cell">
             <span class="header-cell">SKU</span>
         </th>
         <th scope="col" class="table__head--row-cell">
             <span class="header-cell">Category</span>
         </th>
         <th scope="col" class="table__head--row-cell">
             <div class="header-cell">
                 <div class="header-cell__top-row">
                     <span>Stock</span>
                     <div class="dropdown-container">
                         <button class="dropdown-container__btn" aria-expanded="false"
                             aria-controls="advanced-selection">
                             <?= $icon ?>
                         </button>

                         <div id="advanced-selection" class="dropdown-container__dropdown" hidden>
                             <!-- Advanced selection options -->
                         </div>
                     </div>
                 </div>
                 <span class="header-cell__hint-text"></span>
             </div>

         </th>
         <th scope="col" class="table__head--row-cell">
             <div class="header-cell">
                 <div class="header-cell__top-row">
                     <!-- Header label -->
                     <span>Price</span>
                     <!-- Separate dropdown for advanced selection -->
                     <div class="dropdown-container">
                         <button class="dropdown-container__btn" aria-expanded="false"
                             aria-controls="advanced-selection">
                             <?= $icon ?>
                         </button>

                         <div id="advanced-selection" class="dropdown-container__dropdown" hidden>
                             <!-- Advanced selection options -->
                         </div>
                     </div>
                 </div>
                 <span class="header-cell__hint-text"></span>
             </div>
         </th>
         <th scope="col" class="table__head--row-cell">
             <div class="header-cell">
                 <div class="header-cell__top-row">
                     <!-- Header label -->
                     <span>Status</span>
                     <!-- Separate dropdown for advanced selection -->
                     <div class="dropdown-container">
                         <button class="dropdown-container__btn" aria-expanded="false"
                             aria-controls="advanced-selection">
                             <?= $icon ?>
                         </button>

                         <div id="advanced-selection" class="dropdown-container__dropdown" hidden>
                             <!-- Advanced selection options -->
                         </div>
                     </div>
                 </div>
                 <span class="header-cell__hint-text"></span>
             </div>
         </th>
         <th scope="col" class="table__head--row-cell">

             <div class="header-cell">
                 <div class="header-cell__top-row">
                     <!-- Header label -->
                     <span>Added</span>
                     <!-- Separate dropdown for advanced selection -->
                     <div class="dropdown-container">
                         <button class="dropdown-container__btn" aria-expanded="false"
                             aria-controls="advanced-selection">
                             <?= $icon ?>
                         </button>

                         <div id="advanced-selection" class="dropdown-container__dropdown" hidden>
                             <!-- Advanced selection options -->
                         </div>
                     </div>
                 </div>
                 <span class="header-cell__hint-text"></span>
             </div>
         </th>
         <th scope="col" class="table__head--row-cell" aria-label="Actions">
             <span class="header-cell">Action</span>
         </th>
     </tr>
 </thead>