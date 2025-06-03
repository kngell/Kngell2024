<?php

declare(strict_types=1);

class CartController extends Controller
{
    public function __construct(private CartModel $cartModel)
    {
        $this->currentModel($cartModel);
    }

    public function index() : string
    {
        $this->pageTitle('Cart');
        $userCart = new UserCartItemDecorator($this);
        return $this->render('index', $userCart->page());
    }

    public function addToCart() : Response
    {
        $this->pageTitle('Cart');
        $data = $this->request->getPost()->getAll();
        $product = $this->cartModel->getProduct($data['product_id']);
        if ($product instanceof NullObjectInterface) {
            $this->flash->add('This Product does not exist in the database.', FlashType::DANGER);
            return new RedirectResponse('/paypal/product');
        }
        if (! $this->session->exists(CURRENT_USER_SESSION_NAME)) {
            $this->addGuestItemToCart($data, $product);
        } else {
            $this->addLoggedInUserItemToCart($data, $product);
        }
        return new RedirectResponse($this->getRedirectUrl() ?? '/');
    }

    public function updateQuantity() : Response
    {
        $this->pageTitle('Update cart quantity');
        $data = $this->request->getPost()->getAll();
        if ($this->session->exists(CURRENT_USER_SESSION_NAME)) {
            $this->cartModel->save($data);
        } elseif ($this->session->exists('cart')) {
            $cart = $this->session->get('cart');
            foreach ($cart as $id => $item) {
                if ((int) $data['item_id'] === $id) {
                    $cart[$id]['item_quantity'] = $data['item_quantity'];
                }
            }
            $this->session->set('cart', $cart);
        } else {
            throw new BaseNoValueException('item does not exists');
        }

        return new RedirectResponse('/cart/index');
    }

    public function removeFromCart() : Response
    {
        $this->pageTitle('Remove Item from Cart');
        if ($this->session->exists(CURRENT_USER_SESSION_NAME)) {
            $userId = $this->session->get(CURRENT_USER_SESSION_NAME);
            $cart = $this->cartModel->getCart($userId);
            foreach ($cart as $cartItem) {
                if ($cartItem->getItemId() === $this->request->getPost()->get('item_id')) {
                    $this->cartModel->removeItemFromCart($cartItem->getItemId());
                }
            }
        } else {
            if ($this->session->exists('cart')) {
                $cart = $this->session->get('cart');
                foreach ($cart as $id => $cartItem) {
                    if ($id === (int) $this->request->getPost()->get('item_id')) {
                        unset($cart[$id]);
                    }
                }
            }
            $this->session->set('cart', $cart);
        }
        return new RedirectResponse('/cart/index');
    }

    public function restoreUserCart() : Response
    {
        $userCart = $this->request->getRawContent();
        if (! $this->session->exists('cart')) {
        }
        return new JsonResponse(['status' => 'success']);
    }

    private function addLoggedInUserItemToCart(array $data, Product $product) : void
    {
        $userId = $this->session->get(CURRENT_USER_SESSION_NAME);
        $cart = $this->cartModel->getCart($userId);
        $itemExistsInCart = false;
        foreach ($cart as $cartItem) {
            if ($cartItem->getItemId() === $data['product_id']) {
                $cartItem->setItemQuantity($cartItem->getItemQuantity() + 1);
                $cartItem->setItemName($product->getName());
                $cartItem->setItemPrice($product->getPrice());
                $this->cartModel->save($cartItem);
                $itemExistsInCart = true;
            }
        }
        if (! $itemExistsInCart) {
            $newCartItem = new Cart();
            $newCartItem->setUserId($userId);
            $newCartItem->setItemId($data['product_id']);
            $newCartItem->setItemQuantity(1);
            $newCartItem->setItemName($product->getName());
            $newCartItem->setItemPrice($product->getPrice());
            $this->cartModel->save($newCartItem);
        }
    }

    private function addGuestItemToCart(array $data, Product $product) : void
    {
        if (! $this->session->exists('cart')) {
            $this->session->set('cart', []);
        }
        $cart = $this->session->get('cart');
        $cart[$data['product_id']] = [
            'item_id' => $data['product_id'],
            'item_quantity' => ($cart[$data['product_id']]['item_quantity'] ?? 0) + 1,
            'item_name' => $product->getName(),
            'item_price' => $product->getPrice(),
        ];
        $this->session->set('cart', $cart);
    }
}