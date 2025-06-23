<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <?= $this->getPageTitle()?>
   <link rel="shortcut icon" href="data:," />
   <!-- Main style -->
   <?= $this->css('path') ?? '' ?>
   <!-- css/librairies/librairy -->
   <!-- Plugins css -->
   <?= $this->css('css/plugins/homeplugins') ?? '' ?>
   <!-- Main style -->
   <?= $this->css('css/frontend/main/main') ?? '' ?>
</head>

<body class="page-body">
   <!-- Main Header -->
   <header class="header">
      <div class="header-top">
         <button class="header-top__mobile-toggle js-mobile-menu-toggle">
            <img src="<?= $this->asset('img/icons-sprite.svg') ?>#hamburger-menu.svg" alt="Mobile menu"
               class="header__mobile-toggle-img">
         </button>
         <div class="header-top__logo">
            <a href="#" class="header-top__logo-link">
               <svg class="logo">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-logo" alt="Logo" class="image"></use>
               </svg>
            </a>
         </div>
         <form class="header-top__search">
            <button type="submit" class="header-top__search--btn">
               <svg class="search">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-search" alt="search" class="search">
                  </use>
               </svg>
               <!-- <img src="<?= $this->asset('img/icons-sprite.svg') ?>#icon-search.svg" alt="Search"
                  class="header__search-icon"> -->
            </button>
            <input type="text" name="search" id="header-top__search--input" class="header-top__search--input"
               placeholder="Search...">
         </form>
         <nav class="header-top__nav">
            <ul class="header-top__nav-list">
               <li class="header-top__nav-list__item">
                  <a href="#" class="nav-link active">Home</a>
               </li>
               <li class="header-top__nav-list__item">
                  <a href="#" class="nav-link">About</a>
               </li>
               <li class="header-top__nav-list__item">
                  <a href="#" class="nav-link">Contact</a>
               </li>
               <li class="header-top__nav-list__item">
                  <a href="#" class="nav-link">Blog</a>
               </li>
            </ul>
         </nav>
         <div class="header-top__actions">
            <a href="#" class="header-top__actions-link header-top__actions--wishlist">
               <svg class="icon wishlist-icon">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-wishlist" alt="Wishlist">
                  </use>
               </svg>
            </a>
            <a href="#" class="header-top__actions-link header-top__actions--cart" data-count="0">
               <svg class="icon cart-icon">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cart">
                  </use>
                  <desc>User Cart</desc>
               </svg>
            </a>
            <a href="#" class="header-top__actions-link header-top__actions--user">
               <svg class="icon cart-icon">
                  <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-user">
                  </use>
                  <desc>User</desc>
               </svg>
            </a>
         </div>

      </div>
      <div class="header-bottom category-nav">
         <a href="#" class="category-nav__link">
            <svg class="category-nav__link-icon">
               <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-phone"></use>
            </svg>
            <span class="category-nav__link-text">Phone</span>
         </a>
         <a href="#" class="category-nav__link">
            <svg class="category-nav__link-icon">
               <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-computers"></use>
            </svg>
            <span class="category-nav__link-text">Computers</span>
         </a>
         <a href="#" class="category-nav__link">
            <svg class="category-nav__link-icon">
               <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-smart-watches"></use>
            </svg>
            <span class="category-nav__link-text">Smart Watches</span>
         </a>
         <a href="#" class="category-nav__link">
            <svg class="category-nav__link-icon">
               <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cameras"></use>
            </svg>
            <span class="category-nav__link-text">Cameras</span>
         </a>
         <a href="#" class="category-nav__link">
            <svg class="category-nav__link-icon">
               <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-headphones"></use>
            </svg>
            <span class="category-nav__link-text">Headphones</span>
         </a>
         <a href="#" class="category-nav__link">
            <svg class="category-nav__link-icon">
               <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-gaming"></use>
            </svg>
            <span class="category-nav__link-text">Gaming</span>
         </a>
      </div>
   </header>

   <!-- Mobile Menu (Off-canvas) -->
   <!-- <div class="mobile-menu">
      <button class="mobile-menu__close js-mobile-menu-close">
         <img src="<?= $this->asset('img/icons/close.svg') ?>" alt="Close menu" class="mobile-menu__close-icon">
      </button>
      <nav class="mobile-menu__nav">
         <ul class="mobile-menu__list">
            <li class="mobile-menu__item">
               <a href="#" class="mobile-menu__link">Home</a>
            </li>
            <li class="mobile-menu__item">
               <a href="#" class="mobile-menu__link">About</a>
            </li>
            <li class="mobile-menu__item">
               <a href="#" class="mobile-menu__link">Contact</a>
            </li>
            <li class="mobile-menu__item">
               <a href="#" class="mobile-menu__link">Blog</a>
            </li>
         </ul>
      </nav>
      <div class="mobile-menu__categories">
         <h3 class="mobile-menu__heading">Categories</h3>
         <ul class="mobile-menu__list">
            <li class="mobile-menu__item">
               <a href="#" class="mobile-menu__link">
                  <img src="<?= $this->asset('img/icons/phone.svg') ?>" alt="Phone" class="mobile-menu__icon">
                  Phone
               </a>
            </li>
            <li class="mobile-menu__item">
               <a href="#" class="mobile-menu__link">
                  <img src="<?= $this->asset('img/icons/computers.svg') ?>" alt="Computers" class="mobile-menu__icon">
                  Computers
               </a>
            </li>
            <li class="mobile-menu__item">
               <a href="#" class="mobile-menu__link">
                  <img src="<?= $this->asset('img/icons/smart-watches.svg') ?>" alt="Smart Watches"
                     class="mobile-menu__icon">
                  Smart Watches
               </a>
            </li>
            <li class="mobile-menu__item">
               <a href="#" class="mobile-menu__link">
                  <img src="<?= $this->asset('img/icons/cameras.svg') ?>" alt="Cameras" class="mobile-menu__icon">
                  Cameras
               </a>
            </li>
            <li class="mobile-menu__item">
               <a href="#" class="mobile-menu__link">
                  <img src="<?= $this->asset('img/icons/headphones.svg') ?>" alt="Headphones" class="mobile-menu__icon">
                  Headphones
               </a>
            </li>
            <li class="mobile-menu__item">
               <a href="#" class="mobile-menu__link">
                  <img src="<?= $this->asset('img/icons/gaming.svg') ?>" alt="Gaming" class="mobile-menu__icon">
                  Gaming
               </a>
            </li>
         </ul>
      </div>
   </div> -->