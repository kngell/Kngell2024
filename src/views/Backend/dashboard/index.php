<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>



<main class="main" id="main">
   <!-- Content -->
   <div id="app">
      <div class="main-wrapper">
         <div class="main-sidebar">
            <aside id="sidebar-wrapper">
               <div class="sidebar-brand">
                  <a href="index.html">Admin Panel</a>
               </div>
               <div class="sidebar-brand sidebar-brand-sm">
                  <a href="index.html"></a>
               </div>

               <ul class="sidebar-menu">

                  <li class="active"><a class="nav-link" href="index.html"><i class="fa-solid fa-hand-point-right"></i>
                        <span>Dashboard</span></a></li>

                  <li class="nav-item dropdown active">
                     <a href="#" class="nav-link has-dropdown"><i
                           class="fa-solid fa-hand-point-right"></i><span>Dropdown
                           Items</span></a>
                     <ul class="dropdown-menu">
                        <li class="active"><a class="nav-link" href=""><i class="fa-solid fa-angle-right"></i> Item
                              1</a>
                        </li>
                        <li class=""><a class="nav-link" href=""><i class="fa-solid fa-angle-right"></i> Item 2</a></li>
                     </ul>
                  </li>

                  <li class=""><a class="nav-link" href="setting.html"><i class="fa-solid fa-hand-point-right"></i>
                        <span>Setting</span></a></li>

                  <li class=""><a class="nav-link" href="form.html"><i class="fa-solid fa-hand-point-right"></i>
                        <span>Form</span></a></li>

                  <li class=""><a class="nav-link" href="table.html"><i class="fa-solid fa-hand-point-right"></i>
                        <span>Table</span></a></li>

                  <li class=""><a class="nav-link" href="invoice.html"><i class="fa-solid fa-hand-point-right"></i>
                        <span>Invoice</span></a></li>

               </ul>
            </aside>
         </div>

         <div class="main-content">
            <section class="section">
               <div class="section-header">
                  <h1>Dashboard</h1>
               </div>
               <div class="row">
                  <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                     <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                           <i class="far fa-user"></i>
                        </div>
                        <div class="card-wrap">
                           <div class="card-header">
                              <h4>Total News Categories</h4>
                           </div>
                           <div class="card-body">
                              12
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                     <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                           <i class="fa-solid fa-book-open"></i>
                        </div>
                        <div class="card-wrap">
                           <div class="card-header">
                              <h4>Total News</h4>
                           </div>
                           <div class="card-body">
                              122
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                     <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                           <i class="fa-solidfa-bullhorn"></i>
                        </div>
                        <div class="card-wrap">
                           <div class="card-header">
                              <h4>Total Users</h4>
                           </div>
                           <div class="card-body">
                              45
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </section>
         </div>

         <!-- Fin Content -->
      </div>
   </div>
</main>

<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();