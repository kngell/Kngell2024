<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ProductModel extends Model
{
    public function save(mixed $data = null): QueryResult
    {
        if ($data === null) {
            throw new InvalidArgumentException('No data to save.');
        }
        if (is_array($data)) {
            if (!isset($data['name'])) {
                throw new InvalidArgumentException('Cannot save without product name.');
            }
            if (!ArrayUtils::isAssoc($data)) {
                throw new InvalidArgumentException('Save data should be an associative array.');
            }
            $product = $this->getProductById((int) $data['id']);
            $product->getChangeTracker()->track($product);
            /** @var Product */
            $data = $product->assign($data);
        }
        if (!$data instanceof Product) {
            throw new RuntimeException('data to save must be an instance of Product entity.');
        }
        if (!$data->isInitialized('public_id')) {
            /** @var UuidInterface $publicId */
            $publicId = Uuid::uuid4();
            $data->setPublicId($publicId);
        }
        if (!$data->isInitialized('slug')) {
            $baseSlug = $this->slugify($data->getName());
            $slug = $baseSlug;
            $counter = 0;

            while ($this->one(['slug' => $slug])->exists()) {
                $counter++;
                $slug = $baseSlug . '-' . $counter;
            }

            $data->setSlug($slug);
        }
        $dirtyData = $data->getDirtyData();
        dd('dirty data' . var_dump($dirtyData, true));
        return parent::save($data);
    }

    public function getTotal(): int
    {
        $this->em->createQueryBuilder()->select('count(name) AS tot')->build();
        $total = $this->em->persist()->getQueryResult();
        $count = ArrayUtils::first($total->getResults()->all());
        return $count['tot'];
    }

    public function getProducts(int $offset = 0, int $limit = 10): array
    {
        $query = $this->em->createQueryBuilder()
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

        return $this->em->persist()->getQueryResult()->getResults('object')->all();
    }

    public function getProductById(int $id): Product|NullObjectInterface
    {
        $product = $this->find($id)->asClass();
        if ($product instanceof Product) {
            return $product;
        }
        return new NullObject();
    }

    private function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        return empty($text) ? 'n-a-' . substr(Uuid::uuid4()->toString(), 0, 8) : $text;
    }
}