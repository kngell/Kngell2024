<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- user Details  -->
   <div class="container">
      <div class="row wrapper">
         <div class="col-md-3 profile-wrapper" id="profile">
            <!--====== Profile Start ======-->
            <div class="card profile">
               <div class="card-header profile-header">
                  <div class="profile-cover-photo"></div>
                  <div class="profile-author d-sm-flex">
                     <div class="text-center profile-photo">
                        <img src="../../../../assets/img/profile-photo.png" alt="Profile Photo" class="img-fluid">
                     </div>
                     <div class="profile-name">
                        <h4 class="name">Musa Ahmed</h4>
                        <p class="email">musa@email.com</p>
                     </div>
                  </div>
               </div>
               <div class="card-body profile-body">
                  <div class="profile-title">
                     <a class="profile-link" href="javascript:void(0)">
                        <h5 class="title"><i class="fas fa-info-circle fa-lg"></i>&nbsp;Données personnelles
                        </h5>
                     </a>

                  </div>
                  <div class="profile-details">
                     <div class="single-details-item d-flex flex-wrap">
                        <div class="details-title">
                           <h6 class="title">Profession:</h6>
                        </div>
                        <div class="details-content media-body">
                           <p>Consultant</p>
                        </div>
                     </div>

                     <div class="single-details-item d-flex flex-wrap">
                        <div class="details-title">
                           <h6 class="title">Phone:</h6>
                        </div>
                        <div class="details-content media-body">
                           <p>+123 456 789 0234</p>
                        </div>
                     </div>
                     <div class="single-details-item d-flex flex-wrap">
                        <div class="details-title">
                           <h6 class="title">Gender:</h6>
                        </div>
                        <div class="details-content media-body">
                           <p>Male</p>
                        </div>
                     </div>
                     <div class="single-details-item d-flex flex-wrap">
                        <div class="details-title">
                           <h6 class="title">Birthday:</h6>
                        </div>
                        <div class="details-content media-body">
                           <p>04 January 1992</p>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="profile-footer mt-45 mb-5">
                  <div class="profile-title">
                     <!-- <a class="btn btn-primary w-100 profile-link" href="javascript:void(0)">Delete</a> -->
                  </div>
               </div>
            </div>
            <!--====== Profile Ends ======-->
         </div>
         <!-- /.col -->
         <div class="col-md-9 transaction-wrapper" id="transaction">
            <div class="card transaction">
               <div class="card-header transaction-header">
                  <div class="transaction-cover-photo"></div>
                  <div class="text-center transaction-photo">

                  </div>
                  <div class="profile-card-info">
                     <div class="row">
                        <div class="col-md-4 col-sm-6">
                           <div class="single-card-info d-flex">
                              <div class="card-icon">
                                 <img src="../../../../assets/img/visa.png" alt="Card">
                              </div>
                              <div class="card-info media-body">
                                 <h5 class="card-name">Musa Ahmed</h5>
                                 <p class="card-number">.... 4534 <span>02/20</span></p>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                           <div class="single-card-info d-flex">
                              <div class="card-icon">
                                 <img src="../../../../assets/img/american-express.png" alt="Card">
                              </div>
                              <div class="card-info media-body">
                                 <h5 class="card-name">Musa Ahmed</h5>
                                 <p class="card-number">.... 4534 <span>02/20</span></p>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                           <div class="single-card-info d-flex">
                              <div class="card-icon">
                                 <img src="../../../../assets/img/mastercard.png" alt="Card">
                              </div>
                              <div class="card-info media-body">
                                 <h5 class="card-name">Musa Ahmed</h5>
                                 <p class="card-number">.... 4534 <span>02/20</span></p>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div><!-- /.card-header -->
               <div class="card-body transaction-body">
                  <div class="row menu">
                     <div class="col-sm-6 mycol">
                        <div class="card h-100">
                           <div class="card-body">
                              <h5 class="card-title">Mes commandes</h5>
                              <p class="card-text">Suivre, Retourner ou Acheter à nouveau</p>
                           </div>
                           <form class="user_form">
                              <input type="hidden" name="userID" id="userID" value="">
                              <input type="hidden" name="template" id="template" value="commandes">
                           </form>
                        </div>
                     </div>
                     <div class="col-sm-6 mycol">
                        <div class="card h-100">
                           <div class="card-body">
                              <h5 class="card-title">Données personnels</h5>
                              <p class="card-text">Modifier ou supprimer mes données personnels, mot
                                 de passe, email etc..</p>
                           </div>
                        </div>
                     </div>
                     <div class="col-sm-6 mycol">
                        <div class="card h-100">
                           <div class="card-body">
                              <h5 class="card-title">Mes addresses</h5>
                              <p class="card-text">Modifier ou ajouter les adresses de livraison et de
                                 facturation</p>
                           </div>
                        </div>
                     </div>
                     <div class="col-sm-6 mycol">
                        <div class="card h-100">
                           <div class="card-body">
                              <h5 class="card-title">Moyens de paiement</h5>
                              <p class="card-text">Ajouter, modifier ou supprimer vos moyens de
                                 paiement</p>
                           </div>
                        </div>
                     </div>
                  </div>

               </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
         </div>
      </div>
      <hr class="my-4">
   </div>
   <!-- !user Details -->

   <input type="hidden" id="ipAddress" style="display:none" value="">
</main>
<!-- End  Main -->

<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>
<?php $this->end();