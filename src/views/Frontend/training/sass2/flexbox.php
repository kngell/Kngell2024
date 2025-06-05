<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('css/training/sass2/flexbox/main') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<!-- Content -->
<div class="container">
   <header class="header">
      <img src="../../../../assets/img/training/trillo/logotrillo.png" alt="Logo" class="logo">
      <form action="#" class="search-box">
         <input type="text" class="search-box__input" placeholder="search hotels">
         <button class="search-box__button">
            <svg class="search-box__button-icon">
               <use xlink:href="../../../../assets/img/training/trillo/sprite.svg#icon-magnifying-glass"></use>
            </svg>
         </button>
      </form>
      <nav class="user-nav">
         <div class="user-nav__box">
            <svg class="user-nav__box-icon">
               <use xlink:href="../../../../assets/img/training/trillo/sprite.svg#icon-bookmark"></use>
            </svg>
            <span class="user-nav__box-notification">7</span>
         </div>
         <div class="user-nav__box">
            <svg class="user-nav__box-icon">
               <use xlink:href="../../../../assets/img/training/trillo/sprite.svg#icon-chat"></use>
            </svg>
            <span class="user-nav__box-notification">13</span>
         </div>
         <div class="user-nav__user">
            <img src="../../../../assets/img/admin/user.jpg" alt="User Photo" class="user-nav__user-photo">
            <span class="user-nav__user-name">Assomo Jehu</span>
         </div>
      </nav>
   </header>
   <div class="content">
      <nav class="content__sidebar">
         <ul class="side-nav">
            <li class="side-nav__item side-nav__item--active">
               <a href="#" class="side-nav__link">
                  <svg class="side-nav__link-icon">
                     <use xlink:href="../../../../assets/img/training/trillo/sprite.svg#icon-home">
                     </use>
                  </svg>
                  <span class="side-nav__link-text">Hotel</span>
               </a>
            </li>

            <li class="side-nav__item">
               <a href="#" class="side-nav__link">
                  <svg class="side-nav__link-icon">
                     <use xlink:href="../../../../assets/img/training/trillo/sprite.svg#icon-aircraft-take-off">
                     </use>
                  </svg>
                  <span class="side-nav__link-text">Flight</span>
               </a>
            </li>

            <li class="side-nav__item">
               <a href="#" class="side-nav__link">
                  <svg class="side-nav__link-icon">
                     <use xlink:href="../../../../assets/img/training/trillo/sprite.svg#icon-key">
                     </use>
                  </svg>
                  <span class="side-nav__link-text">Car rental</span>
               </a>
            </li>

            <li class="side-nav__item">
               <a href="#" class="side-nav__link">
                  <svg class="side-nav__link-icon">
                     <use xlink:href="../../../../assets/img/training/trillo/sprite.svg#icon-map">
                     </use>
                  </svg>
                  <span class="side-nav__link-text">Tours</span>
               </a>
            </li>
         </ul>
         <div class="legal">
            &copy; 2025 by k&apos;nGELL Enterprise. All rights reserved
         </div>
      </nav>
      <main class="content__main">
         <div class="gallery">
            <fugure class="gallery__item">
               <img src="../../../../assets/img/training/trillo/hotel-1.jpg" alt="Photo of hotel 1"
                  class="gallery__item-photo">
            </fugure>
            <fugure class="gallery__item">
               <img src="../../../../assets/img/training/trillo/hotel-2.jpg" alt="Photo of hotel 2"
                  class="gallery__item-photo">
            </fugure>
            <fugure class="gallery__item">
               <img src="../../../../assets/img/training/trillo/hotel-3.jpg" alt="Photo of hotel 3"
                  class="gallery__item-photo">
            </fugure>
         </div>
         <div class="overview">
            <h1 class="overview__heading-1">
               Hotel Las palmas
            </h1>
            <div class="overview__stars">
               <svg class="overview__stars-icon">
                  <use xlink:href="../../../../assets/img/training/trillo/sprite.svg#icon-star">
                  </use>
               </svg>
               <svg class="overview__stars-icon">
                  <use xlink:href="../../../../assets/img/training/trillo/sprite.svg#icon-star">
                  </use>
               </svg>
               <svg class="overview__stars-icon">
                  <use xlink:href="../../../../assets/img/training/trillo/sprite.svg#icon-star">
                  </use>
               </svg>
               <svg class="overview__stars-icon">
                  <use xlink:href="../../../../assets/img/training/trillo/sprite.svg#icon-star">
                  </use>
               </svg>
               <svg class="overview__stars-icon">
                  <use xlink:href="../../../../assets/img/training/trillo/sprite.svg#icon-star">
                  </use>
               </svg>
            </div>
            <div class="overview__location">
               <svg class="overview__location-icon">
                  <use xlink:href="../../../../assets/img/training/trillo/sprite.svg#icon-location-pin">
                  </use>
               </svg>
               <button class="btn-inline">Albufeira, Portugal</button>
            </div>
            <div class="overview__rating">
               <p class="overview__rating-average">8.6</p>
               <p class="overviw__ratin-count">429 votes</p>
            </div>
         </div>
      </main>
   </div>
</div>

<!-- Fin Content -->
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('js/frontend/training/sass2/flexbox/main') ?>

<?php $this->end();