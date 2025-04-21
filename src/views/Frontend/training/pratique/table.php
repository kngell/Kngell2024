<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/pratique/table') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class="table-container">
      <div class="table-container--row">
         <section class="table-container__header">
            <h1>Customer Orders</h1>
            <div class="search-box">
               <input type="search" name="search" id="search" placeholder="Search Data..."
                  class="search-box__search-input">
               <span class="search-box__icon-container">
                  <i class="fa-solid fa-magnifying-glass"></i>
               </span>
            </div>
            <div class="export-box">
               <label for="export-box__files" class="export-box__files" title="export-files"></label>
               <input type="checkbox" name="export-files" id="export-box__files" class="export-box__input-checkbox">
               <div class="export-box__options">
                  <label>Export as&nbsp;&#10140;</label>
                  <label for="export-box__files" id="exportPdf">
                     PDF <img src="../../../../assets/img/pdf.png" alt="PDF" class="export-box__options--pdf"></label>
                  <label for="export-box__files" id="exportJson">JSON <img src="../../../../assets/img/json.png"
                        alt="PDF" class="export-box__options--pdf"></label>
                  <label for="export-box__files" id="exportCsv">CSV <img src="../../../../assets/img/csv.png" alt="PDF"
                        class="export-box__options--pdf"></label>
                  <label for="export-box__files" id="exportXls">XSL <img src="../../../../assets/img/excel.png"
                        alt="PDF" class="export-box__options--pdf"></label>

               </div>
            </div>
         </section>
         <section class="table-container__body">
            <table class="table">
               <thead class="table__head">
                  <tr class="table-row table__head--row">
                     <th class="table-heading">Order ID <span class="table-heading__icon">&UpArrow;</span></th>
                     <th class="table-heading">Customer <span class="table-heading__icon">&UpArrow;</span></th>
                     <th class="table-heading">Location <span class="table-heading__icon">&UpArrow;</span></th>
                     <th class="table-heading">Order Date <span class="table-heading__icon">&UpArrow;</span></th>
                     <th class="table-heading">Status <span class="table-heading__icon">&UpArrow;</span></th>
                     <th class="table-heading">Price <span class="table-heading__icon">&UpArrow;</span></th>
                  </tr>
               </thead>
               <tbody class="table__body">
                  <tr class="table-row table__body--row">
                     <td class="table-data table__body--row-data">001</td>
                     <td class="table-data table__body--row-data name">
                        <div class="name__img-container">
                           <img src="../../../../assets/img/avatar.png" alt="Customer Image"
                              class="name__img-container--img">
                           <p class="name__img-container--text">John Doe</p>
                        </div>
                     </td>
                     <td class="table-data table__body--row-data">Zoétélé</td>
                     <td class="table-data table__body--row-data">17 Dec, 2002</td>
                     <td class="table-data table__body--row-data">
                        <p class="table__body--row-data-status delivered">Delivered</p>
                     </td>
                     <td class="table-data table__body--row-data"><strong>$1200</strong></td>
                  </tr>
               </tbody>
            </table>
         </section>
      </div>

   </div>

   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/frontend/training/table/main') ?>

<?php $this->end();