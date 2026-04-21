<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main id="main-site" class="main">
    <!-- Content Hero section-->
    <section class="hero-section">
        <?= $heroSection ?? '' ?>
    </section>
    <section class="small-banner-section">
        <?= $smallBannerSection ?? '' ?>
    </section>
    <section class="category-section">
        <?= $categorySection ?? '' ?>
    </section>
    <section class="new-arrival-section">
        <?= $productSection ?? '' ?>
    </section>
    <section class="big-banner-section">
        <div class="big-card-grid">
            <div class="big-card big-card__bg-white big-card-multiple">
                <div class="big-card__content">
                    <h4 class="big-card__content--title">Popular Products</h4>
                    <p class="big-card__content--description">iPad combines a magnificent 10.2-inch Retina display,
                        incredible performance, multitasking and ease of use.</p>
                    <button class="btn btn-outline btn-outline-dark">Shop Now</button>
                </div>
                <div class="big-card__img-container big-card-multiple__img-container">
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
            <div class="big-card big-card__bg-gray-light">
                <div class="big-card__content">
                    <h4 class="big-card__content--title">Ipad Pro</h4>
                    <p class="big-card__content--description">iPad combines a magnificent 10.2-inch Retina display,
                        incredible performance, multitasking and ease of use.</p>
                    <button class="btn btn-outline btn-outline-dark">Shop Now</button>
                </div>
                <div class="big-card__img-container--ipad ">
                    <img src="../../../assets/img/ecommerce/image 64.png" alt="Image 39" class="image">
                </div>
            </div>
            <div class="big-card big-card__bg-gray-normal">
                <div class="big-card__content">
                    <h4 class="big-card__content--title">Samsung Galaxy</h4>
                    <p class="big-card__content--description">iPad combines a magnificent 10.2-inch Retina display,
                        incredible performance, multitasking and ease of use.</p>
                    <button class="btn btn-outline btn-outline-dark">Shop Now</button>
                </div>
                <div class="big-card__img-container--samsung">
                    <img src="../../../assets/img/ecommerce/image 41.png" alt="Image 41" class="image">
                </div>
            </div>
            <div class="big-card big-card__bg-gray-dark">
                <div class="big-card__content">
                    <h4 class="big-card__content--title">Macbook Pro</h4>
                    <p class="big-card__content--description">iPad combines a magnificent 10.2-inch Retina display,
                        incredible performance, multitasking and ease of use.</p>
                    <button class="btn btn-outline btn-outline-dark">Shop Now</button>
                </div>
                <div class="big-card__img-container--macbook">
                    <img src="../../../assets/img/ecommerce/Macbook 1.png" alt="Macbook 1" class="image">
                </div>
            </div>
        </div>
        </div>

    </section>
    <section class="discount-section">
        <div class="container discount">
            <h2 class="discount__title">Discounts up to -50%</h2>
            <div class="discount__row">
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
                        <img src="../../../assets/img/ecommerce/Iphone 14 pro 1.png" alt="Iphone 14 Pro" class="image">
                    </div>
                    <div class="product-card__info">
                        <div class="product-card__info--text">
                            <p class="description">
                                Apple iPhone 14 Pro 512GB Gold (MQ233)
                            </p>
                            <h5 class="price">$1437</h5>
                        </div>
                        <button class="btn btn-dark-small">Buy Now</button>
                    </div>
                </div>
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
                        <img src="../../../assets/img/ecommerce/headphone.png" alt="headphone" class="image">
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
                            <svg class="icon">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-like"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="product-card__image-container">
                        <img src="../../../assets/img/ecommerce/Iphone 14 pro 1-2.png" alt="Iphone 14 pro"
                            class="image">
                    </div>
                    <div class="product-card__info">
                        <div class="product-card__info--text">
                            <p class="description">
                                Apple Watch Series 9 GPS 41mm Starlight Aluminium Case
                            </p>
                            <h5 class="price">$399</h5>
                        </div>
                        <button class="btn btn-dark-small">Buy Now</button>
                    </div>
                </div>
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
                        <img src="../../../assets/img/ecommerce/Iphone 14 pro 1-3.png" alt="Iphone 14 pro"
                            class="image">
                    </div>
                    <div class="product-card__info">
                        <div class="product-card__info--text">
                            <p class="description">
                                Apple iPhone 14 Pro 1TB Gold (MQ2V3)
                            </p>
                            <h5 class="price">$1499</h5>
                        </div>
                        <button class="btn btn-dark-small">Buy Now</button>
                    </div>
                </div>
            </div>
        </div>


    </section>
    <section class="banner-section">
        <div class="banner-section__content">
            <div class="text">
                <h2 class="text__heading">Big Summer <span>Sale</span> </h2>
                <p class="text__description">Commodo fames vitae vitae leo mauris in. Eu consequat.</p>
            </div>
            <button class="btn btn-outline btn-outline-white">Shop Now</button>
        </div>
        <div class="banner-section__img-container1">
            <img src="../../../assets/img/ecommerce/image 6.png" alt="Image 6" class="image">
        </div>
        <div class="banner-section__img-container2">
            <img src="../../../assets/img/ecommerce/image 8.png" alt="Image 8" class="image">
        </div>
        <div class="banner-section__img-container3">
            <img src="../../../assets/img/ecommerce/jbl_jr_310bt_blue 1.png" alt="jbl_jr_310bt_blue 1" class="image">
        </div>
        <div class="banner-section__img-container4">
            <img src="../../../assets/img/ecommerce/image 7.png" alt="image 7" class="image">
        </div>
        <div class="banner-section__img-container5">
            <img src="../../../assets/img/ecommerce/image 18.png" alt="image 18" class="image">
        </div>
    </section>
</main>
<?php $this->end(); ?>
<?php $this->start('footer') ?>
<!----------custom--------->
<?= $this->js('path') ?>

<?php $this->end();