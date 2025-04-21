<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/sass2/oldlayout/main') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<!-- Navigation -->
<div class="navigation">
   <input type="checkbox" name="" id="navigation-toggle" class="navigation__checkbox">
   <label for="navigation-toggle" class="navigation__button">
      <span class="navigation__icon"></span>
   </label>
   <div class="navigation__background">&nbsp;</div>
   <nav class="nav">
      <ul class="nav__list">
         <li class="nav__list-item">
            <a href="#" class="nav__list-link"><span>01</span>About natour</a>
         </li>
         <li class="nav__list-item">
            <a href="#" class="nav__list-link"><span>02</span>Your benefits</a>
         </li>
         <li class="nav__list-item">
            <a href="#" class="nav__list-link"><span>03</span>Popular tours</a>
         </li>
         <li class="nav__list-item">
            <a href="#" class="nav__list-link"><span>04</span>Stories</a>
         </li>
         <li class="nav__list-item">
            <a href="#" class="nav__list-link"><span>05</span>Book now</a>
         </li>
      </ul>
   </nav>
</div>
<!-- Header -->
<header class="header">
   <div class="header__logo-box">
      <img src="../../../../assets/img/training/logo-white.png" alt="Logo" class="header__logo-box--img">
   </div>

   <div class="header__title-box">
      <h1 class="heading-1">
         <span class="heading-1--main">Outdoors</span>
         <span class="heading-1--sub">is where life happens</span>
      </h1>
      <a href="#tours-section" class="btn btn-white btn-animated">Discover our tours</a>
   </div>

</header>

<!-- Main -->
<main id="main-site">
   <!-- About section -->
   <section class="section about-section">
      <div class="u-center-text u-margin-bottom-big">
         <h2 class="heading-2">
            Exciting tours for adventurous peoples
         </h2>
      </div>
      <div class="row">
         <div class="col-1-of-2">
            <h3 class="heading-3 u-margin-bottom-small">you are going to fall in love with the nature</h3>
            <p class="paragraph"> Lorem ipsum dolor sit amet consectetur adipisicing elit. Quod dolor molestias ipsa
               eligendi ad. Reprehenderit quo cum blanditiis autem natus officiis, ad temporibus incidunt </p>
            <h3 class="heading-3 u-margin-bottom-small">Live adenture like you never has been before</h3>
            <p class="paragraph"> Lorem ipsum dolor sit amet, consectetur adipisicing elit. Tempora obcaecati
               incidunt
               sunt voluptatum libero assumenda neque officiis.</p>
            <a href="#" class="btn-text">Learn more &rarr;</a>
         </div>
         <div class="col-1-of-2">
            <div class="composition">
               <img
                  srcset="../../../../assets/img/training/nat-1.jpg 300w,../../../../assets/img/training/nat-1-large.jpg 1000w"
                  sizes="(max-width: 56.25em) 20vw, (max-width: 37.5em) 30vw, 300px" alt="Photo 1"
                  class="composition__photo composition__photo--p1"
                  src="../../../../assets/img/training/nat-1-large.jpg">
               <img
                  srcset="../../../../assets/img/training/nat-2.jpg 300w,../../../../assets/img/training/nat-2-large.jpg 1000w"
                  sizes="(max-width: 56.25em) 20vw, (max-width: 37.5em) 30vw, 300px" alt="Photo 2"
                  class="composition__photo composition__photo--p2"
                  src="../../../../assets/img/training/nat-2-large.jpg">
               <img
                  srcset="../../../../assets/img/training/nat-3.jpg 300w,../../../../assets/img/training/nat-3-large.jpg 1000w"
                  sizes="(max-width: 56.25em) 20vw, (max-width: 37.5em) 30vw, 300px" alt="Photo 3"
                  class="composition__photo composition__photo--p3"
                  src="../../../../assets/img/training/nat-3-large.jpg">
               <!-- <img src="../../../../assets/img/training/nat-1-large.jpg" alt="Photo 1"
                  class="composition__photo composition__photo--p1">
               <img src="../../../../assets/img/training/nat-2-large.jpg" alt="Photo 2"
                  class="composition__photo composition__photo--p2">
               <img src="../../../../assets/img/training/nat-3-large.jpg" alt="Photo 3"
                  class="composition__photo composition__photo--p3"> -->
            </div>
         </div>
      </div>
   </section>
   <!-- Feature section -->

   <section class="section features-section">

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

   <!-- Tour section -->
   <section class="section tours-section" id="tours-section">
      <div class="u-center-text u-margin-bottom-big">
         <h2 class="heading-2">
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
                     <a href="#modal" class="btn btn-white">Book now!</a>
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
                     <a href="#modal" class="btn btn-white">Book now!</a>
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
                     <a href="#modal" class="btn btn-white">Book now!</a>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="u-center-text .u-margin-top-hudge">
         <a href="#" class="btn btn-green">Discover all tours</a>
      </div>
   </section>

   <!-- Story section -->
   <section class="section story-section">

      <div class="bg-video">
         <video class="bg-video__content" autoplay muted loop>
            <source src="../../../../assets/img/training/video.mp4" type="video/mp4">
            <source src="../../../../assets/img/training/video.webm" type="video/webm">
            Your browser is not supported!
         </video>
      </div>
      <div class="u-center-text u-margin-bottom-big">
         <h2 class="heading-2">
            We make people genuinely happy
         </h2>
      </div>

      <div class="row">
         <div class="story">
            <figure class="story__shape">
               <img src="../../../../assets/img/training/nat-8.jpg" alt="Person on a tour" class="story__shape-img">
               <figcaption class="story__shape-caption">Mary Smith</figcaption>
            </figure>

            <div class="story__text">

               <h3 class="heading-3 u-margin-bottom-small">
                  i had the best week ever with my family
               </h3>
               <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Eligendi nulla mollitia corrupti quaerat
                  deleniti expedita officiis consequuntur delectus sunt! Vitae accusantium praesentium provident! Ut,
                  obcaecati explicabo. Quasi nostrum optio nulla. expedita officiis consequuntur.</p>
            </div>
         </div>
      </div>

      <div class="row">
         <div class="story">
            <figure class="story__shape">
               <img src="../../../../assets/img/training/nat-9.jpg" alt="Person on a tour" class="story__shape-img">
               <figcaption class="story__shape-caption">Jack Wilson</figcaption>
            </figure>

            <div class="story__text">

               <h3 class="heading-3 u-margin-bottom-small">
                  Wow! My life is completly different now
               </h3>
               <p> expedita officiis consequuntur dolor sit amet consectetur adipisicing elit. Eligendi nulla mollitia
                  corrupti quaerat
                  deleniti expedita officiis consequuntur delectus sunt! Vitae accusantium praesentium provident! Ut,
                  obcaecati explicabo.</p>
            </div>
         </div>
      </div>
      <div class="u-center-text .u-margin-top-hudge">
         <a href="#" class="btn-text">Read all stories &rarr;</a>
      </div>
   </section>

   <!-- Booking Section -->
   <section class="section booking-section">
      <div class="row">
         <div class="book">
            <div class="book__form-container">
               <form action="#" class="form">
                  <div class="u-margin-bottom-medium">
                     <h2 class="heading-2">
                        Start booking now
                     </h2>
                  </div>
                  <div class="form__group">
                     <input type="text" class="form__input" id="name" placeholder="Full Name" required>
                     <label for="name" class="form__label">Full Name</label>
                  </div>
                  <div class="form__group">
                     <input type="email" class="form__input" id="email" placeholder="Email address" required>
                     <label for="email" class="form__label">Email address</label>
                  </div>
                  <div class="form__group u-margin-bottom-medium">
                     <div class="form__radio-group">
                        <input type="radio" name="size" id="small" class="form__radio-input">
                        <label for="small" class="form__radio-label">
                           <span class="form__radio-label--button"></span>
                           Small tour group
                        </label>
                     </div>
                     <div class="form__radio-group">
                        <input type="radio" name="size" id="large" class="form__radio-input">
                        <label for="large" class="form__radio-label">
                           <span class="form__radio-label--button"></span>
                           Large tour group
                        </label>
                     </div>
                  </div>
                  <div class="form__group">
                     <button type="submit" class="btn btn-green">Next step &rarr;</button>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </section>
</main>

<!-- Footer -->
<footer class="footer">
   <div class="footer__logo-box">
      <picture class="footer__logo-box--img">
         <source
            srcset="../../../../assets/img/training/logo-green-small-1x.png 1x, ../../../../assets/img/training/logo-green-small-1x.png 1x"
            media="(max-width: 37.5em)">
         <img
            srcset="../../../../assets/img/training/logo-green-1x.png 1x,../../../../assets/img/training/logo-green-2x.png 2x"
            alt="Full logo" src="../../../../assets/img/training/logo-green-2x.png">
      </picture>

   </div>
   <div class="row">
      <div class="col-1-of-2">
         <div class="footer__navigation">
            <ul class="footer__list">
               <li class="footer__list-item">
                  <a href="#" class="footer__link">Company</a>
               </li>
               <li class="footer__list-item">
                  <a href="#" class="footer__link">Contact us</a>
               </li>
               <li class="footer__list-item">
                  <a href="#" class="footer__link">Carrers</a>
               </li>
               <li class="footer__list-item">
                  <a href="#" class="footer__link">Privacy policy</a>
               </li>
               <li class="footer__list-item">
                  <a href="#" class="footer__link">Terms</a>
               </li>
            </ul>
         </div>
      </div>
      <div class="col-1-of-2">
         <p class="footer__copyright">
            Build by <a href="#" class="footer__link">K'nGELL'</a> for online course <a href="#"
               class="footer__link">Advanced css and sass</a> Copyright &copy; by K'nGELL Design. You are 100% allowed
            to use thise webpages for personnal or commercial use, but not to claim it as your own design. A credit to
            the original author is of course highly appreciated!
         </p>
      </div>
   </div>
</footer>
<div class="modal" id="modal">
   <div class="modal__content">
      <div class="modal__content-left">
         <img src="../../../../assets/img/training/nat-8.jpg" alt="Tour photo" class="modal__content-img">
         <img src="../../../../assets/img/training/nat-9.jpg" alt="Tour photo" class="modal__content-img">
      </div>
      <div class="modal__content-right">
         <a href="#tours-section" class="modal__close">&times;</a>
         <h2 class="heading-2 u-margin-bottom-small">Start booking now</h2>
         <h3 class="heading-3 u-margin-bottom-small">Important &ndash; Please read this terms before booking</h3>
         <p class="modal__content-text">
            Curabitur nunc magna, posuere eget, venenatis eu, vehicula ac, velit. Aenean ornare, massa a accumsan
            pulvinar, quam lorem laoreet purus, eu sodales magna risus molestie lorem. Nunc erat velit, hendrerit quis,
            malesuada ut, aliquam vitae, wisi. Sed posuere. Suspendisse ipsum arcu, scelerisque nec, aliquam eu,
            molestie tincidunt, justo. Phasellus iaculis. Sed posuere lorem non ipsum. Pellentesque dapibus. Suspendisse
            quam libero, laoreet a, tincidunt eget, consequat at, est. Nullam ut lectus non enim consequat facilisis.
            Mauris leo. Quisque pede ligula, auctor vel, pellentesque vel, posuere id, turpis. Cras ipsum sem, cursus
            et, facilisis ut, tempus euismod, quam. Suspendisse tristique dolor eu orci.
         </p>
         <a href="#" class="btn btn-green">Book now</a>
      </div>
   </div>
</div>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/frontend/training/sass2/oldlayout/main') ?>

<?php $this->end();