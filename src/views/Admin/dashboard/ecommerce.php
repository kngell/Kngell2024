<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>

<main id="main-site">
   <!-- Content -->
   <div class="admin-dashboard admin-dashboard-ecommerce">
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
                           <span class="k-card__title u-font-weight-bld">Seller of the month</span>
                           <span class="k-btn-icon k-btn-icon-static"><i class="ri-bar-chart-line"></i></span>
                        </div>

                        <div class="k-card__body u-display-flex u-justify-content-between u-align-items-end">

                           <div class="left-side">
                              <h2 class="u-margin-b-10">$12.5K</h2>
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
                                 <i class="ri-trophy-line"></i>
                              </div>
                           </div>

                        </div>
                     </div>
                  </div>
                  <div class="k-col-sm-6 k-col-lg-3 u-margin-b-20 u-margin-b-0-lg">
                     <div class="k-card">
                        <div class="k-card__header">
                           <span class="k-card__title u-font-weight-bld">Active Products</span>
                           <span class="k-btn-icon k-btn-icon-static"><i class="ri-bar-chart-line"></i></span>
                        </div>

                        <div class="k-card__body u-display-flex u-justify-content-between u-align-items-end">
                           <div class="left-side">
                              <h2 class="u-margin-b-10">127</h2>
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
                           <span class="k-card__title u-font-weight-bld">New Customers</span>
                           <span class="k-btn-icon k-btn-icon-static"><i class="ri-bar-chart-line"></i></span>
                        </div>

                        <div class="k-card__body u-display-flex u-justify-content-between u-align-items-end">
                           <div class="left-side">
                              <h2 class="u-margin-b-10">1,223</h2>
                              <p class="u-font-size-12 u-display-flex u-align-items-center">
                                 <span
                                    class="admin-indicator-up u-margin-r-10 u-display-inline-flex u-align-items-center"><i
                                       class="ri-arrow-up-line"></i>4%
                                 </span>
                                 <span class="admin-faded-text">From last month</span>
                              </p>
                           </div>
                           <div class="right-side">
                              <div class="k-badge k-badge-warn admin-bdg">
                                 <i class="ri-group-line"></i>
                              </div>
                           </div>

                        </div>
                     </div>
                  </div>
               </div>
            </section>
            <section class="admin-store-revenue u-padding-y-20">
               <div class="k-card">
                  <div class="k-card__header">
                     <span class="k-car__title u-font-weight-bld">Revenue</span>
                  </div>

                  <div class="k-card__body u-padding-0 u-padding-x-20">
                     <div class="k-tbl__header u-padding-20 u-text-align-center u-overflow-hidden">

                        <div class="k-row">
                           <div class="k-col-6 k-col-sm-3 u-margin-b-8 u-margin-b-0-sm">
                              <div class="k-h2">679</div>
                              <span class="u-font-size-12">Total Orders</span>
                           </div>

                           <div class="k-col-6 k-col-sm-3 u-margin-b-8 u-margin-b-0-sm">
                              <div class="k-h2">$24.23K</div>
                              <span class="u-font-size-12">Revenue</span>
                           </div>

                           <div class="k-col-6 k-col-sm-3">
                              <div class="k-h2">$1,232</div>
                              <span class="u-font-size-12">Refunded</span>
                           </div>

                           <div class="k-col-6 k-col-sm-3">
                              <div class="k-h2">$2,531</div>
                              <span class="u-font-size-12">Daily profit</span>
                           </div>
                        </div>
                     </div>
                     <div class="k-row u-align-items-center">
                        <div class="k-col-md-9 u-margin-b-12 u-margin-b-0-md">
                           <div class="admin-chart u-padding-20">
                              <canvas id="admin-revenue"></canvas>
                           </div>
                        </div>
                        <div class="k-col-md-3">
                           <div class="admin-revenue-summary u-padding-20">
                              <div class="u-margin-b-20">
                                 <h2>$79,798.80</h2>
                                 <span
                                    class="admin-faded-text u-font-size-12 u-font-weight-med u-transform-uppercase">Total
                                    balance</span>
                              </div>
                              <div class="u-margin-b-10">
                                 <h2>$10,426.90</h2>
                                 <span class="admin-faded-text u-font-size-12">Revenue</span>
                              </div>
                              <div class="u-margin-b-10">
                                 <h2>$6,426.90</h2>
                                 <span class="admin-faded-text u-font-size-12">Expenses</span>
                              </div>
                              <div class="u-margin-b-10">
                                 <h2>$3,426.90</h2>
                                 <span class="admin-faded-text u-font-size-12">Profits</span>
                              </div>
                              <a href="#" class="k-btn">Read report</a>
                           </div>
                        </div>
                     </div>

                  </div>
               </div>
            </section>

            <section class="admin-recent-orders u-padding-y-20">
               <div class="k-row">

                  <div class="k-col-md-6 k-col-lg-7 u-margin-b-20 u-margin-b-0-md">
                     <div class="k-card admin-orders">
                        <div class="k-card__header">
                           <span class="k-card__title u-font-weight-bld">Recent Orders</span>
                        </div>
                        <div class="k-card__body u-padding-0">
                           <div class="u-overflow-auto">
                              <table class="k-tbl">
                                 <thead class="k-tbl__header">
                                    <tr class="k-tbl__row">
                                       <th class="k-tbl__head">Order Id</th>
                                       <th class="k-tbl__head">Customer</th>
                                       <th class="k-tbl__head">Item</th>
                                       <th class="k-tbl__head">Quantity</th>
                                       <th class="k-tbl__head">Total price</th>
                                       <th class="k-tbl__head">Status</th>
                                    </tr>
                                 </thead>
                                 <tbody class="k-tbl__body">
                                    <tr class="k-tbl__row">
                                       <td class="k-tbl__data">#P1011</td>

                                       <td class="k-tbl__data">
                                          <div class="u-display-flex u-text-align-center">
                                             <div class="u-margin-r-10">
                                                <div class="k-avatar k-avatar-30">
                                                   <img src="../../../assets/img/admin/user-f-3.jpg" alt="User"
                                                      class="k-avatar__img">
                                                </div>
                                             </div>
                                             <div class="user-details">
                                                <span class="u-font-size-12">
                                                   Brie Doyle
                                                </span>
                                             </div>
                                          </div>
                                       </td>

                                       <td class="k-tbl__data">
                                          T-Shirts
                                       </td>

                                       <td class="k-tbl__data">12</td>

                                       <td class="k-tbl__data">$431</td>

                                       <td class="k-tbl__data"><span class="k-badge k-badge-success">Paid</span>
                                       </td>
                                    </tr>
                                    <tr class="k-tbl__row">
                                       <td class="k-tbl__data">#P1012</td>

                                       <td class="k-tbl__data">
                                          <div class="u-display-flex u-text-align-center">
                                             <div class="u-margin-r-10">
                                                <div class="k-avatar k-avatar-30">
                                                   <img src="../../../assets/img/admin/user-m-2.jpg" alt="User"
                                                      class="k-avatar__img">
                                                </div>
                                             </div>
                                             <div class="user-details">
                                                <span class="u-font-size-12">
                                                   Rayan Dun
                                                </span>
                                             </div>
                                          </div>
                                       </td>

                                       <td class="k-tbl__data">
                                          Blue Skateboard
                                       </td>

                                       <td class="k-tbl__data">3</td>

                                       <td class="k-tbl__data">$120</td>

                                       <td class="k-tbl__data"><span class="k-badge k-badge-success">Paid</span>
                                       </td>
                                    </tr>

                                    <tr class="k-tbl__row">
                                       <td class="k-tbl__data">#P1013</td>

                                       <td class="k-tbl__data">
                                          <div class="u-display-flex u-text-align-center">
                                             <div class="u-margin-r-10">
                                                <div class="k-avatar k-avatar-30">
                                                   <img src="../../../assets/img/admin/user-f-2.jpg" alt="User"
                                                      class="k-avatar__img">
                                                </div>
                                             </div>
                                             <div class="user-details">
                                                <span class="u-font-size-12">
                                                   Michelle Wen
                                                </span>
                                             </div>
                                          </div>
                                       </td>

                                       <td class="k-tbl__data">
                                          The automate Javascript Guide
                                       </td>

                                       <td class="k-tbl__data">1</td>

                                       <td class="k-tbl__data">$64</td>

                                       <td class="k-tbl__data"><span class="k-badge k-badge-warn">Pending</span>
                                       </td>
                                    </tr>

                                    <tr class="k-tbl__row">
                                       <td class="k-tbl__data">#P1014</td>

                                       <td class="k-tbl__data">
                                          <div class="u-display-flex u-text-align-center">
                                             <div class="u-margin-r-10">
                                                <div class="k-avatar k-avatar-30">
                                                   <img src="../../../assets/img/admin/user-f-1.jpg" alt="User"
                                                      class="k-avatar__img">
                                                </div>
                                             </div>
                                             <div class="user-details">
                                                <span class="u-font-size-12">
                                                   Daniel AKono
                                                </span>
                                             </div>
                                          </div>
                                       </td>

                                       <td class="k-tbl__data">
                                          Lg autowire 29in LPS Monitor
                                       </td>

                                       <td class="k-tbl__data">1</td>

                                       <td class="k-tbl__data">$149</td>

                                       <td class="k-tbl__data"><span class="k-badge k-badge-danger">Canceled</span>
                                       </td>
                                    </tr>

                                    <tr class="k-tbl__row">
                                       <td class="k-tbl__data">#P1015</td>

                                       <td class="k-tbl__data">
                                          <div class="u-display-flex u-text-align-center">
                                             <div class="u-margin-r-10">
                                                <div class="k-avatar k-avatar-30">
                                                   <img src="../../../assets/img/admin/user-m-1.jpg" alt="User"
                                                      class="k-avatar__img">
                                                </div>
                                             </div>
                                             <div class="user-details">
                                                <span class="u-font-size-12">
                                                   Kayo Smith
                                                </span>
                                             </div>
                                          </div>
                                       </td>

                                       <td class="k-tbl__data">
                                          Men's Gray Sneakers - Size 9
                                       </td>

                                       <td class="k-tbl__data">2</td>

                                       <td class="k-tbl__data">$176</td>

                                       <td class="k-tbl__data"><span class="k-badge k-badge-success">Paid</span>
                                       </td>
                                    </tr>

                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                  </div>

                  <div class="k-col-md-6 k-col-lg-5">
                     <div class="k-card admin-orders">
                        <div class="k-card__header">
                           <span class="k-card__title u-font-weight-bld">Top Selling categories</span>
                        </div>
                        <div class="k-card__body u-padding-0">
                           <div class="u-overflow-auto">
                              <table class="k-tbl">
                                 <thead class="k-tbl__header">
                                    <tr class="k-tbl__row">
                                       <th class="k-tbl__head">Name</th>
                                       <th class="k-tbl__head">Quantity</th>
                                       <th class="k-tbl__head">Earning</th>
                                       <th class="k-tbl__head">Status</th>
                                    </tr>
                                 </thead>
                                 <tbody class="k-tbl__body">
                                    <tr class="k-tbl__row">
                                       <td class="k-tbl__data">
                                          <div class="u-display-flex u-text-align-center">
                                             <div class="u-margin-r-10">
                                                <div class="k-avatar k-avatar-30">
                                                   <img src="../../../assets/img/admin/user-f-1.jpg" alt="User"
                                                      class="k-avatar__img">
                                                </div>
                                             </div>
                                             <div class="user-details">
                                                <span class="u-font-size-12">
                                                   Clothing
                                                </span>
                                             </div>
                                          </div>
                                       </td>

                                       <td class="k-tbl__data">3,626</td>

                                       <td class="k-tbl__data">$42,316.19</td>

                                       <td class="k-tbl__data"><span class="k-badge k-badge-success">In Stock</span>
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
                                                <span class="u-font-size-12">
                                                   Footwear
                                                </span>
                                             </div>
                                          </div>
                                       </td>

                                       <td class="k-tbl__data">1,124</td>

                                       <td class="k-tbl__data">$32,316.19</td>

                                       <td class="k-tbl__data"><span class="k-badge k-badge-success">In Stock</span>
                                       </td>
                                    </tr>

                                    <tr class="k-tbl__row">
                                       <td class="k-tbl__data">
                                          <div class="u-display-flex u-text-align-center">
                                             <div class="u-margin-r-10">
                                                <div class="k-avatar k-avatar-30">
                                                   <img src="../../../assets/img/admin/user-f-2.jpg" alt="User"
                                                      class="k-avatar__img">
                                                </div>
                                             </div>
                                             <div class="user-details">
                                                <span class="u-font-size-12">
                                                   Electronics
                                                </span>
                                             </div>
                                          </div>
                                       </td>

                                       <td class="k-tbl__data">563</td>

                                       <td class="k-tbl__data">$22,316.19</td>

                                       <td class="k-tbl__data"><span class="k-badge k-badge-warn">Low Stock</span>
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
                                                <span class="u-font-size-12">
                                                   Food
                                                </span>
                                             </div>
                                          </div>
                                       </td>

                                       <td class="k-tbl__data">231</td>

                                       <td class="k-tbl__data">$4,316.19</td>

                                       <td class="k-tbl__data"><span class="k-badge k-badge-warn">Low Stock</span>
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
                                                <span class="u-font-size-12">
                                                   Book
                                                </span>
                                             </div>
                                          </div>
                                       </td>

                                       <td class="k-tbl__data">0</td>

                                       <td class="k-tbl__data">$1,000.0</td>

                                       <td class="k-tbl__data"><span class="k-badge k-badge-danger">Out of Stock</span>
                                       </td>
                                    </tr>

                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                  </div>

               </div>

            </section>

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