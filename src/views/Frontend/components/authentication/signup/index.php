<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class="container">
      <div class="row">
         <!-- Register Form -->
         <?=$message ?? '' ?>
         <div class="form-container register-frm-box">
            <div class="user-profile-box">
               <div class="user-profile">
                  <h2>Upload Profile Picture</h2>
                  <label for="profile-input" class="user-profile__label">
                     <input type="file" name="user-profile" form="register-form" id="user-profile__input"
                        class="user-profile__input" multiple>
                     <img src="../../../../../assets/img/avatar.png" alt="Profile" class="user-profile__img">
                     <span class="user-profile__text">Choose a file</span>
                  </label>
                  <div class="user-profile__name">
                     <p>Daniel AKONO</p>
                  </div>
               </div>
            </div>
            <?=$authForm ?? '' ?>
         </div>
      </div>
   </div>
   <!-- Fin Content -->
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/components/authentication/index') ?>

<?php $this->end();