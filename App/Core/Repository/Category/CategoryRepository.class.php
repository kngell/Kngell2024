<?php

declare(strict_types=1);

class CategoryRepository extends Repository
{
    public function findAll(array $conditions = [], ?int $limit = null, ?int $offset = null, array $columns = []): void
    {
        try {
            /** @var QueryBuilder $qbFactory */
            $qbFactory = $this->em->createQueryBuilder();

            $anchor = $qbFactory->select()->from('category');

            $recursive = $qbFactory->select()->from('category')
                ->innerJoin('category_tree')
                ->on('category.parent_id', 'category_tree.cat_id');

            $mainQuery = $qbFactory->select(...$columns)->from('category_tree');

            $whereConditions = $this->applySqlKeywords(
                $conditions,
                $anchor,
                $recursive,
                $mainQuery,
                true,
            );
            if (!empty($whereConditions)) {
                $this->applyMixedConditions($anchor, $whereConditions);
                $this->applyMixedConditions($recursive, $whereConditions);
            }

            if (!$this->hasParentCondition($whereConditions)) {
                $anchor->whereNull('parent_id');
            }

            $qbFactory->withRecursive('category_tree')
                ->body($anchor->unionAll($recursive))
                ->mainQuery($mainQuery)
                ->build();

            // $this->debugSql($qbFactory);
        } catch (Throwable $th) {
            error_log('Error in CategoryRepository::findAll: ' . $th->getMessage());
        }
    }

    private function hasParentCondition(array $conditions): bool
    {
        foreach ($conditions as $key => $value) {
            if ($key === 'parent_id') {
                return true;
            }
            if (is_array($value) && in_array('parent_id', $value)) {
                return true;
            }
        }
        return false;
    }
}
