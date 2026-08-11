<?php

declare(strict_types=1);

class ContentBlockSearchController extends Controller
{
    public function __construct(
        private ProductSearchService $productSearchService,
    ) {
    }

    public function loadProducts(): Response
    {
        try {
            $page = (int) ($this->request->get('page') ?? 1);
            $limit = (int) ($this->request->get('limit') ?? 20);
            $search = $this->request->get('search') ?? '';
            $products = $this->productSearchService->searchProducts($page, $limit, $search);
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
            ], HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getProduct(): Response
    {
        $id = (int) $this->request->get('id', 0);

        if ($id <= 0) {
            return $this->jsonResponse(['error' => 'Invalid product ID'], HttpStatusCode::HTTP_BAD_REQUEST);
        }

        $products = $this->productSearchService->getProductsByIds([$id]);

        if (empty($products)) {
            return $this->jsonResponse(['error' => 'Product not found'], HttpStatusCode::HTTP_NOT_FOUND);
        }

        return $this->jsonResponse($products[0]);
    }
}