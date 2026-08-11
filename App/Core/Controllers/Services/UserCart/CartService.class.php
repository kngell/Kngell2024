<?php

declare(strict_types=1);

use Brick\Money\Money;
use Psr\Log\LoggerInterface;

/**
 * @extends AbstractSingleEntityService<UserCartShow>
 */
final class CartService extends AbstractSingleEntityService
{
    private const string SESSION_KEY = 'user_cart';
    private const string SESSION_ID_KEY = 'cart_session_id';

    public function __construct(
        private readonly UserCartShowModel $model,
        private readonly UserCartCacheManagerFactory $cacheFactory,
        private readonly MoneyManager $moneyManager,
        private readonly TaxManager $taxManager,
        private readonly ProductPriceService $productPriceService,
        private readonly CartWriteOperationsService $writeService,
        private readonly SessionInterface $session,
        private readonly UserContext $userContext,
        LoggerInterface $logger,
    ) {
        parent::__construct($cacheFactory->create(), $logger);
    }

    // ─── Read Operations ──────────────────────────────────────────

    public function getCartResponse(): CartResponse
    {
        $user = $this->userContext->currentUser();
        $userId = $user?->getUserId() ?? 0;
        $sessionId = $this->getSessionId();

        $page = $userId > 0 ? 'user_' . $userId : 'session_' . $sessionId;
        $response = $this->getForPage($page);

        if ($response instanceof CartResponse) {
            return $response;
        }

        return $this->createResponse(
            image: $this->getDefaultImageData(),
            entity: null,
            isDefault: true,
        );
    }

    public function getCurrentCart(): CartCollection
    {
        $response = $this->getCartResponse();
        return $this->createCartCollection($response->getEntity());
    }

    public function getCartData(): CartData
    {
        $cart = $this->getCurrentCart();
        $items = $cart->getItems();
        $totalPrice = $this->calculateTotal($items);

        return new CartData(
            items: $items,
            totalCount: $cart->getTotalCount(),
            totalPrice: $totalPrice,
            moneyManager: $this->moneyManager,
        );
    }

    public function getCartCount(): int
    {
        $response = $this->getCartResponse();
        return $response->getTotalCount();
    }

    // ─── Write Operations (Delegating to WriteService) ──────────

    public function addItem(int $productId, int $quantity = 1, array $productData = []): CartOperationResult
    {
        $user = $this->userContext->currentUser();
        $userId = $user?->getUserId() ?? 0;
        $sessionId = $this->getSessionId();

        $cart = $this->getCurrentCart();

        if (!empty($productData)) {
            $cart->addItemWithData($productId, $quantity, $productData);
        } else {
            $cart->addItem($productId, $quantity);
        }

        $cartItems = $this->prepareCartItemsForSave($cart);
        $cartPk = $cart->getCartPk();

        $result = $this->writeService->saveCart($userId, $sessionId, $cartItems, $cartPk);

        if ($result->isSuccess()) {
            $this->invalidateCartCache($userId, $sessionId);
            $this->loadCartIntoSession($userId, $sessionId);
        }

        return $result;
    }

    public function removeItem(int $productId): CartOperationResult
    {
        $user = $this->userContext->currentUser();
        $userId = $user?->getUserId() ?? 0;
        $sessionId = $this->getSessionId();

        $cart = $this->getCurrentCart();
        $cart->removeItem($productId);

        $cartItems = $this->prepareCartItemsForSave($cart);
        $cartPk = $cart->getCartPk();

        $result = $this->writeService->saveCart($userId, $sessionId, $cartItems, $cartPk);

        if ($result->isSuccess()) {
            $this->invalidateCartCache($userId, $sessionId);
            $this->loadCartIntoSession($userId, $sessionId);
        }

        return $result;
    }

    public function updateQuantity(int $productId, int $quantity): CartOperationResult
    {
        $user = $this->userContext->currentUser();
        $userId = $user?->getUserId() ?? 0;
        $sessionId = $this->getSessionId();

        $cart = $this->getCurrentCart();
        $cart->updateQuantity($productId, $quantity);

        $cartItems = $this->prepareCartItemsForSave($cart);
        $cartPk = $cart->getCartPk();

        $result = $this->writeService->saveCart($userId, $sessionId, $cartItems, $cartPk);

        if ($result->isSuccess()) {
            $this->invalidateCartCache($userId, $sessionId);
            $this->loadCartIntoSession($userId, $sessionId);
        }

        return $result;
    }

    public function clearCart(): CartOperationResult
    {
        $user = $this->userContext->currentUser();
        $userId = $user?->getUserId() ?? 0;
        $sessionId = $this->getSessionId();

        $cart = $this->getCurrentCart();
        $cartPk = $cart->getCartPk();

        if (!$cartPk) {
            return CartOperationResult::skipped(
                operation: 'clear',
                message: 'No cart to clear',
            );
        }

        $result = $this->writeService->clearCart($cartPk);

        if ($result->isSuccess()) {
            $this->invalidateCartCache($userId, $sessionId);
            $this->loadCartIntoSession($userId, $sessionId);
        }

        return $result;
    }

    public function deleteCart(): CartOperationResult
    {
        $user = $this->userContext->currentUser();
        $userId = $user?->getUserId() ?? 0;
        $sessionId = $this->getSessionId();

        $cart = $this->getCurrentCart();
        $cartPk = $cart->getCartPk();

        if (!$cartPk) {
            return CartOperationResult::notFound(
                operation: 'delete',
                message: 'No cart to delete',
            );
        }

        $result = $this->writeService->deleteCart($cartPk);

        if ($result->isSuccess()) {
            $this->invalidateCartCache($userId, $sessionId);
            $this->session->delete(self::SESSION_KEY);
        }

        return $result;
    }

    public function mergeOnLogin(int $userId, string $sessionId): CartOperationResult
    {
        $guestCart = $this->getCurrentCart();
        $guestCartPk = $guestCart->getCartPk();

        $userCart = $this->model->findByUserId($userId);
        $userCartPk = $userCart ? (int) $userCart->getId() : null;

        $result = $this->writeService->mergeCarts($userId, $sessionId, $guestCartPk, $userCartPk);

        if ($result->isSuccess()) {
            $this->invalidateCartCache($userId, $sessionId);
            $this->session->delete(self::SESSION_KEY);
            $this->loadCartIntoSession($userId, $sessionId);
        }

        return $result;
    }

    // ─── Override Abstract Methods ──────────────────────────────

    #[Override]
    public function getDefaultResponse(): CartResponse
    {
        return $this->createResponse(
            image: $this->getDefaultImageData(),
            entity: null,
            isDefault: true,
        );
    }

    #[Override]
    protected function fetchEntityFromDb(string $page): ?UserCartShow
    {
        if (str_starts_with($page, 'user_')) {
            $userId = (int) substr($page, 5);
            return $this->model->findByUserId($userId);
        }

        $sessionId = substr($page, 8);
        return $this->model->findBySessionId($sessionId);
    }

    #[Override]
    protected function fetchEntityByIdFromDb(string $id): ?UserCartShow
    {
        return $this->model->findByCartId((int) $id);
    }

    #[Override]
    protected function buildResponsiveImage(Entity $entity): array
    {
        if (!$entity instanceof UserCartShow) {
            return $this->getDefaultImageData();
        }

        return $this->buildImageData($entity);
    }

    #[Override]
    protected function getDefaultImageData(): array
    {
        return [
            'fallback' => [
                'src' => '/public/assets/img/empty-cart.png',
                'srcset' => '/public/assets/img/empty-cart.png 400w',
                'alt' => 'Empty cart',
                'width' => 400,
                'height' => 300,
            ],
        ];
    }

    #[Override]
    protected function createResponse(array $image, ?Entity $entity, bool $isDefault): EntityResponseInterface
    {
        return new CartResponse(
            image: $image,
            cart: $entity instanceof UserCartShow ? $entity : null,
            isDefault: $isDefault,
            moneyManager: $this->moneyManager,
            productPriceService: $this->productPriceService,
        );
    }

    #[Override]
    protected function warmupIdentifier(string $identifier): int
    {
        $entity = $this->fetchEntityFromDb($identifier);
        if ($entity) {
            $this->cache->getEntityForPage(
                $identifier,
                static::class,
                fn ($p) => $entity,
                fn ($id) => $this->fetchEntityByIdFromDb($id),
            );
            return 1;
        }
        return 0;
    }

    // ─── Private Helper Methods ──────────────────────────────

    private function getSessionId(): string
    {
        $sessionId = $this->session->get(self::SESSION_ID_KEY);
        if (!$sessionId) {
            $sessionId = session_id() ?: bin2hex(random_bytes(32));
            $this->session->set(self::SESSION_ID_KEY, $sessionId);
        }
        return $sessionId;
    }

    private function invalidateCartCache(int $userId, string $sessionId): void
    {
        if ($userId > 0) {
            $this->cache->invalidatePage('user_' . $userId, static::class);
        }
        $this->cache->invalidatePage('session_' . $sessionId, static::class);
    }

    private function loadCartIntoSession(int $userId, string $sessionId): void
    {
        $cart = $this->model->findUserCart($userId, $sessionId);
        if ($cart) {
            $collection = $this->createCartCollection($cart);
            $this->session->set(self::SESSION_KEY, $collection->toArray());
        }
    }

    private function createCartCollection(?UserCartShow $cart): CartCollection
    {
        /** @var UserCartItem[] */
        $cartItems = $cart->getUserCartItem() ?: [];
        $cartPk = $cart ? (int) $cart->getId() : null;

        if (empty($cartItems)) {
            return new CartCollection(
                items: [],
                moneyManager: $this->moneyManager,
                taxManager: $this->taxManager,
                cartPk: $cartPk,
            );
        }

        $productIds = [];

        foreach ($cartItems as $item) {
            $productIds[] = $item->getProductId();
        }

        /** @var ProductPriceResponse[] $priceResponses */
        $priceResponses = $this->productPriceService->getPricesForProducts($productIds);

        $items = [];
        foreach ($cartItems as $item) {
            $productId = $item->getProductId();
            $response = $priceResponses[$productId] ?? null;

            if ($response === null) {
                $response = $this->productPriceService->getPriceForProduct($productId);
            }

            $entity = $response->getEntity();
            $price = $entity?->getProductRegionalPrice()?->getBasePrice() ?? 0;
            $name = $response->getName() ?? 'Product ' . $productId;
            $imageUrl = $response->getImageUrl();
            $onSale = $response->isOnSale();
            $discountPercent = $response->getDiscountPercent();
            $weight = $response->getWeight();

            $items[] = [
                'itemId' => $productId,
                'quantity' => $item->getQuantity(),
                'weight' => $weight,
                'variant_data' => $item->getVariantData(),
                'name' => $name,
                'price' => $price,
                'currency' => $this->moneyManager->getCurrencyCode(),
                'includes_tax' => $this->taxManager->shouldPriceIncludeTax(),
                'imageUrl' => $imageUrl,
                'onSale' => $onSale,
                'discountPercent' => $discountPercent,
            ];
        }

        return new CartCollection(
            items: $items,
            moneyManager: $this->moneyManager,
            taxManager: $this->taxManager,
            cartPk: $cartPk,
        );
    }

    private function prepareCartItemsForSave(CartCollection $cart): array
    {
        return array_map(function ($item) {
            return [
                'product_id' => $item->getItemId(),
                'quantity' => $item->getQuantity(),
                'variant_data' => null,
            ];
        }, $cart->getItems());
    }

    private function calculateTotal(array $items): Money
    {
        $prices = [];
        foreach ($items as $item) {
            if ($item instanceof CartItem) {
                $prices[] = $item->getTotalPrice();
            }
        }

        return $this->moneyManager->sumMoneyArray($prices);
    }

    private function buildImageData(UserCartShow $cart): array
    {
        $items = $cart->getUserCartItem();
        if (!empty($items)) {
            $firstItem = $items[0];
            $priceResponse = $this->productPriceService->getPriceForProduct(
                $firstItem->getProductId(),
            );

            $imageUrl = $priceResponse->getImageUrl();
            if (!empty($imageUrl)) {
                return [
                    'fallback' => [
                        'src' => $imageUrl,
                        'srcset' => $imageUrl . ' 400w',
                        'alt' => 'Cart item',
                        'width' => 400,
                        'height' => 300,
                    ],
                ];
            }
        }

        return $this->getDefaultImageData();
    }
}