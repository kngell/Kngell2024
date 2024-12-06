<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->

<link href="<?= $this->asset('pathcss', 'css') ?? ''?>" rel="stylesheet" type="text/css">
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- user Details  -->
   <div class="container">
      <div class="row wrapper">
         <div class="col-md-3 mini-profile-wrapper" id="mini-profile">
            <!--====== mini-profile Start ======-->
            <?= $user_profile ?? ''?>
            <!--====== mini-profile Ends ======-->
         </div>
         <!-- /.col -->
         <div class="col-md-9 transaction-wrapper" id="transaction">
            <?= $user_payment_card ?? ''?>
            <!-- /.card -->
            <div class="row mt-4 transaction-button">
               <?= $buttons ?? ''?>
            </div>
         </div>
      </div>
      <hr class="my-4">
   </div>
   <!-- !user Details -->
</main>
<!-- End  Main -->

<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<script type="text/javascript" src="<?= $this->asset('pathsjs', 'js') ?? ''?>">
</script>
<?php $this->end();
