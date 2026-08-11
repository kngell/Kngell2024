<?php

declare(strict_types=1);

final class CategoryPaginatedAdapter extends AbstractPaginatedAdapter
{
    protected string $identifierPrefix = 'c_';

    public function __construct(
        CategoryModel $model,
        array $filters = [],
        array $sort = ['order_index' => 'ASC', 'name' => 'ASC'],
    ) {
        parent::__construct($model, $filters, $sort);
    }

    public function getEntityClass(): string
    {
        return Category::class;
    }

    protected function buildConditions(): array
    {
        $conditions = parent::buildConditions();
        $conditions['parent_id is null'] = true;
        return $conditions;
    }

    protected function getKeyField(): string
    {
        $keyField = parent::getKeyField();

        if (method_exists($this->model, 'hasRelationShips') && $this->model->hasRelationShips()) {
            return $keyField ? 'c_' . $keyField : 'c_public_id';
        }

        return $keyField;
    }
}