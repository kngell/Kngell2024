<?php

declare(strict_types=1);

class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutFormConfigFactory $factory,
        private readonly UserCartItemService $cartService,
        private PageWrapperPageConfigFactory $pageWrapperFactory,
        FormCreatorService $frm,
    ) {
        $this->layout(NavbarType::ECOMMERCE);
        $this->frm = $frm;
    }

    public function index(): string
    {
        $this->pageTitle('Checkout');
        return $this->render('index_3'); // $cart->page()
    }

    public function proceed(): Response|string
    {
        $this->pageTitle('Checkout');

        $cartData = $this->cartService->getCartData();
        $decorated = $this->decorate(RegularPageDecorator::class, $this, [
            'factory' => $this->pageWrapperFactory,
        ]);
        $decorated = $this->decorate(FormDecorator::class, $decorated, [
            'factory' => $this->factory,
            'formValues' => $cartData->items,
        ]);
        return $this->render('checkout', $decorated->page());
    }

    public function payment(): string
    {
        return $this->render('payment_method');
    }

    public function input(): string
    {
        return $this->render('/input');
    }

    public function test(): string
    {
        $this->pageTitle('test');
        return $this->render('index_2');
    }

    public function testCheckoutForm(): string
    {
        $cartData = $this->cartService->getCartData();
        $decorated = $this->decorate(RegularPageDecorator::class, $this, [
            'factory' => $this->pageWrapperFactory,
        ]);
        $decorated = $this->decorate(FormDecorator::class, $decorated, [
            'factory' => $this->factory,
            'formValues' => $cartData->items,
        ]);
        return $this->render('checkout', $decorated->page());
    }

    protected function getModalData(null|Entity $entity = null): array
    {
        $dto = $this->createDTO($entity);
        if ($dto === null) {
            return [];
        }
        $this->getModalBuilder()->setDto($dto);

        $decorated = $this->decorate(
            FormDecorator::class,
            $this,
            [
                'modalBuilder' => $this->getModalBuilder(),
                'formValues' => $entity ?? $dto->toFormValues(),
                'action' => $this->getSaveRoute(),
                'factory' => $this->getFormFactory(),
            ],
        );

        return $decorated->page();
    }
}