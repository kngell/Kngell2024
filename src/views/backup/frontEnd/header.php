<!-- Mobile toogle -->
<button class="menu__mobile-toggle js-mobile-menu-toggle">
    <svg class="logo" aria-label="Mobile menu" role="img">
        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#hamburger-menu.svg" class="header__mobile-toggle-img">
        </use>
    </svg>
</button>
<!-- Logo -->
<div class="menu__logo">
    <a href="/ecommerce" class="logo-container">
        <svg class="logo" aria-label="Logo" role="img">
            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-logo" alt="Logo" class="image">
            </use>
        </svg>
    </a>
</div>
<!-- Menu Search Form -->
<form class="menu__search">
    <button type="submit" class="menu__search--btn">
        <svg class="search">
            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-search" alt="search" class="search">
            </use>
        </svg>
    </button>
    <input type="text" name="search" id="menu__search--input" class="menu__search--input" placeholder="Search...">
</form>

<!-- Navigation Menu -->
<nav class="menu__nav">
    <ul class="menu__nav-list">
        <li class="menu__nav-list__item">
            <a href="/ecommerce/shop" class="nav-link active">Shop</a>
        </li>
        <li class="menu__nav-list__item">
            <a href="#" class="nav-link">About</a>
        </li>
        <li class="menu__nav-list__item">
            <a href="#" class="nav-link">Contact</a>
        </li>
        <li class="menu__nav-list__item">
            <a href="#" class="nav-link">Blog</a>
        </li>
    </ul>
</nav>

<!-- Actions Menu -->
<div class="menu__actions">
    <a href="#" class="menu__actions-link menu__actions--wishlist">
        <svg class="icon wishlist-icon">
            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-wishlist" alt="Wishlist">
            </use>
        </svg>
    </a>
    <a href="#" class="menu__actions-link menu__actions--cart" data-count="0">
        <svg class="icon cart-icon">
            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cart">
            </use>
            <desc>User Cart</desc>
        </svg>
    </a>
    <a href="#" class="menu__actions-link menu__actions--user">
        <svg class="icon cart-icon">
            <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-user">
            </use>
            <desc>User</desc>
        </svg>
    </a>
</div>

<!-- HeaderBottom -->
<div class="container category-nav">
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