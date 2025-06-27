<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site" class="main">
    <!-- Content -->
    <section class="hero-section">
        <div class="hero-section__content">
            <div class="hero-section__content-text">
                <div class="hero-section__content-text--titles">
                    <p class="title-intro">Pro.beyond.</p>
                    <h1 class="title-main animate-fade-in-up animate-delay-200">Iphone 14&nbsp;<span
                            class="title-sub">Pro
                            </span">
                    </h1>
                </div>
                <p class="hero-section__content-text--body animate-fade-in-up animate-delay-300">Created to change
                    everything for
                    the
                    better. for everyone</p>
            </div>
            <button class="hero-section__content-cta btn btn-outline btn-outline-white">Shop Now</button>
        </div>
        <div class="hero-section__img-container animate-fade-in-right animate-delay-200">
            <img src="../../../assets/img/ecommerce/IphonePro.jpg" alt="Iphone Pro" class="image">
        </div>
    </section>
    <section class="small-banner-section">
        <div class="banner">
            <div class="banner-left">
                <div class="banner-left__wide">
                    <div class="image-container">
                        <img src="../../../assets/img/ecommerce/PlayStation.png" alt="Play Station"
                            class="image-container--img">
                    </div>
                    <div class="text-container">
                        <h2 class="text-container__heading">Playstation 5</h2>
                        <p class="text-container__body">Incredibly powerful CPUs, GPUs, and an SSD with integrated I/O
                            will
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
                            <p class="banner-square__text-container--body">Computational audio. Listen, it's powerful
                            </p>
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
                            <p class="banner-square__text-container--body">An immersive way to experience entertainment
                            </p>
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
                        <p class="text-container__body">The new 15‑inch MacBook Air makes room for more of what you love
                            with
                            a spacious Liquid Retina
                            display.</p>
                    </div>
                    <button class="btn btn-outline btn-outline-dark">Shop now</button>
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
        <div class="container products">
            <nav class="products-tags">
                <a href="#" class="products-tags__item selected">New Arrival</a>
                <a href="#" class="products-tags__item">Bestseller</a>
                <a href="#" class="products-tags__item">Featured Products</a>
            </nav>
            <div class="products-grid">
                <div class="product-card">
                    <div class="product-card__top">
                        <span class="product-card__top--like">
                            <!-- Like Icon SVG -->
                            <svg class="icon">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-like"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="product-card__image-container">
                        <img src="../../../assets/img/ecommerce/Iphone 14 pro.png" alt="Iphone 14 Pro" class="image">
                    </div>
                    <div class="product-card__info">
                        <div class="product-card__info--text">
                            <p class="description">
                                Apple iPhone 14 Pro Max 128GB Deep Purple (MQ9T3RX/A)
                            </p>
                            <h5 class="price">$900</h5>
                        </div>
                        <button class="btn btn-dark-small">Buy Now</button>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-card__top">
                        <span class="product-card__top--like">
                            <!-- Like Icon SVG -->
                            <svg class="icon" aria-label="Like" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-like"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="product-card__image-container">
                        <img src="../../../assets/img/ecommerce/camera.png" alt="Camera" class="image">
                    </div>
                    <div class="product-card__info">
                        <div class="product-card__info--text">
                            <p class="description">
                                Blackmagic Pocket Cinema Camera 6k
                            </p>
                            <h5 class="price">$2535</h5>
                        </div>
                        <button class="btn btn-dark-small">Buy Now</button>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-card__top">
                        <span class="product-card__top--like">
                            <!-- Like Icon SVG -->
                            <svg class="icon" aria-label="Like" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-like"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="product-card__image-container">
                        <img src="../../../assets/img/ecommerce/watch.png" alt="Watch" class="image">
                    </div>
                    <div class="product-card__info">
                        <div class="product-card__info--text">
                            <p class="description">
                                Apple Watch Series 9 GPS 41mm Starlight Aluminium Case
                            </p>
                            <h5 class="price">$339</h5>
                        </div>
                        <button class="btn btn-dark-small">Buy Now</button>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-card__top">
                        <span class="product-card__top--like">
                            <!-- Like Icon SVG -->
                            <svg class="icon" aria-label="Like" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-like"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="product-card__image-container">
                        <img src="../../../assets/img/ecommerce/headphone.png" alt="Headphone" class="image">
                    </div>
                    <div class="product-card__info">
                        <div class="product-card__info--text">
                            <p class="description">
                                AirPods Max Silver
                            </p>
                            <h5 class="price">$549</h5>
                        </div>
                        <button class="btn btn-dark-small">Buy Now</button>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-card__top">
                        <span class="product-card__top--like">
                            <!-- Like Icon SVG -->
                            <svg class="icon" aria-label="Like" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-like"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="product-card__image-container">
                        <img src="../../../assets/img/ecommerce/watch6-classic.png" alt="Samsung Watch" class="image">
                    </div>
                    <div class="product-card__info">
                        <div class="product-card__info--text">
                            <p class="description">
                                Samsung Galaxy Watch6 Classic 47mm Black
                            </p>
                            <h5 class="price">$369</h5>
                        </div>
                        <button class="btn btn-dark-small">Buy Now</button>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-card__top">
                        <span class="product-card__top--like">
                            <!-- Like Icon SVG -->
                            <svg class="icon" aria-label="Like" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-like"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="product-card__image-container">
                        <img src="../../../assets/img/ecommerce/galaxy-z.png" alt="Galaxy Z" class="image">
                    </div>
                    <div class="product-card__info">
                        <div class="product-card__info--text">
                            <p class="description">
                                Galaxy Z Fold5 Unlocked | 256GB | Phantom Black
                            </p>
                            <h5 class="price">$1799</h5>
                        </div>
                        <button class="btn btn-dark-small">Buy Now</button>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-card__top">
                        <span class="product-card__top--like">
                            <!-- Like Icon SVG -->
                            <svg class="icon" aria-label="Like" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-like"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="product-card__image-container">
                        <img src="../../../assets/img/ecommerce/galaxy-fe.png" alt="Galaxy FE" class="image">
                    </div>
                    <div class="product-card__info">
                        <div class="product-card__info--text">
                            <p class="description">
                                Galaxy Buds FE Graphite
                            </p>
                            <h5 class="price">$99.99</h5>
                        </div>
                        <button class="btn btn-dark-small">Buy Now</button>
                    </div>
                </div>
                <div class="product-card">
                    <div class="product-card__top">
                        <span class="product-card__top--like">
                            <!-- Like Icon SVG -->
                            <svg class="icon" aria-label="Like" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-like"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="product-card__image-container">
                        <img src="../../../assets/img/ecommerce/ipad9.png" alt="IPad" class="image">
                    </div>
                    <div class="product-card__info">
                        <div class="product-card__info--text">
                            <p class="description">
                                Apple iPad 9 10.2" 64GB Wi-Fi Silver (MK2L3) 2021
                            </p>
                            <h5 class="price">$398</h5>
                        </div>
                        <button class="btn btn-dark-small">Buy Now</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="big-banner-section">
        <div class="big-card">
            <div class="big-card__content">
                <h4 class="big-card__content--title">Popular Products</h4>
                <p class="big-card__content--description">iPad combines a magnificent 10.2-inch Retina display,
                    incredible performance, multitasking and ease of use.</p>
                <button class="btn btn--outline btn-outline-dark">Shop Now</button>
            </div>
            <div class="big-card__img-container">
                <div class="img-multiples">
                    <div class="img-multiples__left">
                        <img src="../../../assets/img/ecommerce/image 39.png" alt="Image 39" class="image">
                    </div>
                    <div class="img-multiples__right">
                        <img src="../../../assets/img/ecommerce/image 12.png" alt="Image 12" class="image">
                    </div>
                </div>

            </div>
        </div>
    </section>
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();