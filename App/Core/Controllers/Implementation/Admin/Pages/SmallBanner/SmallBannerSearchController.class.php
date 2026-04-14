<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

class SmallBannerSearchController extends Controller
{
    public function __construct(
        private ProductSearchService $productSearchService,
        private LoggerInterface $logger,
    ) {
    }

    public function loadProducts(): Response
    {
        try {
            $page = (int) ($this->request->get('page') ?? 1);
            $limit = (int) ($this->request->get('limit') ?? 20);
            $search = $this->request->get('search') ?? '';
            $products = $this->productSearchService->searchProducts($page, $limit, $search, $this->builder);
            if ($this->request->isAjax()) {
                return $this->jsonResponse($products);
            }
            return $this->response($this->render('/components/small_banner', ['products' => $products]));
        } catch (Exception $e) {
            $this->logger->error('Product search API error', [
                'error' => $e->getMessage(),
                'page' => $page ?? null,
                'search' => $search ?? null,
            ]);

            return $this->jsonResponse([
                'products' => [],
                'total' => 0,
                'page' => $page ?? 1,
                'limit' => $limit ?? 20,
                'hasMore' => false,
                'error' => 'An error occurred while searching products',
            ], 500);
        }
    }

    public function getProduct(): Response
    {
        $id = (int) $this->request->get('id', 0);

        if ($id <= 0) {
            return $this->jsonResponse(['error' => 'Invalid product ID'], 400);
        }

        $products = $this->productSearchService->getProductsByIds([$id]);

        if (empty($products)) {
            return $this->jsonResponse(['error' => 'Product not found'], 404);
        }

        return $this->jsonResponse($products[0]);
    }
}