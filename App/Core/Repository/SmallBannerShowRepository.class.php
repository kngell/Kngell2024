<?php

declare(strict_types=1);

class SmallBannerShowRepository extends Repository
{
    protected const array COLUMN_MAPS = [
        'small_banner' => [
            'sm_banner_id', 'public_id', 'small_banner_class', 'page_target', 'product_id', 'custom_title', 'custom_title_span', 'custom_subtitle', 'custom_description', 'custom_image_url', 'custom_button_text', 'small_banner_theme', 'sort_order', 'is_active', 'valid_from', 'valid_to',
        ],
        'product' => ['pdt_id', 'name', 'slug', 'short_description', 'main_image'],
    ];

      public function findAll(array $conditions = [], ?int $limit = null, ?int $offset = null, array $columns = []): void
    {
        try {
            $columns = array_merge($columns, $this->getAllColumns());
            $qb = $this->em->createQueryBuilder();
            $select = $this->getSelectQuery($conditions, $columns, $qb);
            $select->build();
            // $this->debugSql($qb);
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function findByIds(array $conditions = [], ?int $limit = null, ?int $offset = null, ?string $keyField = null): void
    {
        try {
            $columns = [$keyField ?? 'small_banner.id'];
            $qb = $this->em->createQueryBuilder();
            $select = $this->getSelectQuery($conditions, $columns, $qb);

            if ($limit !== null) {
                $select->limit($limit);
            }
            if ($offset !== null) {
                $select->offset($offset);
            }

            $select->build();
            $this->debugSql($qb);
        } catch (Throwable $th) {
            throw new RepositoryException('Cache lookup failed: ' . $th->getMessage(), 0, $th);
        }
    }

    public function findOneBy(array $conditions = [], array $columns = []): void
    {
        try {
            $columns = array_merge($columns, $this->getAllColumns());
            $qb = $this->em->createQueryBuilder();
            $select = $this->getSelectQuery($conditions, $columns, $qb);
            $select->build();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    private function getSelectQuery(array $conditions, array $columns, SqlCompositeQueryBuilderInterface $qb): SqlSelectQueryBuilderInterface
    {
        $conditions = array_merge($conditions, [
            ...$this->createDateRangeCondition('valid_from'),
            ...$this->createDateRangeCondition('valid_to'),
        ]);
        $conditions = $this->applyGlobalScopes($conditions);
        $select = $qb->selectWithAlias($columns)
         ->distinct()
         ->from('small_banner', 'sb')
         ->leftJoin('product', 'pt')->on('product_id', 'pdt_id');

        $this->applyMixedConditions($select, $conditions);

        return $select->orderBy('position', 'ASC')
        ->orderBy('sort_order ASC');
    }
}