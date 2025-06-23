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
         <img src="../../../assets/img/ecommerce/IphonePro.jpg" alt="Iphone Pro" class="image">
      </div>
   </section>
   <section class="small-banner">
      <div class="banner">
         <div class="banner-left">
            <div class="banner-left__wide">
               <div class="image-container">
                  <img src="../../../assets/img/ecommerce/PlayStation.png" alt="Play Station"
                     class="image-container--img">
               </div>
               <div class="text-container">
                  <h2 class="text-container__heading">Playstation 5</h2>
                  <p class="text-container__body">Incredibly powerful CPUs, GPUs, and an SSD with integrated I/O will
                     redefine your PlayStation
                     experience.</p>
               </div>
            </div>
            <div class="banner-left__squares">
               <div class="banner-square light">
                  <div class="banner-square__text-container">
                     <h2 class="banner-square__text-container--heading">
                        Apple AirPods&nbsp;<span>Max</span>
                     </h2>
                     <p class="banner-square__text-container--body">Computational audio. Listen, it's powerful</p>
                  </div>
                  <div class="banner-square__img-container">
                     <img src="../../../assets/img/ecommerce/square-img1.png" alt="Square image 1" class="img">
                  </div>
               </div>
               <div class="banner-square dark">
                  <div class="banner-square__text-container">
                     <h2 class="banner-square__text-container--heading">
                        Apple Vision&nbsp;<span>Pro</span>
                     </h2>
                     <p class="banner-square__text-container--body">An immersive way to experience entertainment</p>
                  </div>
                  <div class="banner-square__img-container">
                     <img src="../../../assets/img/ecommerce/square-img2.png" alt="Square image 2" class="img">
                  </div>
               </div>

            </div>
         </div>
         <div class="banner-right">
            <div class="banner-right__content">
               <div class="text-container">
                  <h2 class="text-container__heading">Macbook&nbsp;<span>Air</span></h2>
                  <p class="text-container__body">The new 15‑inch MacBook Air makes room for more of what you love with
                     a spacious Liquid Retina
                     display.</p>
               </div>
               <button class="btn btn-outline-dark">Shop now</button>
            </div>
            <div class="banner-right__img-container">
               <img src="../../../assets/img/ecommerce/MacBook Pro 14.png" alt="Mac Book Air" class="image">
            </div>
         </div>
      </div>
   </section>
   <section class="category-section">
      <div class="container category-container">
         <div class="category-header">
            <h2 class="category-header__title">Browse By Category</h2>
            <div class="category-header__arrows">
               <span class="arrow arrow-left">
                  <!-- Left Arrow SVG -->
                  <svg class="icon icon-arrow-left">
                     <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-left"></use>
                  </svg>
               </span>
               <span class="arrow arrow-right">
                  <!-- Right Arrow SVG -->
                  <svg class="icon icon-arrow-right">
                     <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-right"></use>
                  </svg>
               </span>
            </div>
         </div>
         <div class="category-body">
            <div class="category-body__card">
               <div class="category-body__card--icon-wrapper">
                  <!-- Phones SVG -->
                  <svg class="icon">
                     <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-phone"></use>
                  </svg>
               </div>
               <p class="category-body__card--icon-label">Phones</p>
            </div>
            <div class="category-body__card">
               <div class="category-body__card--icon-wrapper">
                  <!-- Smart Watches SVG -->
                  <svg class="icon">
                     <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-smart-watches"></use>
                  </svg>
               </div>
               <p class="category-body__card--icon-label">Smart Watches</p>
            </div>
            <div class="category-body__card">
               <div class="category-body__card--icon-wrapper">
                  <!-- Cameras SVG -->
                  <svg class="icon">
                     <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cameras"></use>
                  </svg>
               </div>
               <div class="category-body__card--icon-label">Cameras</div>
            </div>
            <div class="category-body__card">
               <div class="category-body__card--icon-wrapper">
                  <!-- Headphones SVG -->
                  <svg class="icon">
                     <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-headphones"></use>
                  </svg>
               </div>
               <div class="category-body__card--icon-label">Headphones</div>
            </div>
            <div class="category-body__card">
               <div class="category-body__card--icon-wrapper">
                  <!-- Computers SVG -->
                  <svg class="icon">
                     <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-computers"></use>
                  </svg>
               </div>
               <div class="category-body__card--icon-label">Computers</div>
            </div>
            <div class="category-body__card">
               <div class="category-body__card--icon-wrapper">
                  <!-- Gaming SVG -->
                  <svg class="icon">
                     <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-gaming"></use>
                  </svg>
               </div>
               <div class="category-body__card--icon-label">Gaming</div>
            </div>
         </div>
      </div>
   </section>
   <section class="new-arrival-section">
      <div class="products">
         <div class="products__container">
            <div class="products__tags">
               <div class="products__tag products__tag--selected">
                  <span>New Arrival</span>
                  <div class="products__tag-underline"></div>
               </div>
               <div class="products__tag">Bestseller</div>
               <div class="products__tag">Featured Products</div>
            </div>
            <div class="products__grid">
               <!-- Repeat this .products__row for each row -->
               <div class="products__row">
                  <!-- Repeat this .product-card for each product -->
                  <div class="product-card">
                     <div class="product-card__top">
                        <button class="product-card__like" aria-label="Like">
                           <!-- Inline SVG for heart icon -->
                           <svg viewBox="0 0 27 24" fill="none">
                              <path
                                 d="M3.6 13.21L12.98 22.02C13.31 22.33 13.47 22.48 13.67 22.48C13.86 22.48 14.03 22.33 14.35 22.02L23.73 13.21C26.34 10.76 26.66 6.73 24.46 3.90L24.05 3.37C21.43 -0.01 16.16 0.55 14.32 4.42C14.06 4.96 13.28 4.96 13.02 4.42C11.17 0.55 5.91 -0.01 3.28 3.37L2.87 3.90C0.68 6.73 0.99 10.76 3.6 13.21Z"
                                 fill="#8F556A" stroke="#F99417" stroke-width="1.4" />
                           </svg>
                        </button>
                     </div>
                     <div class="product-card__image" style="background-image: url('your-image-url.svg');"></div>
                     <div class="product-card__info">
                        <div class="product-card__name">Apple iPhone 14 Pro Max 128GB Deep Purple (MQ9T3RX/A)</div>
                        <div class="product-card__price">$900</div>
                        <button class="product-card__buy">Buy Now</button>
                     </div>
                  </div>
                  <!-- ...repeat .product-card for each product in the row... -->
               </div>
               <!-- ...repeat .products__row for each row... -->
            </div>
         </div>
      </div>







      <div class="small-product-card">
         <div class="small-product-card__top">
            <span class="small-product-card__top--like">
               <!-- Like Icon SVG -->
               <svg class="icon">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-like"></use>
               </svg>
            </span>
         </div>
         <div class="small-product-card__image-container">
            <img src="../../../assets/img/ecommerce/Iphone 14 pro.png" alt="Iphone 14 Pro" class="image">
         </div>
         <div class="small-product-card__info">
            <div class="small-product-card__info--text">
               <p class="description">
                  Apple iPhone 14 Pro Max 128GB Deep Purple (MQ9T3RX/A)
               </p>
               <h5 class="price">$900</h5>
            </div>
            <button class="btn btn-dark-small">Buy Now</button>
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
