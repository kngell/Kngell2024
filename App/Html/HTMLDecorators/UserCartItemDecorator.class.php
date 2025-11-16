<?php

declare(strict_types=1);

class UserCartItemDecorator extends AbstractHtmlDecorator
{
    private UserCartHTMLElement $userCart;
    private int $nbItems;
    private int $totalQty;
    private array $userCartItems;

    public function __construct(Controller $controller)
    {
        parent::__construct($controller);
        $this->userCart = new UserCartHTMLElement(
            $this->builder,
            $this->userCartItems(),
            MoneyManager::getInstance()
        );
    }

    public function page(): array
    {
        $uri = $this->request->get('url');
        if (str_contains($uri, 'checkout/index')) {
            return array_merge(
                $this->controller->page(),
                ['checkoutHtmlelement' => $this->userCart->getCheckoutHtmlElement(),
                    'nbItems' => $this->nbItems,
                    'paymentSubmitBtn' => $this->userCart->getPaymentSubmitBtn(),
                ]
            );
        }
        if (str_contains($uri, 'cart/index')) {
            return array_merge(
                $this->controller->page(),
                ['userCart' => $this->userCart->display(),
                    'nbItems' => $this->nbItems,
                ]
            );
        }
        return array_merge(
            $this->controller->page(),
            ['nbItems' => $this->nbItems,
            ]
        );
    }

    /**
     * @return UserCartHTMLElement
     */
    public function getUserCart(): UserCartHTMLElement
    {
        return $this->userCart;
    }

    /**
     * @return Cart[]
     * @throws SessionInvalidArgumentException
     */
    private function userCartItems(): array
    {
        $cartModel = $this->getModel(CartModel::class);
        if (! $cartModel instanceof CartModel) {
            return [];
        }
        if ($this->session->exists(CURRENT_USER_SESSION_NAME)) {
            $userId = $this->session->get(CURRENT_USER_SESSION_NAME);
            $userItems = $cartModel->getUserItem($userId);
            if (! empty($userItems)) {
                $this->nbItems = (int) count($userItems);
                $this->totalQty = (int) $userItems[0]->getitemQuantity();
            } else {
                $this->nbItems = 0;
                $this->totalQty = 0;
            }
            return $this->userCartItems = $userItems;
        }
        // Logic to get the user-specific item count from the cart
        if ($this->session->exists('cart')) {
            $cartItems = $this->session->get('cart');
            $this->nbItems = count($cartItems);
            $this->totalQty = array_reduce($cartItems, function ($carry, $item) {
                return $carry + $item['item_quantity'];
            }, 0);
            return $this->userCartItems = $this->arrayCartEntity($cartItems);
        }
        $this->nbItems = 0;
        $this->totalQty = 0;
        return $this->userCartItems = [];
    }

    /**
     * @param array $cartItems
     * @return Cart[]
     */
    private function arrayCartEntity(array $cartItems) : array
    {
        $cartEntityCollection = [];
        foreach ($cartItems as $id => $cart) {
            $cartEntity = new Cart();
            $cartEntity->setCartId((string) $id);
            $cartEntity->setItemId($cart['item_id']);
            $cartEntity->setItemQuantity((int) $cart['item_quantity']);
            $cartEntity->setItemName($cart['item_name']);
            $cartEntity->setItemPrice($cart['item_price']);
            $cartEntityCollection[] = $cartEntity;
        }
        return $cartEntityCollection;
    }
}