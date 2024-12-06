<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class="container pb-4">
      <div class="row justify-content-center" id="resetpass_wrapper">
         <div class="col-lg-6 my-auto">
            <div class="card-group" id="form-wrapper">
               <div class="card rounded-right p-4" style="flex-grow:2;">
                  <h3 class="text-center font-weight-bold text-primary"> Nouveau mot de passe </h3>
                  <hr class="my-3">
                  <form action="#" method="post" class="px-3 needs-validation" novalidate id="resetpass-frm">

                     <input type="hidden" name="user_data" value="<?=$_GET['url']?>">
                     <div class="align-self-center" id="alertErr"></div>
                     <div class="text-center lead mb-2"><?= ''; ?>
                     </div>
                     <div class="mb-3">
                        <div class="input-group-text"> <span class="input-group-text rounded-0"> <i
                                 class="fas fa-key fa-lg"></i> </span>
                        </div>
                        <input type="password" name="password" id="r_password" class="form-control rounded-0"
                           placeholder="New Password">
                        <div class="invalid-feedback"></div>
                     </div>
                     <div class="mb-3">
                        <div class="input-group-text"> <span class="input-group-text rounded-0"> <i
                                 class="fas fa-key fa-lg"></i> </span> </div>
                        <input type="password" name="cpassword" id="r_cpassword" class="form-control rounded-0"
                           placeholder="Confirm New Password">
                        <div class="invalid-feedback"></div>
                     </div>
                     <div class="input-group-text">
                        <input type="submit" value="Reset password" name="submit" id="submitBtn"
                           class="btn btn-primary btn-block mybtn">
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- Fin Content -->
   <input type="hidden" id="ipAddress" style="display:none" value="">
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>
<?php $this->end();