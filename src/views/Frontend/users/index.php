<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class="container justify-content-center align-items-center">
      <div class="row">
         <div class="col-lg-6 offset-md-3">



         </div>
      </div>
      <div class="row">
         <div class="col-md-6 offset-md-3">
            <div class="panel panel-login">
               <div class="panel-heading">
                  <div class="row">
                     <div class="col-sm-6 text-center">
                        <a href="/user/login" class="active" id="login-form-link">Login</a>
                     </div>
                     <div class="col-sm-6 text-center">
                        <a href="/user/register" id="">Register</a>
                     </div>
                  </div>
                  <hr>
               </div>
               <div class="panel-body">
                  <div class="row">
                     <div class="col-lg-12">
                        <!-- Login Form -->
                        <?=$authForm ?? '' ?>
                     </div>
                  </div>
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
<?= $this->js('path') ?>

<?php $this->end();