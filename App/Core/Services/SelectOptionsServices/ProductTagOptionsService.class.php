<?php

declare(strict_types=1);

#[SelectOptionConfig(
    selectLabel: '',
    entityClass: ProductTag::class,
    labelMethod: 'getName',
    defaultOptions: ['' => '-- Select a tag --', '1' => 'New Arrival'],
)]
class ProductTagOptionsService extends AbstractSelectOptionsService
{
    public function __construct(
        private ProductTagModel $model,
    ) {
    }

    protected function fetchOptions(bool $active = true): array
    {
        $conditions = $active ? ['is_active', true] : [];
        $entities = $this->model->all($conditions)->asClass();
        return $this->processEntities($entities);
    }
}