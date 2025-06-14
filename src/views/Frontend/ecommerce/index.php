<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site" class="main">
   <!-- Content -->
   <section class="hero">
      <div class="hero__content">
         <div class="hero__content-text">
            <div class="hero__content-text--titles">
               <p class="title-intro">Pro.beyond.</p>
               <h1 class="title-main animate-fade-in-up animate-delay-200">Iphone 14&nbsp;<span class="title-sub">Pro
                     </span">
               </h1>
            </div>
            <p class="hero__content-text--body animate-fade-in-up animate-delay-300">Created to change everything for
               the
               better. for everyone</p>
         </div>
         <button class="hero__content-cta btn btn-outline-white">Shop Now</button>
      </div>
      <div class="hero__img-container animate-fade-in-right animate-delay-200">
         <img src="../../../assets/img/hero/IphonePro.jpg" alt="Iphone Pro" class="image">
      </div>
   </section>
   <section class="banner">
      <div class="banner-left">
         <div class="wide-square">
            <div class="product-container">
               <img src="#" alt="" class="img">
               <div class="text">
                  <h2>Playstation</h2>
                  <p>Incredibly powerful CPUs, GPUs, and an SSD with integrated I/O will redefine your PlayStation
                     experience.</p>
               </div>
            </div>
         </div>
         <div class="squares">
            <div class="square-banner">
               <div class="text">
                  <h2 class="title">
                     Apple AirPods Max
                  </h2>
                  <p class="body">Computational audio. Listen, it's powerful</p>
               </div>
               <div class="img-container">
                  <img src="#" alt="">
               </div>
            </div>
            <div class="square-banner">
               <div class="text">
                  <h2 class="title">
                     Apple Vision Pro
                  </h2>
                  <p class="body">An immersive way to experience entertainment</p>
               </div>
               <div class="img-container">
                  <img src="#" alt="">
               </div>
            </div>
            <div class="square-banner"></div>
         </div>
      </div>
      <div class="banner-right">
         <div class="content">
            <div class="text">
               <h2>Macbook&nbsp;<span>Air</span></h2>
               <p>The new 15‑inch MacBook Air makes room for more of what you love with a spacious Liquid Retina
                  display.</p>
            </div>
            <button class="btn btn-outline-black">Shop now</button>
         </div>
         <div class="img-container">
            <img src="#" alt="">
         </div>
      </div>
   </section>
   <!-- Fin Content -->

</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();