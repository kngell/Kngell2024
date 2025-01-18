<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class="k-container" style="border: 2px dashed green">
      <h1>Hello world</h1>
      <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Temporibus tempore vero quos?</p>
      <div class="k-row u-margin-b-20">
         <div class="k-flex-6 k-flex-md-3 u-margin-b-20 u-margin-b-0-md" style="border: 1px solid red">1
         </div>
         <div class="k-flex-6 k-flex-md-3 u-margin-b-20 u-margin-b-0-md" style="border: 1px solid blue">2</div>
         <div class="k-flex-6 k-flex-md-3" style="border: 1px solid green">3</div>
         <div class="k-flex-6 k-flex-md-3" style="border: 1px solid black">4</div>

         <div class="k-offset-2 k-flex-6" style="border: 1px solid blue;padding: 10px;">Hello offset 2</div>
      </div>
      <div class="k-offset-2" style="border: 1px solid blue;padding: 10px;">Hello offset 2</div>
      <div class="k-offset-12" style="border: 1px solid blue;padding: 10px;">Hello offset 12</div>

      <div class="u-margin-y-0 u-overflow-hidden-vh">Testing</div>

      <!-- Buttons -->
      <div class="u-margin-y-20">
         <a href="#" class="k-btn">Log out</a>
         <a href="#" class="k-btn k-btn-primary">primary</a>
         <a href="#" class="k-btn k-btn-secondary">secondary</a>
         <a href="#" class="k-btn k-btn-success">success</a>
         <a href="#" class="k-btn k-btn-warn">warning</a>
         <a href="#" class="k-btn k-btn-danger">danger</a>
         <button class="k-btn k-btn-primary k-btn-sm"><i class="ri-arrow-up-line u-margin-r-10"></i>Export</button>
         <button class="k-btn k-btn-primary k-btn-square"><i class="ri-arrow-up-line"></i></button>
         <a href="#" class="k-btn k-btn-link k-btn-secondary">Buttun link</a>
      </div>
      <!-- Badges -->
      <div class="u-margin-y-20">
         <div class="k-badge k-badge-success">Completed</div>
         <div class="k-badge k-badge-secondary">In progress</div>
         <div class="k-badge k-badge-warn">Pending</div>
         <div class="k-badge k-badge-danger">Canceled</div>
      </div>
      <!-- avatar -->
      <div class="u-margin-y-20">
         <div class="k-avatar k-avatar-30"><img src="../../../assets/img/admin/user-f-1.jpg" alt="Avatar"
               class="k-avatar__img"></div>
         <div class="k-avatar k-avatar-35"><img src="../../../assets/img/admin/user-f-1.jpg" alt="Avatar"
               class="k-avatar__img"></div>
         <div class="k-avatar k-avatar-40"><img src="../../../assets/img/admin/user-f-1.jpg" alt="Avatar"
               class="k-avatar__img"></div>
         <div class="k-avatar k-avatar-45"><img src="../../../assets/img/admin/user-f-1.jpg" alt="Avatar"
               class="k-avatar__img"></div>
         <div class="k-avatar k-avatar-50"><img src="../../../assets/img/admin/user-f-1.jpg" alt="Avatar"
               class="k-avatar__img"></div>
         <div class="k-avatar-group">
            <div class="k-avatar k-avatar-30"><img src="../../../assets/img/admin/user-f-1.jpg" alt="Avatar"
                  class="k-avatar__img"></div>
            <div class="k-avatar k-avatar-30"><img src="../../../assets/img/admin/user-f-1.jpg" alt="Avatar"
                  class="k-avatar__img"></div>
            <div class="k-avatar k-avatar-30"><img src="../../../assets/img/admin/user-f-1.jpg" alt="Avatar"
                  class="k-avatar__img"></div>
            <div class="k-avatar k-avatar-30"><img src="../../../assets/img/admin/user-f-1.jpg" alt="Avatar"
                  class="k-avatar__img"></div>
         </div>
      </div>
      <!-- Cards -->
      <div class="k-card u-margin-y-20">
         <div class="k-card__header">
            <span class="k-card__title u-font-weight-bld">Active Project</span>
            <span><i class="ri-arrow-up-line"></i></span>
         </div>
         <div class="k-card__body">
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Esse fuga rem officiis!</p>
         </div>
      </div>
      <!-- form input -->
      <div class="k-card u-margin-y-20">
         <div class="k-card__header">
            <form action="#" class="k-form">
               <div class="k-form__group">
                  <input type="text" class="k-form__input" placeholder="Search">
                  <i class="ri-search-line k-form__icon u-font-size-14"></i>
               </div>
            </form>
         </div>
         <div class="k-card__body">
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Esse fuga rem officiis!</p>
         </div>
      </div>
      <!-- Table -->
      <div class="u-margin-y-20">
         <div class="u-overflow-auto">
            <table class="k-tbl">
               <thead class="k-tbl__header">
                  <tr class="k-tbl__row">
                     <th class="k-tbl__head">ID</th>
                     <th class="k-tbl__head">Name</th>
                     <th class="k-tbl__head">Email</th>
                     <th class="k-tbl__head">Project</th>
                     <th class="k-tbl__head">Salary</th>
                  </tr>
               </thead>
               <tbody class="k-tbl__body">
                  <tr class="k-tbl__row">
                     <td class="k-tbl__data">025</td>
                     <td class="k-tbl__data">Akono</td>
                     <td class="k-tbl__data">admin@kngell.com</td>
                     <td class="k-tbl__data">E-commerce</td>
                     <td class="k-tbl__data">$80000</td>
                  </tr>
                  <tr class="k-tbl__row">
                     <td class="k-tbl__data">025</td>
                     <td class="k-tbl__data">Akono</td>
                     <td class="k-tbl__data">admin@kngell.com</td>
                     <td class="k-tbl__data">E-commerce</td>
                     <td class="k-tbl__data">$80000</td>
                  </tr>
                  <tr class="k-tbl__row">
                     <td class="k-tbl__data">025</td>
                     <td class="k-tbl__data">Akono</td>
                     <td class="k-tbl__data">admin@kngell.com</td>
                     <td class="k-tbl__data">E-commerce</td>
                     <td class="k-tbl__data">$80000</td>
                  </tr>
               </tbody>
            </table>
         </div>
         <div class="k-card">
            <div class="k-card__header">
               <span class="k-card__title u-font-weight-bld">Active Project</span>
               <span><i class="ri-arrow-up-line"></i></span>
            </div>
            <div class="k-card__body">
               <div class="u-overflow-auto">
                  <table class="k-tbl">
                     <thead class="k-tbl__header">
                        <tr class="k-tbl__row">
                           <th class="k-tbl__head">ID</th>
                           <th class="k-tbl__head">Name</th>
                           <th class="k-tbl__head">Email</th>
                           <th class="k-tbl__head">Project</th>
                           <th class="k-tbl__head">Salary</th>
                        </tr>
                     </thead>
                     <tbody class="k-tbl__body">
                        <tr class="k-tbl__row">
                           <td class="k-tbl__data">025</td>
                           <td class="k-tbl__data">Akono</td>
                           <td class="k-tbl__data">admin@kngell.com</td>
                           <td class="k-tbl__data">E-commerce</td>
                           <td class="k-tbl__data">$80000</td>
                        </tr>
                        <tr class="k-tbl__row">
                           <td class="k-tbl__data">025</td>
                           <td class="k-tbl__data">Akono</td>
                           <td class="k-tbl__data">admin@kngell.com</td>
                           <td class="k-tbl__data">E-commerce</td>
                           <td class="k-tbl__data">$80000</td>
                        </tr>
                        <tr class="k-tbl__row">
                           <td class="k-tbl__data">025</td>
                           <td class="k-tbl__data">Akono</td>
                           <td class="k-tbl__data">admin@kngell.com</td>
                           <td class="k-tbl__data">E-commerce</td>
                           <td class="k-tbl__data">$80000</td>
                        </tr>
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
      <!-- Dropdown -->
      <div class="u-margin-y-20">
         <div class="k-dropdown k-dropdown-abs-right u-display-inline-block">
            <div class="k-avatar k-avatar-30" data-dropdown-target="notifications">
               <img src="../../../assets/img/admin/user-f-1.jpg" alt="Avatar" class="k-avatar__img">
            </div>
            <div class="k-dropdown__menu" id="notifications">
               <div class="u-font-size-16 u-font-weight-med u-padding-20">
                  Notifications
               </div>
            </div>
         </div>
         <div
            class="k-dropdown k-dropdown-abs-left k-dropdown-abs-center-md k-dropdown-abs-right-lg u-display-inline-block">
            <div class="k-avatar k-avatar-40" data-dropdown-target="message">
               <img src="../../../assets/img/admin/user-f-1.jpg" alt="Avatar" class="k-avatar__img">
            </div>
            <div class="k-dropdown__menu" id="message">
               <div class="u-font-size-16 u-font-weight-med u-padding-20">
                  Messages
               </div>
               <p class="u-padding-20">Lorem ipsum dolor sit, amet consectetur adipisicing elit.</p>
            </div>
         </div>
      </div>

   </div>

   </div>
   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js() ?>

<?php $this->end();