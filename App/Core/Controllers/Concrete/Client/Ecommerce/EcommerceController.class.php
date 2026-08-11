<?php

declare(strict_types=1);

class EcommerceController extends Controller
{
    public function __construct(
        private IndexPageConfigFactory $indexFactory,
        private PageWrapperPageConfigFactory $pageWrapperFactory,
    ) {
        $this->layout(NavbarType::ECOMMERCE);
    }

    public function index(): string
    {
        $this->pageTitle('Ecommerce');
        $this->invalidateCacheIfFlagged(static::class, 'index');
        return $this->cachePage(
            function () {
                $decoratedPage = $this->decorate(RegularPageDecorator::class, $this, [
                    'factory' => $this->pageWrapperFactory,
                ]);
                $decoratedPage = $this->decorate(RegularPageDecorator::class, $decoratedPage, [
                    'factory' => $this->indexFactory,
                ]);
                return $this->render('index', $decoratedPage->page());
            },
            ttl: 3600, // 1 hour
        );
    }

    public function shop(): string
    {
        $this->pageTitle('Shop');
        return $this->render('shop');
    }

    public function product(): string
    {
        $this->pageTitle('Product');
        return $this->render('product');
    }

    public function shoppingCart(): string
    {
        $this->pageTitle('Shopping Cart');
        return $this->render('shopping-cart');
    }
}