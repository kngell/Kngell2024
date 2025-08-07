<?php declare(strict_types=1);
$this->start('head'); ?>
<!-------Costum-------->
<?= $this->css('path') ?>
<?php $this->end(); ?>
<?php $this->start('body'); ?>
<main class="main" id="main">
    <!-- Content -->
    <nav class="breadcrumbs-section">
        <div class="container breadcrumbs">
            <ul class="breadcrumbs-list">
                <li class="breadcrumbs-list__item">
                    <a href="#" class="breadcrumbs-list__item--link">Home</a>
                </li>
                <li class="breadcrumbs-list__item">
                    <a href="#" class="breadcrumbs-list__item--link">Catalog</a>
                </li>
                <li class="breadcrumbs-list__item">
                    <a href="#" class="breadcrumbs-list__item--link">Smarphone</a>
                </li>
                <li class="breadcrumbs-list__item">
                    <a href="#" class="breadcrumbs-list__item--link active">Iphone Pro 14 Max</a>
                </li>
            </ul>

        </div>
    </nav>
    <section class="section single-product-section">
        <div class="container single-product">
            <div class="single-product__img-container">
                <div class="small-img-group">
                    <div class="small-img-group__container active">
                        <img src="../../../assets/img/ecommerce/product-img.png" alt="" class="image">
                    </div>
                    <div class="small-img-group__container">
                        <img src="../../../assets/img/ecommerce/product-img.png" alt="" class="image">
                    </div>
                    <div class="small-img-group__container">
                        <img src="../../../assets/img/ecommerce/product-img.png" alt="" class="image">
                    </div>
                    <div class="small-img-group__container">
                        <img src="../../../assets/img/ecommerce/product-img.png" alt="" class="image">
                    </div>

                </div>
                <div class="big-img-container">
                    <img src="../../../assets/img/ecommerce/product-img.png" alt="" class="image">
                </div>
            </div>
            <div class="single-product__info">
                <div class="content">
                    <div class="content__header">
                        <h2 class="content__header--heading">Apple iPhone 14 Pro Max</h2>
                        <div class="content__header--price">
                            <h5 class="current-price">$1399</h5>
                            <span class="initial-price">$1499</span>
                        </div>
                    </div>
                    <div class="content__body">
                        <div class="content__body--colors">
                            <p class="colors-title">Select Color:</p>
                            <div class="colors-value">
                                <svg class="icon color-black" aria-label="Elipse" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-elipse-color"></use>
                                </svg>
                                <svg class="icon color-purple" aria-label="Elipse" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-elipse-color"></use>
                                </svg>
                                <svg class="icon color-red" aria-label="Elipse" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-elipse-color"></use>
                                </svg>
                                <svg class="icon color-yellow" aria-label="Elipse" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-elipse-color"></use>
                                </svg>
                                <svg class="icon color-gray" aria-label="Elipse" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-elipse-color"></use>
                                </svg>
                            </div>
                        </div>
                        <div class="content__body--tabs-memory">
                            <div class="tab">
                                <p class="tab__text disabled">128GB</p>
                            </div>
                            <div class="tab">
                                <p class="tab__text">256GB</p>
                            </div>
                            <div class="tab">
                                <p class="tab__text">512GB</p>
                            </div>
                            <div class="tab">
                                <p class="tab__text active">1TB</p>
                            </div>
                        </div>
                        <div class="content__body--details">
                            <div class="details">
                                <svg class="icon details__icon" aria-label="Elipse" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-screen-size"></use>
                                </svg>
                                <div class="details__text">
                                    <h6 class="details__text--title">Screen size</h6>
                                    <p class="details__text--desc">6.7"</p>
                                </div>
                            </div>
                            <div class="details">
                                <svg class="icon details__icon" aria-label="Elipse" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cpu"></use>
                                </svg>
                                <div class="details__text">
                                    <h6 class="details__text--title">CPU</h6>
                                    <p class="details__text--desc">Apple A16 Bionic</p>
                                </div>
                            </div>
                            <div class="details">
                                <svg class="icon details__icon" aria-label="Elipse" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cores"></use>
                                </svg>
                                <div class="details__text">
                                    <h6 class="details__text--title">Number of cores</h6>
                                    <p class="details__text--desc">6</p>
                                </div>
                            </div>
                            <div class="details">
                                <svg class="icon details__icon" aria-label="Elipse" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cameras"></use>
                                </svg>
                                <div class="details__text">
                                    <h6 class="details__text--title">Main Camera</h6>
                                    <p class="details__text--desc">48-12 -12 MP</p>
                                </div>
                            </div>
                            <div class="details">
                                <svg class="icon details__icon" aria-label="Elipse" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-cameras"></use>
                                </svg>
                                <div class="details__text">
                                    <h6 class="details__text--title">Front Camera</h6>
                                    <p class="details__text--desc">12 MP</p>
                                </div>
                            </div>
                            <div class="details">
                                <svg class="icon details__icon" aria-label="Elipse" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-battery"></use>
                                </svg>
                                <div class="details__text">
                                    <h6 class="details__text--title">Battery capacity</h6>
                                    <p class="details__text--desc">4323 mAh</p>
                                </div>
                            </div>
                        </div>
                        <p class="content__body--description">Enhanced capabilities thanks toan enlarged display of 6.7
                            inchesand work without
                            rechargingthroughout the day. Incredible photosas in weak, yesand in bright lightusing
                            the
                            new systemwith two cameras <span>more...</span></p>

                    </div>
                </div>
                <div class="buttons">
                    <button class="btn btn-outline btn-outline-dark">Add to Wishlist</button>
                    <button class="btn btn-dark">Add to Car</button>
                </div>
                <div class="icons">
                    <div class="icons__feature">
                        <div class="icons__feature--icon-container">
                            <svg class="icon truck" aria-label="Truck" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-truck"></use>
                            </svg>
                        </div>
                        <div class="icons__feature--text">
                            <h6 class="title">Free delivery</h6>
                            <p class="desc">1-2 day</p>
                        </div>

                    </div>
                    <div class="icons__feature">
                        <div class="icons__feature--icon-container">
                            <svg class="icon feature2" aria-label="Truck" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-shop"></use>
                            </svg>
                        </div>
                        <div class="icons__feature--text">
                            <h6 class="title">In stock</h6>
                            <p class="text">Today</p>
                        </div>

                    </div>
                    <div class="icons__feature">
                        <div class="icons__feature--icon-container">
                            <svg class="icon feature1" aria-label="Truck" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-clock"></use>
                            </svg>
                        </div>
                        <div class="icons__feature--text">
                            <h6 class="title">Guaranteed</h6>
                            <p class="text">1 year</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="section details-section">
        <div class="container details-container">
            <div class="content">
                <h4 class="content__heading">Details</h4>
                <p class="content__desc">Just as a book is judged by its cover, the first thing you notice when you pick
                    up
                    a modern smartphone is the display. Nothing surprising, because advanced technologies allow you to
                    practically level the display frames and cutouts for the front camera and speaker, leaving no room
                    for
                    bold design solutions. And how good that in such realities Apple everything is fine with displays.
                    Both
                    critics and mass consumers always praise the quality of the picture provided by the products of the
                    Californian brand. And last year's 6.7-inch Retina panels, which had ProMotion, caused real
                    admiration
                    for many.</p>
                <div class="content__details">
                    <div class="content__details--info">
                        <h5 class="heading">Screen</h5>
                        <div class="features">
                            <div class="features__details">
                                <p class="features__details--parameter">Screen diagonal</p>
                                <p class="features__details--value">6.7"</p>
                            </div>
                            <div class="features__details">
                                <p class="features__details--parameter">The screen resolution</p>
                                <p class="features__details--value">2796x1290</p>
                            </div>
                            <div class="features__details">
                                <p class="features__details--parameter">The screen refresh rate</p>
                                <p class="features__details--value">120 Hz</p>
                            </div>
                            <div class="features__details">
                                <p class="features__details--parameter">Screen type</p>
                                <p class="features__details--value">OLED</p>
                            </div>
                            <div class="features__details">
                                <p class="features__details--parameter">The pixel density</p>
                                <p class="features__details--value">6.7"</p>
                            </div>
                            <div class="features__details">
                                <p class="features__details--parameter">Additionally</p>
                                <div class="features__details-group">
                                    <p class="features__details-group--value">Dynamic Island</p>
                                    <p class="features__details-group--value">Always-On display</p>
                                    <p class="features__details-group--value">HDR display</p>
                                    <p class="features__details-group--value">True Tone</p>
                                    <p class="features__details-group--value">Wide color (P3)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content__details--info">
                        <h5 class="heading">CPU</h5>
                        <div class="features">
                            <div class="features__details">
                                <p class="features__details--parameter">CPU</p>
                                <p class="features__details--value">A16 Bionic</p>
                            </div>
                            <div class="features__details">
                                <p class="features__details--parameter">Number of cores</p>
                                <p class="features__details--value">6</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content__buttons">
                    <button class="btn btn-small btn-outline btn-arrow-down-right">View More</button>
                </div>
            </div>
        </div>
    </section>
    <section class="section reviews-section">
        <div class="container reviews-container">
            <div class="reviews-header">
                <div class="reviews-header__title"><span>Reviews</span></div>
                <div class="reviews-header__overall-rating">
                    <div class="reviews-header__overall-rating--values">
                        <p class="total">4.8</p>
                        <p class="total-of">of 125 reviews</p>
                        <div class="stars">
                            <svg class="stars__icon star-full" aria-label="Star" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                            </svg>
                            <svg class="stars__icon star-full" aria-label="Star" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                            </svg>
                            <svg class="stars__icon star-full" aria-label="Star" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                            </svg>
                            <svg class="stars__icon star-full" aria-label="Star" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                            </svg>
                            <svg class="stars__icon star-half" aria-label="Star" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star-gradient"></use>
                            </svg>
                        </div>

                    </div>
                    <div class="reviews-header__overall-rating--schedule">
                        <div class="review-level">
                            <span class="review-level__text">Excellent</span>
                            <div class="review-level__bar">
                                <div class="bar1"></div>
                                <div class="bar2"></div>
                            </div>
                            <div class="review-level__value">
                                <span>100</span>
                            </div>
                        </div>
                        <div class="review-level">
                            <span class="review-level__text">Good</span>
                            <div class="review-level__bar">
                                <div class="bar1"></div>
                                <div class="bar2 good"></div>
                            </div>
                            <div class="review-level__value">
                                <span>11</span>
                            </div>
                        </div>
                        <div class="review-level">
                            <span class="review-level__text">Average</span>
                            <div class="review-level__bar">
                                <div class="bar1"></div>
                                <div class="bar2 average"></div>
                            </div>
                            <div class="review-level__value">
                                <span>3</span>
                            </div>
                        </div>
                        <div class="review-level">
                            <span class="review-level__text">Below Average</span>
                            <div class="review-level__bar">
                                <div class="bar1"></div>
                                <div class="bar2 below-average"></div>
                            </div>
                            <div class="review-level__value">
                                <span>8</span>
                            </div>
                        </div>
                        <div class="review-level">
                            <span class="review-level__text">Poor</span>
                            <div class="review-level__bar">
                                <div class="bar1"></div>
                                <div class="bar2 poor"></div>
                            </div>
                            <div class="review-level__value">
                                <span>1</span>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="reviews-header__comments">
                    <h6 class="reviews-header__comments--title">
                        Leave Comment
                    </h6>
                    <div class="reviews-header__comments--icon-container">

                    </div>
                </div>
            </div>
            <div class="reviews-body">
                <div class="review">
                    <div class="avatar-container">
                        <img src="../../../assets/img/ecommerce/Rectangle 6.png" alt="Avatar" class="image">
                    </div>
                    <div class="content">
                        <div class="content__headings">
                            <div class="content__headings--titles">
                                <h5 class="name">Grace Carey</h5>
                                <h5 class="date">24 January,2023</h5>
                            </div>
                            <div class="content__headings--stars">
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-half" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star-gradient">
                                    </use>
                                </svg>
                            </div>

                        </div>
                        <p class="content__text">
                            I was a bit nervous to be buying a secondhand phone from Amazon, but I couldn’t be
                            happier with my purchase!! I have a pre-paid data plan so I was worried that this phone
                            wouldn’t connect with my data plan, since the new phones don’t have the physical Sim
                            tray anymore, but couldn’t have been easier! I bought an Unlocked black iPhone 14 Pro
                            Max in excellent condition and everything is PERFECT. It was super easy to set up and
                            the phone works and looks great. It truly was in excellent condition. Highly
                            recommend!!!🖤
                        </p>
                        <div class="content__images-container"></div>
                    </div>
                </div>
                <div class="review">
                    <div class="avatar-container">
                        <img src="../../../assets/img/ecommerce/Rectangle 7.png" alt="Avatar" class="image">
                    </div>
                    <div class="content">
                        <div class="content__headings">
                            <div class="content__headings--titles">
                                <h5 class="name">Ronald Richards</h5>
                                <h5 class="date">24 January,2023</h5>
                            </div>
                            <div class="content__headings--stars">
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>

                            </div>
                        </div>
                        <p class="content__text">
                            This phone has 1T storage and is durable. Plus all the new iPhones have a C port! Apple
                            is phasing out the current ones! (All about the Benjamin’s) So if you want a phone
                            that’s going to last grab an iPhone 14 pro max and get several cords and plugs.
                        </p>
                        <div class="content__images-container"></div>
                    </div>
                </div>
                <div class="review">
                    <div class="avatar-container">
                        <img src="../../../assets/img/ecommerce/Rectangle 7.png" alt="Avatar" class="image">
                    </div>
                    <div class="content">
                        <div class="content__headings">
                            <div class="content__headings--titles">
                                <h5 class="name">Ronald Richards</h5>
                                <h5 class="date">24 January,2023</h5>
                            </div>
                            <div class="content__headings--stars">
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>

                            </div>
                        </div>
                        <p class="content__text">
                            This phone has 1T storage and is durable. Plus all the new iPhones have a C port! Apple
                            is phasing out the current ones! (All about the Benjamin’s) So if you want a phone
                            that’s going to last grab an iPhone 14 pro max and get several cords and plugs.
                        </p>
                        <div class="content__images">
                            <div class="img-container">
                                <img src="../../../assets/img/ecommerce/Review=Image1.png" alt="Review">
                            </div>
                            <div class="img-container">
                                <img src="../../../assets/img/ecommerce/Review=Image2.png" alt="Review">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="review">
                    <div class="avatar-container">
                        <img src="../../../assets/img/ecommerce/Rectangle 8.png" alt="Avatar" class="image">
                    </div>
                    <div class="content">
                        <div class="content__headings">
                            <div class="content__headings--titles">
                                <h5 class="name">Jhon Malcolm</h5>
                                <h5 class="date">24 January,2023</h5>
                            </div>
                            <div class="content__headings--stars">
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-full" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>
                                <svg class="content__headings--stars__icon star-circle" aria-label="Star" role="img">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-star"></use>
                                </svg>

                            </div>
                        </div>
                        <p class="content__text">
                            This phone has 1T storage and is durable. Plus all the new iPhones have a C port! Apple
                            is phasing out the current ones! (All about the Benjamin’s) So if you want a phone
                            that’s going to last grab an iPhone 14 pro max and get several cords and plugs.
                        </p>
                        <div class="content__images">
                            <div class="img-container">
                                <img src="../../../assets/img/ecommerce/Review=Image1.png" alt="Review">
                            </div>
                            <div class="img-container">
                                <img src="../../../assets/img/ecommerce/Review=Image2.png" alt="Review">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="reviews-footer">
                <button class="btn btn-small btn-outline btn-arrow-right">View more</button>
            </div>
        </div>
    </section>
    <section class="section related-products-section">
        <div class="container related-products-container">
            <h4 class="title">Related products</h4>
            <div class="related-products">
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
                        <img src="../../../assets/img/ecommerce/Iphone 14 pro.png" alt="Iphone 14 Pro" class="image">
                    </div>
                    <div class="product-card__info">
                        <div class="product-card__info--text">
                            <p class="description">
                                pple iPhone 14 Pro 512GB Gold (MQ233)
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
                            <svg class="icon" aria-label="Like" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-like"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="product-card__image-container">
                        <img src="../../../assets/img/ecommerce/image 64.png" alt="Iphone 14 Pro" class="image">
                    </div>
                    <div class="product-card__info">
                        <div class="product-card__info--text">
                            <p class="description">
                                pple iPhone 14 Pro 512GB Gold (MQ233)
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
                            <svg class="icon" aria-label="Like" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-like"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="product-card__image-container">
                        <img src="../../../assets/img/ecommerce/image 8.png" alt="Iphone 14 Pro" class="image">
                    </div>
                    <div class="product-card__info">
                        <div class="product-card__info--text">
                            <p class="description">
                                pple iPhone 14 Pro 512GB Gold (MQ233)
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
                            <svg class="icon" aria-label="Like" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-like"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="product-card__image-container">
                        <img src="../../../assets/img/ecommerce/image 12.png" alt="Iphone 14 Pro" class="image">
                    </div>
                    <div class="product-card__info">
                        <div class="product-card__info--text">
                            <p class="description">
                                pple iPhone 14 Pro 512GB Gold (MQ233)
                            </p>
                            <h5 class="price">$1437</h5>
                        </div>
                        <button class="btn btn-dark-small">Buy Now</button>
                    </div>
                </div>
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