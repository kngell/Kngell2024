<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<link href="<?= $this->asset('path', 'css') ?? ''?>" rel="stylesheet" type="text/css">
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site">
   <!-- Content -->
   <div class="popup-modal">
      <div class="center">
         <button id="open-popup">Show Popup</button>
      </div>
      <div class="popup">
         <div class="head">
            <div class="icon">
               <div class="box">
                  <i class="fa-solid fa-check"></i>
               </div>
            </div>
         </div>
         <div class="body">
            <h3>Success</h3>
            <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit.</p>
            <button class="close-btn">&times;Close</button>
         </div>
      </div>
   </div>
   <!-- Fin Content -->
   <input type="hidden" id="ipAddress" style="display:none" value="<?=H_visitors::getIP()?>">
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<script type="text/javascript" src="<?= $this->asset('pathjs', 'js') ?? ''?>">
</script>
<?php $this->end();
