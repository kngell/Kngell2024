<?php

declare(strict_types=1);

class ShoppingCartController extends Controller
{
    use HtmlPageCacheableTrait;

    public function __construct(
        private readonly UserCartItemService $cartService,
        private ShoppingCartPageConfigFactory $shoppingCartFactory,
        private PageWrapperPageConfigFactory $pageWrapperFactory,
        private readonly CartEmptyComponent $cartEmpty,
    ) {
        $this->layout(NavbarType::ECOMMERCE);
    }

    public function index(): Response|string
    {
        $this->logTiming('Controller action started');
        $this->pageTitle('Shopping Cart');
        return $this->cachePage(
            function () {
                return $this->buildPage();
            },
            ttl: 3600, // 1 hour
        );
    }

    private function buildPage(): Response|string
    {
        $this->logTiming('Building page (cache miss)');
        $isAjax = $this->request->isAjax();

        $cartData = $this->cartService->getCartData();

        // ✅ Handle empty cart
        if (empty($cartData->items)) {
            if ($isAjax) {
                return $this->respondSuccess(
                    isAjax: true,
                    message: 'Cart is now empty',
                    redirect: $this->getRedirectUrl(),
                    flashType: FlashType::SUCCESS,
                    extraData: [
                        'empty' => true,
                        'content' => $this->cartEmpty->build()->generate(),
                        'cart' => $this->cartService->formatCartData($cartData),
                    ],
                );
            }

            $this->getFlash()->add('Your cart is empty', FlashType::INFO);
            return $this->displayFullPage(null, true);
        }
        return $this->displayFullPage($cartData, false);
    }

    private function displayFullPage(?CartData $cartData = null, bool $pageEmpty = false): string
    {
        $decoratedPage = $this->decorate(RegularPageDecorator::class, $this, [
            'factory' => $this->pageWrapperFactory->expectedController(static::class),
        ]);

        $decoratedPage = $this->decorate(RegularPageDecorator::class, $decoratedPage, [
            'items' => $pageEmpty ? [] : $cartData->items,
            'factory' => $this->shoppingCartFactory,
        ]);

        $pageData = $decoratedPage->page();
        $html = $this->render('/components/shopping-cart', $pageData);

        $this->logTiming('Page built');

        return $html;
    }
}