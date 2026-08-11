<?php

declare(strict_types=1);

class UserCartOperationController extends Controller
{
    public function __construct(
        private readonly UserCartItemService $cartService,
        private readonly UserCartComponent $userCart,
        private readonly CartItemComponent $cartItem,
        private readonly CartSummaryComponent $cartSummary,
        private readonly CartEmptyComponent $cartEmpty,
    ) {
    }

    public function updateDown(): Response
    {
        $productId = (int) $this->request->get('product_id', null);
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

        try {
            $cartData = $this->cartService->updateQuantity(
                productId: $productId,
                quantity: $quantity,
            );

            $this->invalidateCache(ShoppingCartController::class, 'index');

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

                $this->flash->add('Cart is now empty', FlashType::INFO);
                return $this->redirect($this->getRedirectUrl());
            }

            // ✅ Build updated cart HTML (only if not empty)
            $userCart = $this->userCart->build($cartData)->generate();
            $cartItems = $this->cartItem->build($cartData->items)->generate();
            $cartSummary = $this->cartSummary->build($cartData)->generate();

            if ($isAjax) {
                return $this->respondSuccess(
                    isAjax: true,
                    message: 'Cart updated',
                    redirect: $this->getRedirectUrl(),
                    flashType: FlashType::SUCCESS,
                    extraData: [
                        'empty' => false,
                        'cartItems' => $cartItems,
                        'cartSummary' => $cartSummary,
                        'userCart' => $userCart,
                        'cart' => $this->cartService->formatCartData($cartData),
                    ],
                );
            }

            // ─── Non-AJAX Response ──────────────────────────────
            $this->flash->add('Cart updated', FlashType::SUCCESS);
            return $this->redirect($this->getRedirectUrl());
        } catch (Exception $e) {
            $this->logger->error('Update down error: ' . $e->getMessage());

            return $this->respondError(
                isAjax: $isAjax,
                message: 'An error occurred while updating the cart',
                redirect: $this->getRedirectUrl(),
                statusCode: HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function updateUp(): Response
    {
        $productId = (int) $this->request->get('product_id', null);
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

        try {
            $cartData = $this->cartService->updateQuantity(
                productId: $productId,
                quantity: $quantity,
            );

            $this->invalidateCache(ShoppingCartController::class, 'index');

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

                $this->flash->add('Cart is now empty', FlashType::INFO);
                return $this->redirect($this->getRedirectUrl());
            }

            // ✅ Build updated cart HTML (only if not empty)
            $userCart = $this->userCart->build($cartData)->generate();
            $cartItems = $this->cartItem->build($cartData->items)->generate();
            $cartSummary = $this->cartSummary->build($cartData)->generate();

            if ($isAjax) {
                return $this->respondSuccess(
                    isAjax: true,
                    message: 'Cart updated',
                    redirect: $this->getRedirectUrl(),
                    flashType: FlashType::SUCCESS,
                    extraData: [
                        'empty' => false,
                        'cartItems' => $cartItems,
                        'cartSummary' => $cartSummary,
                        'userCart' => $userCart,
                        'cart' => $this->cartService->formatCartData($cartData),
                    ],
                );
            }

            // ─── Non-AJAX Response ──────────────────────────────
            $this->flash->add('Cart updated', FlashType::SUCCESS);
            return $this->redirect($this->getRedirectUrl());
        } catch (Exception $e) {
            $this->logger->error('Update up error: ' . $e->getMessage());

            return $this->respondError(
                isAjax: $isAjax,
                message: 'An error occurred while updating the cart',
                redirect: $this->getRedirectUrl(),
                statusCode: HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function removeItem(): Response
    {
        $productId = (int) $this->request->get('product_id', null);
        $isAjax = $this->request->isAjax();

        if (!$productId) {
            return $this->respondError(
                isAjax: $isAjax,
                message: 'Product ID is required',
                redirect: $this->getRedirectUrl(),
                statusCode: HttpStatusCode::HTTP_BAD_REQUEST,
            );
        }

        try {
            $cartData = $this->cartService->removeItem($productId);

            $this->invalidateCache(ShoppingCartController::class, 'index');

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

                $this->flash->add('Cart is now empty', FlashType::INFO);
                return $this->redirect($this->getRedirectUrl());
            }

            // ✅ Build updated cart HTML (only if not empty)
            $userCart = $this->userCart->build($cartData)->generate();
            $cartItems = $this->cartItem->build($cartData->items)->generate();
            $cartSummary = $this->cartSummary->build($cartData)->generate();

            if ($isAjax) {
                return $this->respondSuccess(
                    isAjax: true,
                    message: 'Item removed from cart',
                    redirect: $this->getRedirectUrl(),
                    flashType: FlashType::SUCCESS,
                    extraData: [
                        'empty' => false,
                        'cartItems' => $cartItems,
                        'cartSummary' => $cartSummary,
                        'userCart' => $userCart,
                        'cart' => $this->cartService->formatCartData($cartData),
                    ],
                );
            }

            // ─── Non-AJAX Response ──────────────────────────────
            $this->flash->add('Item removed from cart', FlashType::SUCCESS);
            return $this->redirect($this->getRedirectUrl());
        } catch (Exception $e) {
            $this->logger->error('Remove item error: ' . $e->getMessage());

            return $this->respondError(
                isAjax: $isAjax,
                message: 'An error occurred while removing the item',
                redirect: $this->getRedirectUrl(),
                statusCode: HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}