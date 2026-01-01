<?php

declare(strict_types=1);
class CategoryRepository extends Repository
{
    public function findAll(array $conditions = []): void
    {
        try {
            $qb = $this->em->createQueryBuilder();
            $anchor = $qb->select()->from('category')
                ->whereNull('parent_id');
            $qb->withRecursive(
                'category_tree',
                $anchor->unionAll(
                    $qb->select()->from('category')
                    ->innerJoin('category_tree')
                    ->on('category.parent_id', 'category_tree.cat_id'),
                ),
            )->select()->from('category_tree')->build();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    private function findProductsByCategoryBranch(int $parentCategoryId): SqlSelectQueryBuilderInterface
    {
        $qb = $this->em->createQueryBuilder();

        // 1. Build the CTE to get all descendant Category IDs
        $anchor = $qb->select('cat_id')->from('category')->where('cat_id', $parentCategoryId);

        $recursiveCte = $qb->withRecursive(
            'subcategories',
            $anchor->unionAll(
                $this->em->createQueryBuilder()
                    ->select('c.cat_id')
                    ->from('category', 'c')
                    ->innerJoin('subcategories', 's')
                    ->on('c.parent_id', 's.cat_id'),
            ),
        );

        // 2. Use the CTE results in the main query
        return $recursiveCte
            ->select('p.*')
            ->from('products', 'p')
            ->whereIn('p.category_id', function ($subQb) {
                $subQb->select('cat_id')->from('subcategories');
            });
    }

    private function getBreadcrumbs(int $categoryId): SqlSelectQueryBuilderInterface
    {
        $qb = $this->em->createQueryBuilder();

        $anchor = $qb->select('cat_id', 'name', 'parent_id', '1 as depth')
                     ->from('category')
                     ->where('cat_id', $categoryId);

        return $qb->withRecursive(
            'breadcrumbs',
            $anchor->unionAll(
                $this->em->createQueryBuilder()
                    ->select('c.cat_id', 'c.name', 'c.parent_id', 'b.depth + 1')
                    ->from('category', 'c')
                    ->innerJoin('breadcrumbs', 'b')
                    ->on('c.cat_id', 'b.parent_id'),
            ),
        )
        ->select('name')
        ->from('breadcrumbs')
        ->orderBy('depth', 'DESC');
        // $path = implode(' > ', $repository->getBreadcrumbs(23));
        // Output: Electronics > Laptops > Gaming Laptops
    }
}
