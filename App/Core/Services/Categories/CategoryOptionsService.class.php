<?php

declare(strict_types=1);

#[SelectOptionConfig(
    selectLabel: '-- Select a Category --',
    entityClass: Category::class,
    labelMethod: 'getName',
    defaultOptions: ['' => '-- Select a Category --'],
)]
class CategoryOptionsService extends AbstractSelectOptionsService
{
    public function __construct(
        private CategoryModel $model,
    ) {
    }

    protected function fetchOptions(bool $active = true): array
    {
        $conditions = [
            'deleted_at is null',
            'ORDER BY' => ['order_index ASC', 'name ASC'],
        ];
        if ($active) {
            $conditions = array_merge($conditions, ['is_active' => true]);
        }

        $result = $this->model->all($conditions);
        $entities = $result->isSuccess() ? $result->asClass() : [];

        return $this->processEntities($entities);
    }
}