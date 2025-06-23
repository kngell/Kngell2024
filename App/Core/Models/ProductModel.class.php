<?php

declare(strict_types=1);

class ProductModel extends Model
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function getTotal() : int
    {
        $this->entityManager->createQueryBuilder()->select('count(name) AS tot')->build();
        $total = $this->entityManager->persist()->getResults();
        $count = ArrayUtils::first($total->getResults()->all());
        return $count['tot'];
    }

    public function getProducts(int $offset = 0, int $limit = 10) : array
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select()
            ->innerJoin('product_category')
            ->on('product_category.product_id', 'product.id')
            ->innerJoin('categories', 'category_name', 'category_description')
            ->on('product_category.category_id', 'categories.cat_id')
            ->innerJoin('brand', 'brand_name')
            ->on('categories.br_id', 'brand.br_id')
            ->limit($limit)
            ->offset($offset)
            ->orderBy('product.id', 'DESC')
            ->build();

        return $this->entityManager->persist()->getResults()->getResults('object')->all();
    }

    public function getProductById(int $id) : Product|NullObjectInterface
    {
        $product = $this->find($id);
        if ($product->getQueryResult() && $product->rowCount() > 0) {
            return $product->getResults('class')->single();
        }
        return new NullObject();
    }
}