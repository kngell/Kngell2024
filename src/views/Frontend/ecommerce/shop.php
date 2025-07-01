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
            <ul class="beadcrumbs-list">
                <li class="breadcrumbs-list__item">
                    <a href="#" class="breadcrumbs-list__item--link">Home</a>
                </li>
                <li class="breadcrumbs-list__item">
                    <a href="#" class="breadcrumbs-list__item--link">Catalog</a>
                </li>
                <li class="breadcrumbs-list__item">
                    <a href="#" class="breadcrumbs-list__item--link">Smartphones</a>
                </li>
            </ul>

        </div>
    </nav>
    <section class="shop-section">
        <div class="container shop-content">
            <div class="shop-content__filter"></div>
            <div class="shop-content__products">
                <div class="products">
                    <div class="products__header"></div>
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
                                        Apple iPhone 14 Pro 512GB Gold (MQ233)
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
<script>
if (window.localStorage) {
    let count = parseInt(localStorage.getItem('reloadCount') || '0', 10) + 1;
    localStorage.setItem('reloadCount', count);
    console.log('[Reload Counter] This page has reloaded', count, 'times (since last clear).');
    // Optionally show on page:
    document.body.insertAdjacentHTML('afterbegin',
        '<div style="position:fixed;top:0;left:0;z-index:9999;background:#222;color:#fff;padding:4px 12px;font-size:14px;">Reload count: ' +
        count + '</div>');
}
</script>
<?php $this->end();