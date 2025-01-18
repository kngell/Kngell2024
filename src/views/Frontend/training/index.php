<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/main') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>

<!-- Content -->
<header class="header">
   <div class="header__logo-box">
      <img src="../../../assets/img/training/logo-white.png" alt="Logo" class="header__logo">
   </div>
   <div class="header__title-box">
      <h1 class="heading-primary">
         <span class="heading-primary--main">Outdoors</span>
         <span class="heading-primary--sub">is where life happens</span>
      </h1>
      <a href="#" class="btn btn--white btn--animated">Discover our tours</a>
   </div>

</header>
<main id="main-site">
   <section class="section-about">
      <div class="u-center-text u-margin-bottom-big">
         <h2 class="heading-secondary">
            Exciting tours for adventurous peoples
         </h2>
      </div>
      <div class="row">
         <div class="col-1-of-2">
            <h3 class="heading-tertiary u-margin-bottom-small">you are going to fall in love with the nature</h3>
            <p class="paragraph"> Lorem ipsum dolor sit amet consectetur adipisicing elit. Quod dolor molestias ipsa
               eligendi ad. Reprehenderit quo cum blanditiis autem natus officiis, ad temporibus incidunt </p>
            <h3 class="heading-tertiary u-margin-bottom-small">Live adenture like you never has been before</h3>
            <p class="paragraph"> Lorem ipsum dolor sit amet, consectetur adipisicing elit. Tempora obcaecati
               incidunt
               sunt voluptatum libero assumenda neque officiis.</p>
            <a href="#" class="btn-text">Learn more &rarr;</a>
         </div>
         <div class="col-1-of-2">
            <div class="composition">
               <img src="../../../assets/img/training/nat-1-large.jpg" alt="Photo 1"
                  class="composition__photo composition__photo--p1">
               <img src="../../../assets/img/training/nat-2-large.jpg" alt="Photo 2"
                  class="composition__photo composition__photo--p2">
               <img src="../../../assets/img/training/nat-3-large.jpg" alt="Photo 3"
                  class="composition__photo composition__photo--p3">
            </div>
         </div>
      </div>
   </section>
   <section class="section-features">

      <div class="row">
         <div class="col-1-of-4">
            <div class="feature-box">
               <i class="feature-box__icon icon-basic-world"></i>
               <h3 class="heading-tertiary u-margin-bottom-small">Explore the world</h3>
               <p class="feature-box__text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Tempora
                  obcaecati adipisicing.</p>
            </div>
         </div>
         <div class="col-1-of-4">
            <div class="feature-box">
               <i class="feature-box__icon icon-basic-compass"></i>
               <h3 class="heading-tertiary u-margin-bottom-small">Meet nature</h3>
               <p class="feature-box__text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Tempora
                  obcaecati adipisicing.</p>
            </div>
         </div>
         <div class="col-1-of-4">
            <div class="feature-box">
               <i class="feature-box__icon icon-basic-map"></i>
               <h3 class="heading-tertiary u-margin-bottom-small">Find your way</h3>
               <p class="feature-box__text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Tempora
                  obcaecati adipisicing.</p>
            </div>
         </div>
         <div class="col-1-of-4">
            <div class="feature-box">
               <i class="feature-box__icon icon-basic-heart"></i>
               <h3 class="heading-tertiary u-margin-bottom-small">Live a healthier life</h3>
               <p class="feature-box__text">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Tempora
                  obcaecati adipisicing.</p>
            </div>
         </div>
      </div>
   </section>
   <section class="section section-tours">
      <div class="u-center-text u-margin-bottom-big">
         <h2 class="heading-secondary">
            Most popular tours
         </h2>
      </div>
      <div class="row">
         <div class="col-1-of-3">
            <div class="card">
               <div class="card__side card__side--front">
                  <div class="card__picture card__picture--1">
                     &nbsp;
                  </div>
                  <h4 class="card__heading">
                     <span class="card__heading-span card__heading-span--1">The sea explorer</span>

                  </h4>
                  <div class="card__details">
                     <ul>
                        <li>3 days tours</li>
                        <li>Up to 30 peoples</li>
                        <li>2 tours guide</li>
                        <li>Sleep in cozy hotels</li>
                        <li>Difficulty: esay</li>
                     </ul>
                  </div>
               </div>
               <div class="card__side card__side--back card__side--back-1">
                  <div class="card__cta">
                     <div class="card__price-box">
                        <p class="card__price-only">Only</p>
                        <p class="card__price-value">$297</p>
                     </div>
                     <a href="#" class="btn btn--white">Book now!</a>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-1-of-3">
            <div class="card">
               <div class="card__side card__side--front">
                  <div class="card__picture card__picture--2">
                     &nbsp;
                  </div>
                  <h4 class="card__heading">
                     <span class="card__heading-span card__heading-span--2">The forest hiker</span>

                  </h4>
                  <div class="card__details">
                     <ul>
                        <li>7 day tours</li>
                        <li>Up to 40 people</li>
                        <li>6 tour guide</li>
                        <li>Sleep in provided tents</li>
                        <li>Difficulty: medium</li>
                     </ul>
                  </div>
               </div>
               <div class="card__side card__side--back card__side--back-2">
                  <div class="card__cta">
                     <div class="card__price-box">
                        <p class="card__price-only">Only</p>
                        <p class="card__price-value">$497</p>
                     </div>
                     <a href="#" class="btn btn--white">Book now!</a>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-1-of-3">
            <div class="card">
               <div class="card__side card__side--front">
                  <div class="card__picture card__picture--3">
                     &nbsp;
                  </div>
                  <h4 class="card__heading">
                     <span class="card__heading-span card__heading-span--3">The snow adventurer</span>

                  </h4>
                  <div class="card__details">
                     <ul>
                        <li>5 days tour</li>
                        <li>Up to 15 people</li>
                        <li>3 tour guide</li>
                        <li>Sleep in provided hotels</li>
                        <li>Difficulty: hard</li>
                     </ul>
                  </div>
               </div>
               <div class="card__side card__side--back card__side--back-3">
                  <div class="card__cta">
                     <div class="card__price-box">
                        <p class="card__price-only">Only</p>
                        <p class="card__price-value">$897</p>
                     </div>
                     <a href="#" class="btn btn--white">Book now!</a>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="u-center-text .u-margin-top-hudge">
         <a href="#" class="btn btn--green">Discover all tours</a>
      </div>
   </section>

</main>
<!-- <section class="grid-system">
      <div class="row">
         <div class="col-1-of-2">
            Col 1 of 2
         </div>
         <div class="col-1-of-2">
            Col 1 of 2
         </div>
      </div>
      <div class="row">
         <div class="col-1-of-3">
            Col 1 of 3
         </div>
         <div class="col-1-of-3">
            Col 1 of 3
         </div>
         <div class="col-1-of-3">
            Col 1 of 3
         </div>
      </div>
      <div class="row">
         <div class="col-1-of-3">
            Col 1 of 3
         </div>
         <div class="col-2-of-3">
            Col 2 of 3
         </div>
      </div>
      <div class="row">
         <div class="col-1-of-4">
            Col 1 of 4
         </div>
         <div class="col-1-of-4">
            Col 1 of 4
         </div>
         <div class="col-1-of-4">
            Col 1 of 4
         </div>
         <div class="col-1-of-4">
            Col 1 of 4
         </div>
      </div>
      <div class="row">
         <div class="col-1-of-4">
            Col 1 of 4
         </div>
         <div class="col-1-of-4">
            Col 1 of 4
         </div>
         <div class="col-2-of-4">
            Col 2 of 4
         </div>
      </div>
      <div class="row">
         <div class="col-1-of-4">
            Col 1 of 4
         </div>
         <div class="col-3-of-4">
            Col 3 of 4
         </div>
      </div>
      </div>
   </section> -->
<!-- Fin Content -->
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js() ?>

<?php $this->end();