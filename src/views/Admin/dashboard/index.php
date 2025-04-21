<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class="admin-dashboard">
      <div class="k-container">
         <div class="k-offset-12 k-offset-md-3 k-offset-lg-2">
            <div class="admin-section-heading u-padding-y-20">
               <h4 class="admin-title">Good morning, Jane</h4>
               <p class="admin-sub-title">
                  Your dashboard and components are refreshed
               </p>
            </div>
            <section class="admin-overview u-padding-y-20">
               <div class="k-row">
                  <div class="k-col-sm-6 k-col-lg-3 u-margin-b-20 u-margin-b-0-lg">
                     <div class="k-card">
                        <div class="k-card__header">
                           <span class="k-card__title u-font-weight-bld">Active Project</span>
                           <span class="k-btn-icon k-btn-icon-static"><i class="ri-bar-chart-line"></i></span>
                        </div>

                        <div class="k-card__body u-display-flex u-justify-content-between u-align-items-end">

                           <div class="left-side">
                              <h2 class="u-margin-b-10">125</h2>
                              <p class="u-font-size-12 u-display-flex u-align-items-center">
                                 <span
                                    class="admin-indicator-up u-margin-r-10 u-display-inline-flex u-align-items-center"><i
                                       class="ri-arrow-up-line"></i>12.5%
                                 </span>
                                 <span class="admin-faded-text">From last week</span>
                              </p>
                           </div>
                           <div class="right-side">
                              <div class="k-badge k-badge-danger admin-bdg">
                                 <i class="ri-folders-line"></i>
                              </div>
                           </div>

                        </div>
                     </div>
                  </div>
                  <div class="k-col-sm-6 k-col-lg-3 u-margin-b-20 u-margin-b-0-lg">
                     <div class="k-card">
                        <div class="k-card__header">
                           <span class="k-card__title u-font-weight-bld">Avg projects Earnings</span>
                           <span class="k-btn-icon k-btn-icon-static"><i class="ri-bar-chart-line"></i></span>
                        </div>

                        <div class="k-card__body u-display-flex u-justify-content-between u-align-items-end">
                           <div class="left-side">
                              <h2 class="u-margin-b-10">$5,436.07</h2>
                              <p class="u-font-size-12 u-display-flex u-align-items-center">
                                 <span
                                    class="admin-indicator-up u-margin-r-10 u-display-inline-flex u-align-items-center"><i
                                       class="ri-arrow-up-line"></i>15.7%
                                 </span>
                                 <span class="admin-faded-text">From last week</span>
                              </p>
                           </div>
                           <div class="right-side">
                              <div class="k-badge k-badge-secondary admin-bdg">
                                 <i class="ri-money-dollar-circle-line"></i>
                              </div>
                           </div>

                        </div>
                     </div>
                  </div>
                  <div class="k-col-sm-6 k-col-lg-3 u-margin-b-20 u-margin-b-0-sm">
                     <div class="k-card">
                        <div class="k-card__header">
                           <span class="k-card__title u-font-weight-bld">Revenue</span>
                           <span class="k-btn-icon k-btn-icon-static"><i class="ri-bar-chart-line"></i></span>
                        </div>

                        <div class="k-card__body u-display-flex u-justify-content-between u-align-items-end">
                           <div class="left-side">
                              <h2 class="u-margin-b-10">$62,789.06</h2>
                              <p class="u-font-size-12 u-display-flex u-align-items-center">
                                 <span
                                    class="admin-indicator-down u-margin-r-10 u-display-inline-flex u-align-items-center"><i
                                       class="ri-arrow-down-line"></i>4%
                                 </span>
                                 <span class="admin-faded-text">From last month</span>
                              </p>
                           </div>
                           <div class="right-side">
                              <div class="k-badge k-badge-success admin-bdg">
                                 <i class="ri-line-chart-line"></i>
                              </div>
                           </div>

                        </div>
                     </div>
                  </div>
                  <div class="k-col-sm-6 k-col-lg-3">
                     <div class="k-card">
                        <div class="k-card__header">
                           <span class="k-card__title u-font-weight-bld">Miscellaneous</span>
                           <span class="k-btn-icon k-btn-icon-static"><i class="ri-bar-chart-line"></i></span>
                        </div>

                        <div class="k-card__body u-display-flex u-justify-content-between">

                           <div class="admin-box u-text-align-center">
                              <h2 class="u-margin-b-10">12</h2>
                              <p class="u-font-size-12 admin-faded-text">Messages</p>
                           </div>

                           <div class="admin-box u-text-align-center">
                              <h2 class="u-margin-b-10">6</h2>
                              <p class="u-font-size-12 admin-faded-text">Inbox</p>
                           </div>

                           <div class="admin-box u-text-align-center">
                              <h2 class="u-margin-b-10">4</h2>
                              <p class="u-font-size-12 admin-faded-text">Notifications</p>
                           </div>

                           <div class="admin-box u-text-align-center">
                              <h2 class="u-margin-b-10">1</h2>
                              <p class="u-font-size-12 admin-faded-text">Alerts</p>
                           </div>

                        </div>
                     </div>
                  </div>
               </div>
            </section>
            <section class="admin-project-updates u-padding-y-20">
               <div class="k-row">
                  <div class="k-col-md-7 u-margin-b-20 u-margin-b-0-md">

                     <div class="k-card">
                        <div class="k-card__header">
                           <span class="k-card__title u-font-weight-bld">Projects updates</span>
                           <div class="u-display-flex u-align-items-center">
                              <button type="buttton" class="k-btn k-btn-sm k-btn-primary u-margin-r-10">
                                 <i class="ri-arrow-up-line u-margin-r-10"></i>
                                 Export
                              </button>
                              <button type="buttton" class="k-btn k-btn-secondary k-btn-square">
                                 <i class="ri-add-line"></i>
                              </button>
                           </div>
                        </div>
                        <div class="k-card__body u-padding-0">
                           <div class="u-overflow-auto">
                              <table class="k-table">
                                 <thead class="k-tbl__header">
                                    <tr class="k-tbl__row">
                                       <th class="k-tbl__head">Name</th>
                                       <th class="k-tbl__head">Teams</th>
                                       <th class="k-tbl__head">Progress</th>
                                       <th class="k-tbl__head">Status</th>
                                       <th class="k-tbl__head">Budget</th>
                                       <th class="k-tbl__head">Deadline</th>
                                    </tr>
                                 </thead>
                                 <tboby class="k-tbl__body">
                                    <tr class="k-tbl__row">
                                       <td class="k-tbl__data">FitPro website analysis</td>
                                       <td class="k-tbl__data">
                                          <div class="k-avatar-group">
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-m-1.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-m-2.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-f-3.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-f-2.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-f-1.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                          </div>
                                       </td>
                                       <td class="k-tbl__data">
                                          <div class="admin-progress" data-current-progress="35">
                                             <span class="admin-progress__inner">

                                             </span>
                                          </div>
                                       </td>
                                       <td class="k-tbl__data">
                                          <div class="k-badge k-badge-secondary">
                                             In Progress
                                          </div>
                                       </td>
                                       <td class="k-tbl__data">
                                          7,345.98
                                       </td>
                                       <td class="k-tbl__data">
                                          July 16, 2023
                                       </td>
                                    </tr>

                                    <tr class="k-tbl__row">
                                       <td class="k-tbl__data">Gmail re-branding</td>
                                       <td class="k-tbl__data">
                                          <div class="k-avatar-group">
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-m-1.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-m-2.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-f-3.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-f-2.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                          </div>
                                       </td>
                                       <td class="k-tbl__data">
                                          <div class="admin-progress" data-current-progress="0">
                                             <span class="admin-progress__inner">

                                             </span>
                                          </div>
                                       </td>
                                       <td class="k-tbl__data">
                                          <div class="k-badge k-badge-danger">
                                             Canceled
                                          </div>
                                       </td>
                                       <td class="k-tbl__data">
                                          7,345.98
                                       </td>
                                       <td class="k-tbl__data">
                                          July 16, 2023
                                       </td>
                                    </tr>

                                    <tr class="k-tbl__row">
                                       <td class="k-tbl__data">Wordpress deployment</td>
                                       <td class="k-tbl__data">
                                          <div class="k-avatar-group">
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-m-1.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-m-2.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                          </div>
                                       </td>
                                       <td class="k-tbl__data">
                                          <div class="admin-progress" data-current-progress="100">
                                             <span class="admin-progress__inner">
                                             </span>
                                          </div>
                                       </td>
                                       <td class="k-tbl__data">
                                          <div class="k-badge k-badge-success">
                                             Completed
                                          </div>
                                       </td>
                                       <td class="k-tbl__data">
                                          7,345.98
                                       </td>
                                       <td class="k-tbl__data">
                                          July 16, 2023
                                       </td>
                                    </tr>
                                    <tr class="k-tbl__row">
                                       <td class="k-tbl__data">Website design for EatPro</td>
                                       <td class="k-tbl__data">
                                          <div class="k-avatar-group">
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-m-1.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-m-2.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-f-3.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-f-2.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-f-1.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                          </div>
                                       </td>
                                       <td class="k-tbl__data">
                                          <div class="admin-progress" data-current-progress="35">
                                             <span class="admin-progress__inner">

                                             </span>
                                          </div>
                                       </td>
                                       <td class="k-tbl__data">
                                          <div class="k-badge k-badge-secondary">
                                             In Progress
                                          </div>
                                       </td>
                                       <td class="k-tbl__data">
                                          7,345.98
                                       </td>
                                       <td class="k-tbl__data">
                                          July 16, 2023
                                       </td>
                                    </tr>

                                    <tr class="k-tbl__row">
                                       <td class="k-tbl__data">Wireframe for Amazon App</td>
                                       <td class="k-tbl__data">
                                          <div class="k-avatar-group">
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-m-1.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-m-2.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-f-3.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-f-2.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                             <div class="k-avatar k-avatar-30">
                                                <img src="../../../assets/img/admin/user-f-1.jpg" alt="User"
                                                   class="k-avatar__img">
                                             </div>
                                          </div>
                                       </td>
                                       <td class="k-tbl__data">
                                          <div class="admin-progress" data-current-progress="82">
                                             <span class="admin-progress__inner">

                                             </span>
                                          </div>
                                       </td>
                                       <td class="k-tbl__data">
                                          <div class="k-badge k-badge-secondary">
                                             In Progress
                                          </div>
                                       </td>
                                       <td class="k-tbl__data">
                                          7,345.98
                                       </td>
                                       <td class="k-tbl__data">
                                          July 16, 2023
                                       </td>
                                    </tr>
                                 </tboby>
                              </table>
                           </div>

                        </div>
                     </div>

                  </div>


                  <div class="k-col-md-5">

                     <div class="k-card">
                        <div class="k-card__header">
                           <span class="k-card__title u-font-weight-bld">Featured Projects</span>
                           <a href="#" class="k-btn k-btn-square k-btn-secondary">
                              <i class="ri-arrow-right-up-line"></i>
                           </a>
                        </div>

                        <div class="k-card__body u-display-flex u-align-items-center">

                           <div class="k-row u-align-items-center">
                              <div class="k-col-sm-7 k-col-md-12 k-col-lg-7 u-margin-b-12 u-margin-b-0-lg">

                                 <div class="admin-section-heading u-padding-y-20">
                                    <h4 class="admin-title">FitPro website</h4>
                                    <p class="u-font-size-12 admin-sub-title">
                                       Website analysis
                                    </p>
                                 </div>
                                 <p class="u-margin-b-20">Lorem ipsum dolor sit amet consectetur adipisicing elit.
                                    Dolores suscipit magnam quam dicta debitis quo explicabo ipsum laborum commodi
                                    voluptate accusantium culpa quis, illo doloribus praesentium quos reprehenderit
                                    repellendus voluptatibus repudiandae optio? Quo, impedit accusantium.</p>
                                 <div class="k-avatar-group">
                                    <div class="k-avatar k-avatar-30">
                                       <img src="../../../assets/img/admin/user-m-1.jpg" alt="User"
                                          class="k-avatar__img">
                                    </div>
                                    <div class="k-avatar k-avatar-30">
                                       <img src="../../../assets/img/admin/user-m-2.jpg" alt="User"
                                          class="k-avatar__img">
                                    </div>
                                    <div class="k-avatar k-avatar-30">
                                       <img src="../../../assets/img/admin/user-f-3.jpg" alt="User"
                                          class="k-avatar__img">
                                    </div>
                                    <div class="k-avatar k-avatar-30">
                                       <img src="../../../assets/img/admin/user-f-2.jpg" alt="User"
                                          class="k-avatar__img">
                                    </div>
                                    <div class="k-avatar k-avatar-30">
                                       <img src="../../../assets/img/admin/user-f-1.jpg" alt="User"
                                          class="k-avatar__img">
                                    </div>
                                 </div>

                              </div>

                              <div class="k-col-sm-5 k-col-md-12 k-col-lg-5">
                                 <div class="k-card admin-featured-project u-overflow-hidden">
                                    <img src="../../../assets/img/admin/fitness-lg.jpg" alt="Fitness">
                                    <div class="decoration"><img src="../../../assets/img/admin/decoration.svg"
                                          alt="Decoration"></div>
                                 </div>
                              </div>
                           </div>

                        </div>

                     </div>

                  </div>


               </div>


            </section>
            <section class="admin-project-overview u-padding-y-20">
               <div class="k-row">
                  <div class="k-col-lg-6 u-margin-b-20 u-margin-b-0-lg">
                     <div class="k-card">
                        <div class="k-card__header">
                           <span class="k-car__title u-font-weight-bld">Project Overview</span>
                           <div class="u-display-inline-block k-dropdown k-dropdown-abs-right">
                              <button type="button" class="k-btn-icon" data-dropdown-target="project-overview">
                                 <i class="ri-more-2-line"></i>
                              </button>
                              <div class="k-dropdown__menu" id="project-overview">
                                 <ul class="dropdown__content">
                                    <li class="dropdown__list">
                                       <a href="#" class="dropdown__link u-display-block u-padding-10">
                                          Completed
                                       </a>
                                    </li>
                                    <li class="dropdown__list">
                                       <a href="#" class="dropdown__link u-display-block u-padding-10">
                                          In Progress
                                       </a>
                                    </li>
                                    <li class="dropdown__list">
                                       <a href="#" class="dropdown__link u-display-block u-padding-10">
                                          Delayed / Canceled
                                       </a>
                                    </li>
                                 </ul>
                              </div>
                           </div>
                        </div>
                        <div class="k-card__body u-padding-0 u-padding-x-20">
                           <div class="k-tbl__header u-padding-20 u-text-align-center u-overflow-hidden">

                              <div class="k-row">
                                 <div class="k-col-6 k-col-sm-3 u-margin-b-8 u-margin-b-0-sm">
                                    <div class="k-h2">576</div>
                                    <span class="u-font-size-12">Total Project</span>
                                 </div>

                                 <div class="k-col-6 k-col-sm-3 u-margin-b-8 u-margin-b-0-sm">
                                    <div class="k-h2">243</div>
                                    <span class="u-font-size-12 k-badge k-badge-success">Completed</span>
                                 </div>

                                 <div class="k-col-6 k-col-sm-3">
                                    <div class="k-h2">102</div>
                                    <span class="u-font-size-12 k-badge k-badge-secondary">In Progress</span>
                                 </div>

                                 <div class="k-col-6 k-col-sm-3">
                                    <div class="k-h2">231</div>
                                    <span class="u-font-size-12 k-badge k-badge-danger">Delayed/Canceled</span>
                                 </div>
                              </div>
                           </div>

                           <div class="admin-chart u-padding-20">
                              <canvas id="admin-project"></canvas>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="k-col-lg-6">
                     <div class="k-card">
                        <div class="k-card__header">
                           <span class="k-car__title u-font-weight-bld">Project's earnings</span>
                        </div>
                        <div class="k-card__body u-display-flex u-fdirection-column u-align-items-center">
                           <div class="k-row u-margin-b-20">
                              <div class="k-col-6 k-col-sm-3 u-margin-b-8 u-margin-b-0-sm">
                                 <div class="k-h2">$13,243.75</div>
                                 <span class="u-font-size-12 k-badge k-badge-secondary u-transform-capitalize">web /
                                    mobile applications</span>
                              </div>

                              <div class="k-col-6 k-col-sm-3 u-margin-b-8 u-margin-b-0-sm">
                                 <div class="k-h2">$6,234.75</div>
                                 <span class="u-font-size-12 k-badge k-badge-danger u-transform-capitalize">branding /
                                    marketing</span>
                              </div>

                              <div class="k-col-6 k-col-sm-3">
                                 <div class="k-h2">$10,423.75</div>
                                 <span class="u-font-size-12 k-badge k-badge-success u-transform-capitalize">financial
                                    analysis / web / mobile</span>
                              </div>

                              <div class="k-col-6 k-col-sm-3">
                                 <div class="k-h2">$11,423.75</div>
                                 <span
                                    class="u-font-size-12 k-badge k-badge-warn u-transform-capitalize">consultation</span>
                              </div>
                           </div>
                           <div
                              class="admin-earning-chart u-display-flex u-justify-content-center u-align-items-center">
                              <canvas id="admin-earnings"></canvas>
                           </div>
                        </div>
                     </div>

                  </div>

               </div>

            </section>

            <section class="admin-client-overview u-padding-y-20">
               <div class="k-card admin-client">
                  <div class="k-card__header">
                     <span class="k-card__title u-font-weight-bld">Client Overview</span>
                  </div>
                  <div class="k-card__body u-padding-0">
                     <div class="u-overflow-auto">
                        <table class="k-tbl">
                           <thead class="k-tbl__header">
                              <tr class="k-tbl__row">
                                 <th class="k-tbl__head">Name</th>
                                 <th class="k-tbl__head">Project</th>
                                 <th class="k-tbl__head">Joined</th>
                                 <th class="k-tbl__head">Spend</th>
                                 <th class="k-tbl__head">Level</th>
                              </tr>
                           </thead>
                           <tbody class="k-tbl__body">
                              <tr class="k-tbl__row">
                                 <td class="k-tbl__data">
                                    <div class="u-display-flex u-text-align-center">
                                       <div class="u-margin-r-10">
                                          <div class="k-avatar k-avatar-30">
                                             <img src="../../../assets/img/admin/fitness-sm.jpg" alt="User"
                                                class="k-avatar__img">
                                          </div>
                                       </div>
                                       <div class="user-details">
                                          <span class="k-h5 u-display-block">
                                             FitPro
                                          </span>
                                          <span class="u-font-size-12">
                                             John D.
                                          </span>
                                       </div>
                                    </div>
                                 </td>
                                 <td class="k-tbl__data">
                                    FitPro wesite analysis
                                 </td>
                                 <td class="k-tbl__data">July 16, 2023</td>
                                 <td class="k-tbl__data">$27,345.98</td>
                                 <td class="k-tbl__data"><span class="admin-level-gold u-font-weight-med">Gold</span>
                                 </td>
                              </tr>
                              <tr class="k-tbl__row">
                                 <td class="k-tbl__data">
                                    <div class="u-display-flex u-text-align-center">
                                       <div class="u-margin-r-10">
                                          <div class="k-avatar k-avatar-30">
                                             <img src="../../../assets/img/admin/fruit-2.jpg" alt="User"
                                                class="k-avatar__img">
                                          </div>
                                       </div>
                                       <div class="user-details">
                                          <span class="k-h5 u-display-block">
                                             EatPro
                                          </span>
                                          <span class="u-font-size-12">
                                             Jackson D.
                                          </span>
                                       </div>
                                    </div>
                                 </td>
                                 <td class="k-tbl__data">
                                    Website design for EatPro
                                 </td>
                                 <td class="k-tbl__data">July 16, 2023</td>
                                 <td class="k-tbl__data">$17,345.98</td>
                                 <td class="k-tbl__data"><span
                                       class="admin-level-silver u-font-weight-med">Silver</span>
                                 </td>
                              </tr>
                              <tr class="k-tbl__row">
                                 <td class="k-tbl__data">
                                    <div class="u-display-flex u-text-align-center">
                                       <div class="u-margin-r-10">
                                          <div class="k-avatar k-avatar-30">
                                             <img src="../../../assets/img/admin/shoe-1.jpg" alt="User"
                                                class="k-avatar__img">
                                          </div>
                                       </div>
                                       <div class="user-details">
                                          <span class="k-h5 u-display-block">
                                             Nike
                                          </span>
                                          <span class="u-font-size-12">
                                             Rilley R.
                                          </span>
                                       </div>
                                    </div>
                                 </td>
                                 <td class="k-tbl__data">
                                    Marketing &amp; branding
                                 </td>
                                 <td class="k-tbl__data">July 16, 2023</td>
                                 <td class="k-tbl__data">$27,345.98</td>
                                 <td class="k-tbl__data"><span class="admin-level-gold u-font-weight-med">Gold</span>
                                 </td>
                              </tr>
                              <tr class="k-tbl__row">
                                 <td class="k-tbl__data">
                                    <div class="u-display-flex u-text-align-center">
                                       <div class="u-margin-r-10">
                                          <div class="k-avatar k-avatar-30">
                                             <img src="../../../assets/img/admin/fruit-1.jpg" alt="User"
                                                class="k-avatar__img">
                                          </div>
                                       </div>
                                       <div class="user-details">
                                          <span class="k-h5 u-display-block">
                                             Instacard
                                          </span>
                                          <span class="u-font-size-12">
                                             Williams W.
                                          </span>
                                       </div>
                                    </div>
                                 </td>
                                 <td class="k-tbl__data">
                                    marketing &amp; branding
                                 </td>
                                 <td class="k-tbl__data">July 16, 2023</td>
                                 <td class="k-tbl__data">$22,345.98</td>
                                 <td class="k-tbl__data"><span class="admin-level-gold u-font-weight-med">Gold</span>
                                 </td>
                              </tr>
                              <tr class="k-tbl__row">
                                 <td class="k-tbl__data">
                                    <div class="u-display-flex u-text-align-center">
                                       <div class="u-margin-r-10">
                                          <div class="k-avatar k-avatar-30">
                                             <img src="../../../assets/img/admin/bag.jpg" alt="User"
                                                class="k-avatar__img">
                                          </div>
                                       </div>
                                       <div class="user-details">
                                          <span class="k-h5 u-display-block">
                                             Amazon
                                          </span>
                                          <span class="u-font-size-12">
                                             Jeff B.
                                          </span>
                                       </div>
                                    </div>
                                 </td>
                                 <td class="k-tbl__data">
                                    Wireframe for Amazon App
                                 </td>
                                 <td class="k-tbl__data">July 16, 2023</td>
                                 <td class="k-tbl__data">$12,345.98</td>
                                 <td class="k-tbl__data"><span
                                       class="admin-level-silver u-font-weight-med">Silver</span>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </section>
            <section class="admin-communication-overview u-padding-y-20">

               <div class="k-row">

                  <div class="k-col-md-6 u-margin-b-20 u-margin-b-0-md">

                     <div class="k-card admin-communication">

                        <div class="k-card__header">
                           <span class="k-card__title u-font-weight-bld">Message updates</span>

                           <div class="u-display-flex u-text-align-center">
                              <span class="u-font-size-12 admin-faded-text">Refreshed 1h ago</span>
                              <div class="u-margin-l-10">
                                 <button type="button" class="k-btn-icon"><i class="ri-refresh-line"></i></button>
                              </div>
                           </div>
                        </div>

                        <div class="k-card__body u-padding-0">

                           <div class="admin-message">

                              <div
                                 class="k-tbl__header u-display-flex u-justify-content-between u-align-items-center u-padding-x-20 u-padding-y-16">
                                 <span>Messages</span>
                                 <a href="#" class="k-btn k-btn-secondary k-btn-link u-transform-none">View all</a>
                              </div>

                              <div class="k-tbl__row">

                                 <div class="k-tbl__data u-padding-x-20">

                                    <div class="admin-communication-content">

                                       <div class="u-display-flex">
                                          <div class="u-margin-r-10">
                                             <div class="k-avatar k-avatar-30"><img
                                                   src="../../../assets/img/admin/user-m-1.jpg" alt=""
                                                   class="k-avatar__img"></div>
                                          </div>

                                          <div class="admin-communication-details">
                                             <span class="k-h5 u-display-block">Johnathan R.</span>
                                             <p class="u-font-size-12 admin-content">Lorem ipsum, dolor sit amet
                                                consectetur
                                                adipisicing
                                                elit. Eos voluptate odio saepe voluptatum magnam voluptas!</p>
                                          </div>

                                       </div>

                                       <span class="admin-meta u-font-size-12 admin-faded-text">02/02/23 || 09:45
                                          AM</span>

                                    </div>

                                 </div>

                              </div>

                              <div class="k-tbl__row">

                                 <div class="k-tbl__data u-padding-x-20">

                                    <div class="admin-communication-content">

                                       <div class="u-display-flex">
                                          <div class="u-margin-r-10">
                                             <div class="k-avatar k-avatar-30"><img
                                                   src="../../../assets/img/admin/user-f-2.jpg" alt=""
                                                   class="k-avatar__img"></div>
                                          </div>

                                          <div class="admin-communication-details">
                                             <span class="k-h5 u-display-block">Samantha R.</span>
                                             <p class="u-font-size-12 admin-content">Lorem ipsum, dolor sit amet
                                                consectetur
                                                adipisicing
                                                elit. Eos voluptate odio saepe voluptatum magnam voluptas!</p>
                                          </div>

                                       </div>

                                       <span class="admin-meta u-font-size-12 admin-faded-text">02/01/23 || 07:45
                                          AM</span>

                                    </div>

                                 </div>

                              </div>

                           </div>

                        </div>

                     </div>

                  </div>

                  <div class="k-col-md-6">

                     <div class="k-card admin-communication">

                        <div class="k-card__header">
                           <span class="k-card__title u-font-weight-bld">Inbox updates</span>

                           <div class="u-display-flex u-align-items-center">
                              <span class="u-font-size-12 admin-faded-text">Refreshed 1h ago</span>
                              <div class="u-margin-l-10">
                                 <button type="button" class="k-btn-icon"><i class="ri-refresh-line"></i></button>
                              </div>
                           </div>
                        </div>

                        <div class="k-card__body u-padding-0">

                           <div class="admin-inbox">

                              <div
                                 class="k-tbl__header u-display-flex u-justify-content-between u-align-items-center u-padding-x-20 u-padding-y-16">
                                 <span>Inbox</span>
                                 <a href="#" class="k-btn k-btn-secondary k-btn-link u-transform-none">View all</a>
                              </div>

                              <div class="k-tbl__row">

                                 <div class="k-tbl__data u-padding-x-20">

                                    <div class="admin-communication-content">

                                       <div class="u-display-flex-lg">

                                          <div class="left-side u-display-flex u-margin-r-10-lg">

                                             <div class="u-margin-r-10">
                                                <div class="k-avatar k-avatar-30"><img
                                                      src="../../../assets/img/admin/user-m-1.jpg" alt=""
                                                      class="k-avatar__img"></div>
                                             </div>

                                             <div class="admin-communication-details">
                                                <span class="k-h5 u-display-block">Jonnathan R.</span>
                                                <p class="u-font-size-12">jonnathan.r@example.com</p>
                                             </div>

                                          </div>

                                          <div class="right-side">
                                             <div class="admin-communication-details">
                                                <span class="k-h5 u-display-block">Re: Wireframe</span>
                                                <p class="p-font-size-12">Lorem ipsum dolor sit amet consectetur,
                                                   adipisicing
                                                   elit.
                                                   Voluptatum velit rem tempora?</p>
                                             </div>
                                          </div>

                                       </div>

                                       <span class="admin-meta u-font-size-12 admin-faded-text">02/02/23 || 09:45
                                          AM</span>

                                    </div>

                                 </div>

                              </div>

                              <div class="k-tbl__row">

                                 <div class="k-tbl__data u-padding-x-20">

                                    <div class="admin-communication-content">

                                       <div class="u-display-flex-lg">

                                          <div class="left-side u-display-flex u-marin-r-10-lg">

                                             <div class="u-margin-r-10">
                                                <div class="k-avatar k-avatar-30"><img
                                                      src="../../../assets/img/admin/user-f-2.jpg" alt=""
                                                      class="k-avatar__img"></div>
                                             </div>

                                             <div class="admin-communication-details">
                                                <span class="k-h5 u-display-block">Samantha R.</span>
                                                <p class="u-font-size-12">samantha.r@example.com</p>
                                             </div>

                                          </div>

                                          <div class="right-side">
                                             <div class="admin-communication-details">
                                                <span class="k-h5 u-display-block">Re: Today's meet</span>
                                                <p class="u-font-size-12">Lorem ipsum dolor sit amet consectetur,
                                                   adipisicing
                                                   elit.
                                                   Voluptatum velit rem tempora?</p>
                                             </div>
                                          </div>

                                       </div>

                                       <span class="admin-meta u-font-size-12 admin-faded-text">02/01/23 || 07:45
                                          AM</span>

                                    </div>

                                 </div>

                              </div>

                           </div>

                        </div>

                     </div>

                  </div>

               </div>

            </section><!-- .ease-communication-overview ends -->
         </div>
      </div>
   </div>
   <!-- !Content -->
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();