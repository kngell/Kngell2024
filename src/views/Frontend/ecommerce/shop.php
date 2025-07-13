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
                    <a href="#" class="breadcrumbs-list__item--link active">Smartphones</a>
                </li>
            </ul>

        </div>
    </nav>
    <section class="shop-section">
        <div class="container shop-content">
            <div class="shop-content__filter">
                <div class="shop-content__filter--price">
                    <div class="product-dropdown price__dropdown">
                        <h5 class="product-dropdown--heading">Price</h5>
                        <div class="product-dropdown--icon-container">
                            <svg class="icon icon-arrow-up">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-up"></use>
                            </svg>
                        </div>
                    </div>
                    <div class="filter">
                        <div class="filter--from-to">
                            <div class="text">
                                <p class="text__filter">From</p>
                                <p class="text__filter">To</p>
                            </div>
                            <div class="info">
                                <div class="info__filter"><span>1299</span></div>
                                <div class="info__line"></div>
                                <div class="info__filter"><span>1299</span></div>
                            </div>

                        </div>
                        <div class="filter--scroll">
                            <div class="barre"></div>
                            <svg class="icon elipse1" aria-label="Elipse" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-elipse-scroll-start"></use>
                            </svg>
                            <div class="progress-bar"></div>
                            <svg class="icon elipse2" aria-label="Elipse" role="img">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-elipse-scroll-end"></use>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="shop-filter shop-content__filter--brand">
                    <div class="product-dropdown">
                        <h5 class="product-dropdown--heading">Brand</h5>
                        <div class="product-dropdown--icon-container">
                            <svg class="icon icon-arrow-up">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-up"></use>
                            </svg>
                        </div>
                    </div>

                    <div class="content">
                        <div class="content__search-items">
                            <form class="search">
                                <button class="search__icon-box" type="submit">
                                    <svg class="icon" aria-label="Search" role="img">
                                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-search"></use>
                                    </svg>
                                </button>

                                <input type="search" name="search-brand" id="search-brand" class="search__input"
                                    placeholder="Search...">
                            </form>
                            <form class="items">
                                <div class="items__box">
                                    <input type="checkbox" name="items" id="items1" class="items__box--input">
                                    <label for="items1">
                                        <p> Apple&nbsp;<span>110</span></p>
                                    </label>
                                </div>
                                <div class="items__box">
                                    <input type="checkbox" name="items" id="items2" class="items__box--input">
                                    <label for="items2">
                                        <p>
                                            Samsung&nbsp;<span>126</span>
                                        </p>
                                    </label>
                                </div>
                                <div class="items__box">
                                    <input type="checkbox" name="items" id="items3" class="items__box--input">
                                    <label for="items3">
                                        <p>Xaomi&nbsp;<span>68</span></p>
                                    </label>
                                </div>
                                <div class="items__box">
                                    <input type="checkbox" name="items" id="items4" class="items__box--input">
                                    <label for="items4">
                                        <p>Poco&nbsp;<span>44</span></p>
                                    </label>
                                </div>
                                <div class="items__box">
                                    <input type="checkbox" name="items" id="items5" class="items__box--input">
                                    <label for="items5">
                                        <p>OPPO&nbsp;<span>36</span></p>
                                    </label>
                                </div>
                                <div class="items__box">
                                    <input type="checkbox" name="items" id="items6" class="items__box--input">
                                    <label for="items6">
                                        <p>Honor&nbsp;<span>10</span></p>
                                    </label>
                                </div>
                                <div class="items__box">
                                    <input type="checkbox" name="items" id="items7" class="items__box--input">
                                    <label for="items7">
                                        <p>Motorola&nbsp;<span>34</span></p>
                                    </label>
                                </div>
                                <div class="items__box">
                                    <input type="checkbox" name="items" id="items8" class="items__box--input">
                                    <label for="items8">
                                        <p>Nokia&nbsp;<span>22</span></p>
                                    </label>
                                </div>
                                <div class="items__box">
                                    <input type="checkbox" name="items" id="items9" class="items__box--input">
                                    <label for="items9">
                                        <p>RealMe&nbsp;<span>35</span></p>
                                    </label>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
                <div class="shop-filter shop-content__filter--built-in-memory">
                    <div class="product-dropdown">
                        <h5 class="product-dropdown--heading">Built-in memory</h5>
                        <div class="product-dropdown--icon-container">
                            <svg class="icon icon-arrow-up">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-up"></use>
                            </svg>
                        </div>
                    </div>
                    <div class="content">
                        <div class="content__search-items">
                            <form class="search">
                                <button class="search__icon-box" type="submit">
                                    <svg class="icon" aria-label="Search" role="img">
                                        <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-search"></use>
                                    </svg>
                                </button>

                                <input type="search" name="search-brand" id="search-brand" class="search__input"
                                    placeholder="Search...">
                            </form>
                            <form class="items">
                                <div class="items__box">
                                    <input type="checkbox" name="mem" id="mem1" class="items__box--input">
                                    <label for="mem1">
                                        <p> 16GB&nbsp;<span>65</span></p>
                                    </label>
                                </div>
                                <div class="items__box">
                                    <input type="checkbox" name="mem" id="mem2" class="items__box--input">
                                    <label for="mem2">
                                        <p>
                                            32GB&nbsp;<span>123</span>
                                        </p>
                                    </label>
                                </div>
                                <div class="items__box">
                                    <input type="checkbox" name="mem" id="mem3" class="items__box--input">
                                    <label for="mem3">
                                        <p>64GB&nbsp;<span>48</span></p>
                                    </label>
                                </div>
                                <div class="items__box">
                                    <input type="checkbox" name="mem" id="mem4" class="items__box--input">
                                    <label for="mem4">
                                        <p>128GB&nbsp;<span>50</span></p>
                                    </label>
                                </div>
                                <div class="items__box">
                                    <input type="checkbox" name="mem" id="mem5" class="items__box--input">
                                    <label for="mem5">
                                        <p>256GB&nbsp;<span>24</span></p>
                                    </label>
                                </div>
                                <div class="items__box">
                                    <input type="checkbox" name="mem" id="mem6" class="items__box--input">
                                    <label for="mem6">
                                        <p>512GB&nbsp;<span>8</span></p>
                                    </label>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
                <div class="shop-filter shop-content__filter--dropdowns">
                    <div class="product-dropdown">
                        <h5 class="product-dropdown--heading">Protection class</h5>
                        <div class="product-dropdown--icon-container">
                            <svg class="icon icon-arrow-up">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down"></use>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="shop-filter shop-content__filter--dropdowns">
                    <div class="product-dropdown">
                        <h5 class="product-dropdown--heading">Screen diagonal</h5>
                        <div class="product-dropdown--icon-container">
                            <svg class="icon icon-arrow-up">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down"></use>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="shop-filter shop-content__filter--dropdowns">
                    <div class="product-dropdown">
                        <h5 class="product-dropdown--heading">Screen type</h5>
                        <div class="product-dropdown--icon-container">
                            <svg class="icon icon-arrow-up">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down"></use>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="shop-filter shop-content__filter--dropdowns">
                    <div class="product-dropdown">
                        <h5 class="product-dropdown--heading">Battery capacity</h5>
                        <div class="product-dropdown--icon-container">
                            <svg class="icon icon-arrow-up">
                                <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-down"></use>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="products shop-content__products">
                <div class="products__content">
                    <div class="products__content--header">
                        <h5 class="heading-left">
                            <span class="heading-left__text">Selected Products:</span> <span
                                class="heading-left__number">85</span>
                        </h5>
                        <div class="product-dropdown">
                            <h5 class="product-dropdown--heading">By rating</h5>
                            <div class="product-dropdown--icon-container">
                                <svg class="icon icon-arrow-up">
                                    <use href="<?= $this->asset('img/icons-sprite.svg') ?>#icon-arrow-up"></use>
                                </svg>
                            </div>
                        </div>

                    </div>
                    <div class="products__body">
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
                                <img src="../../../assets/img/ecommerce/Iphone 14 pro 1.png" alt="Iphone 14 Pro"
                                    class="image">
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
                                <img src="../../../assets/img/ecommerce/Iphone 14 pro 1.png" alt="Iphone 14 Pro"
                                    class="image">
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
                                <img src="../../../assets/img/ecommerce/Iphone 14 pro 1.png" alt="Iphone 14 Pro"
                                    class="image">
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
                                <img src="../../../assets/img/ecommerce/Iphone 14 pro 1.png" alt="Iphone 14 Pro"
                                    class="image">
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
                                <img src="../../../assets/img/ecommerce/Iphone 14 pro 1.png" alt="Iphone 14 Pro"
                                    class="image">
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
                                <img src="../../../assets/img/ecommerce/Iphone 14 pro 1.png" alt="Iphone 14 Pro"
                                    class="image">
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
                                <img src="../../../assets/img/ecommerce/Iphone 14 pro 1.png" alt="Iphone 14 Pro"
                                    class="image">
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
                                <img src="../../../assets/img/ecommerce/Iphone 14 pro 1.png" alt="Iphone 14 Pro"
                                    class="image">
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
                                <img src="../../../assets/img/ecommerce/Iphone 14 pro 1.png" alt="Iphone 14 Pro"
                                    class="image">
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
                                <img src="../../../assets/img/ecommerce/Iphone 14 pro 1.png" alt="Iphone 14 Pro"
                                    class="image">
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
                                <img src="../../../assets/img/ecommerce/Iphone 14 pro 1.png" alt="Iphone 14 Pro"
                                    class="image">
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
                                <img src="../../../assets/img/ecommerce/Iphone 14 pro 1.png" alt="Iphone 14 Pro"
                                    class="image">
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

                <div class="pagination"></div>
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