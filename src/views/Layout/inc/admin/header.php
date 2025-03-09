<header class="k-header k-header-fixed u-display-flex u-justify-content-between u-align-items-center">
   <div class="k-container">
      <div class="k-offset-12 k-offset-md-3 k-offset-lg-2">
         <nav class="k-header__nav u-display-flex u-justify-content-between u-align-items-center">
            <!-- Header left -->
            <div class="k-header__left u-display-flex u-align-items-center u-margin-r-10 u-margin-0-md">
               <div class="k-header__mobile u-margin-r-10 u-display-none-md">
                  <button type="button" class="u-icon-btn k-header__mobile--trigger" data-menu-target="main-menu">
                     <i class="ri-menu-2-line u-font-size-16"></i>
                  </button>
               </div>
               <div class="k-header__form">
                  <form action="#" class="k-form">
                     <div class="k-form__group">
                        <input type="text" class="k-form__input" placeholder="Search...">
                        <i class="ri-search-line k-form__icon"></i>
                     </div>
                  </form>
               </div>
            </div>
            <!-- end Header left -->
            <!-- Start Side bar -->
            <ul class="k-header__nav-items k-header__sidebar-md-3 k-header__sidebar-lg-2 u-padding-y-20" id="main-menu">
               <li class="k-header__list">
                  <a href="#"
                     class="k-header__link--logo k-active u-padding-20 u-display-flex u-align-items-center u-justify-content-between">
                     <img src="../../../../assets/img/admin/ease-logo.svg" alt="">
                  </a>
               </li>
               <!-- Dashboard -->
               <li class="k-header__list k-dropdown">
                  <a href=""
                     class="k-header__link u-padding-20 u-display-flex u-align-items-center u-justify-content-between"
                     data-dropdown-target="submenu">
                     <span class="u-font-size-14 u-display-flex u-align-items-center">
                        <i class="ri-dashboard-line u-margin-r-10"></i>
                        Dashboard
                     </span>
                     <i class="ri-arrow-down-s-line"></i>
                  </a>
                  <ul class="k-dropdown__menu" id="submenu">
                     <li><a href="/dashboard/index"
                           class="k-header__sublink u-display-block u-padding-x-20 u-padding-y-10 <?= $indexActive ?? '' ?>">
                           Projects
                        </a>
                     </li>
                     <li><a href="/dashboard/ecommerce"
                           class="k-header__sublink u-display-block u-padding-x-20 u-padding-y-10 <?= $ecommerceActive ?? '' ?>">
                           E-Commerce
                        </a>
                     </li>

                  </ul>
               </li>

               <li
                  class="k-header__list k-header__list--title u-font-weight-bld u-transform-uppercase u-padding-x-20 u-padding-y-10">
                  Applications
               </li>
               <!-- Message -->
               <li class="k-header__list">
                  <a href="#"
                     class="k-header__link u-padding-20 u-display-flex u-align-items-center u-justify-content-between">
                     <span class="u-font-size-14 u-display-flex u-align-items-center">
                        <i class="ri-message-2-line u-margin-r-10"></i>
                        Messages
                     </span>
                     <span class="k-badge k-badge-danger">12</span>
                  </a>
               </li>
               <!-- Inbox -->
               <li class="k-header__list">
                  <a href="#"
                     class="k-header__link u-padding-20 u-display-flex u-align-items-center u-justify-content-between">
                     <span class="u-font-size-14 u-display-flex u-align-items-center">
                        <i class="ri-inbox-line u-margin-r-10"></i>
                        Inbox
                     </span>
                     <span class="k-badge k-badge-danger">12</span>
                  </a>
               </li>
               <!-- Calendar -->
               <li class="k-header__list">
                  <a href="#"
                     class="k-header__link u-padding-20 u-display-flex u-align-items-center u-justify-content-between">
                     <span class="u-font-size-14 u-display-flex u-align-items-center"><i
                           class="ri-calendar-2-line u-margin-r-10"></i>Calendar</span>
                  </a>
               </li>
               <!-- Tasks -->
               <li class="k-header__list">
                  <a href="#"
                     class="k-header__link u-padding-20 u-display-flex u-align-items-center u-justify-content-between">
                     <span class="u-font-size-14 u-display-flex u-align-items-center"><i
                           class="ri-task-line u-margin-r-10"></i>Task</span>
                  </a>
               </li>
               <!-- Earnings -->
               <li class="k-header__list">
                  <a href=""
                     class="k-header__link u-padding-20 u-display-flex u-align-items-center u-justify-content-between">
                     <span class="u-font-size-14 u-display-flex u-align-items-center"><i
                           class="ri-bar-chart-line u-margin-r-10"></i>Reports
                        /
                        Earnings</span>
                  </a>
               </li>
               <!-- Kanban -->
               <li class="k-header__list">
                  <a href=""
                     class="k-header__link u-padding-20 u-display-flex u-align-items-center u-justify-content-between">
                     <span class="u-font-size-14 u-display-flex u-align-items-center"><i
                           class="ri-table-2 u-margin-r-10"></i>Kanban
                        board</span>
                  </a>
               </li>
               <li
                  class="-header__list k-header__list--title u-font-weight-bld u-transform-uppercase u-padding-x-20 u-padding-y-10">
                  Components
               </li>
               <!-- Teams -->
               <li class="k-header__list">
                  <a href="#"
                     class="k-header__link u-padding-20 u-display-flex u-align-items-center u-justify-content-between">
                     <span class="u-font-size-14 u-display-flex u-align-items-center"><i
                           class="ri-team-line u-margin-r-10"></i>Teams</span>
                  </a>
               </li>
               <!-- Client -->
               <li class="k-header__list">
                  <a href=""
                     class="k-header__link u-padding-20 u-display-flex u-align-items-center u-justify-content-between">
                     <span class="u-font-size-14 u-display-flex u-align-items-center"><i
                           class="ri-group-2-line u-margin-r-10"></i>Clients</span>
                  </a>
               </li>
               <!-- Pricing -->
               <li class="k-header__list">
                  <a href="#"
                     class="k-header__link u-padding-20 u-display-flex u-align-items-center u-justify-content-between">
                     <span class="u-font-size-14 u-display-flex u-align-items-center"><i
                           class="ri-price-tag-2-line u-margin-r-10"></i>Pricing
                        table</span>
                  </a>
               </li>
               <!-- Products -->
               <li class="k-header__list">
                  <a href=""
                     class="k-header__link u-padding-20 u-display-flex u-align-items-center u-justify-content-between">
                     <span class="u-font-size-14 u-display-flex u-align-items-center"><i
                           class="ri-shopping-cart-line u-margin-r-10"></i>Products</span>
                  </a>
               </li>
               <li
                  class="k-header__list k-header__list--title u-font-weight-bld u-transform-uppercase u-padding-x-20 u-padding-y-10">
                  System
               </li>
               <!-- Settings -->
               <li class="k-header__list">
                  <a href="#"
                     class="k-header__link u-padding-20 u-display-flex u-align-items-center u-justify-content-between">
                     <span class="u-font-size-14 u-display-flex u-align-items-center"><i
                           class="ri-equalizer-line u-margin-r-10"></i>Settings</span>
                  </a>
               </li>
               <!-- Profile -->
               <li class="k-header__list">
                  <a href=""
                     class="k-header__link u-padding-20 u-display-flex u-align-items-center u-justify-content-between">
                     <span class="u-font-size-14 u-display-flex u-align-items-center"><i
                           class="ri-user-star-line u-margin-r-10"></i>Profile</span>
                  </a>
               </li>
               <!-- Employee -->
               <li class="k-header__list k-header__list--employee-of-month">
                  <div class="k-card">
                     <img src="../../../../assets/img/admin/employee-month.jpg" alt="Employee of the month">
                     <div class="employee-details u-padding-20 u-text-align-center">
                        <h4>Employee of the month</h4>
                        <h2>Casey W.</h2>
                        <span class="u-font-size-12 u-transform-capitalize">Senior software engineer</span>
                     </div>
                  </div>
               </li>
               <!-- Logout -->
               <li class="k-header__list u-padding-x-20">
                  <a href="#" class="k-btn u-display-flex u-padding-y-20">
                     <span class="u-font-size-16 u-display-flex u-text-align-center"><i
                           class="ri-logout-box-r-line u-margin-r-10"></i>Log
                        out</span>
                  </a>
               </li>
            </ul>
            <!-- End sidebar -->
            <div class="k-header__right u-display-flex u-align-items-center">
               <div class="site-alert u-margin-r-10">
                  <button type="button" class="admin-modal-btn k-btn-icon" data-modal-target="subscription-reminder">
                  </button>
               </div>
               <div class="theme-control u-margin-r-10">
                  <button type="button" class="theme-switcher k-btn-icon" data-current-theme="light">
                     <i class="ri-moon-line theme-dark"></i>
                     <i class="ri-sun-line theme-light"></i>
                  </button>
               </div>
               <div class="k-dropdown k-dropdown-abs-right u-display-inline-flex u-margin-r-10">
                  <button type="button" class="k-btn k-btn-secondary k-btn-square" data-dropdown-target="notifications">
                     <i class="ri-notification-line u-font-size-16"></i>
                     <span class="k-badge k-badge-danger">4</span>
                  </button>
                  <div class="k-dropdown__menu" id="notifications">
                     <div class="dropdown-header u-padding-20">
                        <h6>Notifications</h6>
                     </div>
                     <ul class="dropdown-content">
                        <li class="dropdown-list">
                           <a href="#" class="dropdown-link u-display-block u-padding-x-20 u-padding-y-10">
                              <span class="notice-time u-font-size-12">
                                 2:47 PM
                              </span>
                              <span class="u-display-block notice-content u-font-size-14 u-font-weight-bld">New task
                                 has been assigned</span>
                           </a>
                        </li>
                        <li class="dropdown-list">
                           <a href="#" class="dropdown-link u-display-block u-padding-x-20 u-padding-y-10">
                              <span class="notice-time u-font-size-12">
                                 2:47 PM
                              </span>
                              <span class="u-display-block notice-content u-font-size-14 u-font-weight-bld">New task
                                 has been assigned</span>
                           </a>
                        </li>
                        <li class="dropdown-list">
                           <a href="#" class="dropdown-link u-display-block u-padding-x-20 u-padding-y-10">
                              <span class="notice-time u-font-size-12">
                                 2:47 PM
                              </span>
                              <span class="u-display-block notice-content u-font-size-14 u-font-weight-bld">New task
                                 has been assigned</span>
                           </a>
                        </li>
                        <li class="dropdown-list">
                           <a href="#" class="dropdown-link u-display-block u-padding-x-20 u-padding-y-10">
                              <span class="notice-time u-font-size-12">
                                 2:47 PM
                              </span>
                              <span class="u-display-block notice-content u-font-size-14 u-font-weight-bld">New task
                                 has been assigned</span>
                           </a>
                        </li>
                     </ul>
                     <div class="dropdown-footer u-padding-20">
                        <button type="button" class="k-btn k-btn-secondary k-btn-link">
                           Mark all as read
                        </button>
                     </div>
                  </div>
                  <!-- ends dropdown menu -->
               </div>
               <!-- end dropdown -->
               <div class="k-dropdown k-dropdown-abs-right u-display-inline-flex u-align-items-center">
                  <button type="button" class="k-avatar k-avatar-35" data-dropdown-target="profile">
                     <img src="../../../../assets/img/admin/user-f-1.jpg" alt="Profile" class="k-avatar__img">
                  </button>
                  <div class="k-dropdown__menu" id="profile">
                     <div class="dropdown-header u-padding-20">
                        <div class="u-display-flex u-align-items-center">

                           <div class="user-img u-margin-r-10">
                              <div class="k-avatar k-avatar-40">
                                 <img src="../../../../assets/img/admin/user-f-1.jpg" alt="User" class="k-avatar__img">
                              </div>
                           </div>

                           <div class="user-details">
                              <span class="u-font-weight-bld u-font-size-14 u-display-block">
                                 Jane Mathieu
                              </span>
                              <span class="u-font-size-12">
                                 Project Manager
                              </span>
                           </div>
                           <!-- !header details-->
                        </div>
                     </div>
                     <!-- !dropdown header -->
                     <ul class="dropdown-content">
                        <li class="dropdown-list">
                           <a href="#"
                              class="dropdown-link u-u-display-flex u-align-items-center u-padding-x-20 u-padding-y-10">
                              <i class="ri-user-star-line u-font-size-16 u-margin-r-10"></i>
                              <span class="u-font-size-14 u-font-weight-bld">Profile</span>
                           </a>
                        </li>
                        <li class="dropdown-list">
                           <a href="#"
                              class="dropdown-link u-u-display-flex u-align-items-center u-padding-x-20 u-padding-y-10">
                              <i class="ri-equalizer-line u-font-size-16 u-margin-r-10"></i>
                              <span class="u-font-size-14 u-font-weight-bld">Settings</span>
                           </a>
                        </li>
                        <li class="dropdown-list">
                           <a href="#"
                              class="dropdown-link u-u-display-flex u-align-items-center u-padding-x-20 u-padding-y-10">
                              <i class="ri-task-line u-font-size-16 u-margin-r-10"></i>
                              <span class="u-font-size-14 u-font-weight-bld">Tasks</span>
                           </a>
                        </li>

                     </ul>
                     <!-- !dropdown content -->
                     <div class="dropdown-footer">
                        <a href="#"
                           class="dropdown-link u-u-display-flex u-align-items-center u-padding-x-20 u-padding-y-10">
                           <i class="ri-logout-box-r-line u-font-size-18 u-margin-r-10"></i>
                           <span class="u-font-size-16 u-font-weight-bld">Logout</span>
                        </a>

                     </div>
                     <!-- !dropdown footer -->
                  </div>
                  <!-- ends dropdown menu -->
               </div>
            </div>
            <!-- end header right -->
         </nav>
      </div>
   </div>
</header>