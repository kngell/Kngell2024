<?php

declare(strict_types=1);

class UserCartController extends Controller
{
    public function __construct(
        private readonly UserCartItemService $cartService,
        private readonly UserCartComponent $userCart,
        private readonly CartItemComponent $cartItem,
        private readonly CartSummaryComponent $cartSummary,
    ) {
    }

    public function addItem(): Response
    {
        try {
            $productId = $this->request->get('product_id', null);
            $quantity = (int) $this->request->get('quantity', 1);
            $isAjax = $this->request->isAjax();

            if (!$productId) {
                return $this->respondError(
                    isAjax: $this->request->isAjax(),
                    message: 'Product ID is required',
                    redirect: $this->getRedirectUrl(),
                    statusCode: HttpStatusCode::HTTP_BAD_REQUEST,
                );
            }

            if ($quantity < 1) {
                return $this->respondError(
                    isAjax: $this->request->isAjax(),
                    message: 'Quantity must be at least 1',
                    redirect: $this->getRedirectUrl(),
                    statusCode: HttpStatusCode::HTTP_BAD_REQUEST,
                );
            }

            // Add to cart
            $cartData = $this->cartService->addItem(
                productId: $productId,
                quantity: $quantity,
            );
            $this->invalidateCache(ShoppingCartController::class, 'index');

            // Build updated cart HTML
            $html = $this->userCart->build($cartData);

            // ─── AJAX Response ──────────────────────────────────
            if ($isAjax) {
                return $this->respondSuccess(
                    isAjax: true,
                    message: 'Item added to cart',
                    redirect: $this->getRedirectUrl(),
                    flashType: FlashType::SUCCESS,
                    extraData: [
                        'cart' => $this->cartService->formatCartData($cartData),
                        'html' => $html->generate(),
                    ],
                );
            }

            // ─── Non-AJAX Response ──────────────────────────────
            $this->flash->add('Item added to cart', FlashType::SUCCESS);
            return $this->redirect($this->getRedirectUrl());
        } catch (InvalidArgumentException $e) {
            return $this->respondError(
                isAjax: $this->request->isAjax(),
                message: $e->getMessage(),
                redirect: $this->getRedirectUrl(),
                statusCode: HttpStatusCode::HTTP_BAD_REQUEST,
            );
        } catch (RuntimeException $e) {
            return $this->respondError(
                isAjax: $this->request->isAjax(),
                message: $e->getMessage(),
                redirect: $this->getRedirectUrl(),
                statusCode: HttpStatusCode::HTTP_NOT_FOUND,
            );
        } catch (Exception $e) {
            $this->logger->error('Add to cart error: ' . $e->getMessage());

            return $this->respondError(
                isAjax: $this->request->isAjax(),
                message: 'An error occurred while adding item to cart',
                redirect: $this->getRedirectUrl(),
                statusCode: HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function updateDown(): Response
    {
        $productId = $this->request->get('product_id', null);
        $quantity = (int) $this->request->get('quantity', 0);
        $isAjax = $this->request->isAjax();

        if (!$productId) {
            return $this->respondError(
                isAjax: $isAjax,
                message: 'Product ID is required',
                redirect: $this->getRedirectUrl(),
                statusCode: HttpStatusCode::HTTP_BAD_REQUEST,
            );
        }

        if ($quantity < 1) {
            return $this->respondError(
                isAjax: $isAjax,
                message: 'Quantity must be at least 1',
                redirect: $this->getRedirectUrl(),
                statusCode: HttpStatusCode::HTTP_BAD_REQUEST,
            );
        }
        $cartData = $this->cartService->updateQuantity(
            productId: $productId,
            quantity: $quantity,
        );
        $userCart = $this->userCart->build($cartData)->generate();
        $cartItems = $this->cartItem->build($cartData->items)->generate();
        $cartSummary = $this->cartSummary->build($cartData->items)->generate();
        if ($isAjax) {
            return $this->respondSuccess(
                isAjax: true,
                message: 'Item added to cart',
                redirect: $this->getRedirectUrl(),
                flashType: FlashType::SUCCESS,
                extraData: [
                    'userCart' => $userCart,
                    'cartItems' => $cartItems,
                    'cartSummary' => $cartSummary,
                ],
            );
        }
        // ─── Non-AJAX Response ──────────────────────────────
        $this->flash->add('Item added to cart', FlashType::SUCCESS);
        return $this->redirect($this->getRedirectUrl());
    }

    public function updateItem(): Response
    {
        try {
            $productId = $this->request->get('product_id', null);
            $quantity = (int) $this->request->get('quantity', 0);

            if (!$productId) {
                return $this->respondError(
                    isAjax: $this->request->isAjax(),
                    message: 'Product ID is required',
                    redirect: $this->getRedirectUrl(),
                    statusCode: HttpStatusCode::HTTP_BAD_REQUEST,
                );
            }

            if ($quantity < 0) {
                return $this->respondError(
                    isAjax: $this->request->isAjax(),
                    message: 'Quantity cannot be negative',
                    redirect: $this->getRedirectUrl(),
                    statusCode: HttpStatusCode::HTTP_BAD_REQUEST,
                );
            }

            $cartData = $this->cartService->updateQuantity($productId, $quantity);
            $html = $this->cartComponent->build($cartData);

            if ($this->request->isAjax()) {
                return $this->respondSuccess(
                    isAjax: true,
                    message: $quantity === 0 ? 'Item removed from cart' : 'Cart updated',
                    redirect: $this->getRedirectUrl(),
                    flashType: FlashType::SUCCESS,
                    extraData: [
                        'cart' => $this->cartService->formatCartData($cartData),
                        'html' => $html->generate(),
                    ],
                );
            }

            $this->flash->add('Cart updated', FlashType::SUCCESS);
            return $this->redirect($this->getRedirectUrl());
        } catch (Exception $e) {
            $this->logger->error('Update cart error: ' . $e->getMessage());

            return $this->respondError(
                isAjax: $this->request->isAjax(),
                message: 'Failed to update cart',
                redirect: $this->getRedirectUrl(),
                statusCode: HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function removeItem(): Response
    {
        try {
            $productId = $this->request->get('product_id', null);

            if (!$productId) {
                return $this->respondError(
                    isAjax: $this->request->isAjax(),
                    message: 'Product ID is required',
                    redirect: $this->getRedirectUrl(),
                    statusCode: HttpStatusCode::HTTP_BAD_REQUEST,
                );
            }

            $cartData = $this->cartService->removeItem($productId);
            $html = $this->cartComponent->build($cartData);

            if ($this->request->isAjax()) {
                return $this->respondSuccess(
                    isAjax: true,
                    message: 'Item removed from cart',
                    redirect: $this->getRedirectUrl(),
                    flashType: FlashType::SUCCESS,
                    extraData: [
                        'cart' => $this->cartService->formatCartData($cartData),
                        'html' => $html->generate(),
                    ],
                );
            }

            $this->flash->add('Item removed from cart', FlashType::SUCCESS);
            return $this->redirect($this->getRedirectUrl());
        } catch (Exception $e) {
            $this->logger->error('Remove cart error: ' . $e->getMessage());

            return $this->respondError(
                isAjax: $this->request->isAjax(),
                message: 'Failed to remove item',
                redirect: $this->getRedirectUrl(),
                statusCode: HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function clearCart(): Response
    {
        try {
            $cartData = $this->cartService->clearCart();
            $html = $this->cartComponent->build($cartData);

            if ($this->request->isAjax()) {
                return $this->respondSuccess(
                    isAjax: true,
                    message: 'Cart cleared',
                    redirect: $this->getRedirectUrl(),
                    flashType: FlashType::SUCCESS,
                    extraData: [
                        'cart' => $this->cartService->formatCartData($cartData),
                        'html' => $html->generate(),
                    ],
                );
            }

            $this->flash->add('Cart cleared', FlashType::SUCCESS);
            return $this->redirect($this->getRedirectUrl());
        } catch (Exception $e) {
            $this->logger->error('Clear cart error: ' . $e->getMessage());

            return $this->respondError(
                isAjax: $this->request->isAjax(),
                message: 'Failed to clear cart',
                redirect: $this->getRedirectUrl(),
                statusCode: HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function getCart(): Response|string
    {
        $this->pageTitle('Shopping Cart');
        $isAjax = $this->request->isAjax();

        $cartData = $this->cartService->getCartData();
        $html = $this->userCart->build($cartData);

        if ($isAjax) {
            return $this->respondSuccess(
                isAjax: true,
                message: 'Cart loaded',
                redirect: $this->getRedirectUrl(),
                flashType: FlashType::SUCCESS,
                extraData: [
                    'cart' => $this->cartService->formatCartData($cartData),
                    'html' => $html->generate(),
                ],
            );
        }

        return $this->render('/cart/index', [
            'cart' => $cartData,
            'cartComponent' => $html,
        ]);
    }

    public function getCount(): Response
    {
        return new JsonResponse([
            'success' => true,
            'count' => $this->cartService->itemCount(),
        ]);
    }

    private function debugCacheStatus(string $label): void
    {
        $cache = $this->getHtmlCache();

        // Check if the cache keys exist
        $keys = [
            'ShoppingCart_index',
            'Ecommerce_index',
        ];

        foreach ($keys as $key) {
            $fullKey = 'html_page_' . $key . '_*';
            error_log("[Cache] $label - Checking pattern: $fullKey");

            // Try to get the cache
            $cached = $cache->getPage($key, []);
            error_log("[Cache] $label - $key exists: " . ($cached ? 'YES' : 'NO'));
        }
    }
}