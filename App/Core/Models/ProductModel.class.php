<?php

declare(strict_types=1);

class ProductModel extends AbstractSaveModel
{
    public function findBySku(string $sku): ?Product
    {
        return $this->one(['sku' => $sku])->asClass();
    }

    public function getProductWithPaths(): array
    {
        $query = $this->em->setEntity($this->entity)->createQueryBuilder();

        $query->select('main_image', 'main_video')
            ->where(function ($query) {
                $query->where('main_image', '!=', '')
                      ->orWhere('main_video', '!=', '');
            })
            ->build();
        return $this->em->persist()->getQueryResult()->asArray();
    }

    public function getProductById(int|string $id): Product|null
    {
        if (is_int($id)) {
            $product = $this->find($id);
        } else {
            $product = $this->one(['public_id' => $id]);
        }
        if ($product->exists()) {
            return $product->asClass();
        }
        return null;
    }

    public function getProductsWithColumns(array $columns, int $page = 1, int $limit = 20, string $search = ''): array
    {
        $conditions = [
            'searchTerm' => !empty($search) ? '%' . $search . '%' : null,
        ];
        $results = $this->page($page, $limit, $conditions, $columns);
        if ($results->isSuccess()) {
            return $results->asArray();
        }
        return [];
    }

    public function deleteProductByUuId(string $id, ?string $deleteOption = null): ?QueryResult
    {
        $params = [];
        if ($deleteOption !== null) {
            $params['deleteOption'] = $deleteOption;
        }
        $params['public_id'] = $id;
        return parent::delete($params);
    }

    protected function validateData(array $data): void
    {
    }

    protected function generateMissingFields(array $data): array
    {
        $data = $this->generatePublicId($data);

        if (empty($data['slug']) && $this->entity->hasProperty('slug')) {
            $data['slug'] = $this->generateUniqueSlug($data['name']);
        }
        $defaults = [
            'status_id' => 1,
            'stock_quantity' => 0,
            'stock_status_id' => 1,
        ];

        foreach ($defaults as $field => $value) {
            if (empty($data[$field])) {
                $data[$field] = $value;
            }
        }
        return $data;
    }

    private function validateProductData(array $data): void
    {
        if (!isset($data['name']) || empty(trim($data['name']))) {
            throw new InvalidArgumentException('Product name is required.');
        }
    }
}