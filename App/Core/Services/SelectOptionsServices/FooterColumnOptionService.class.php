<?php

declare(strict_types=1);

#[SelectOptionConfig(
    selectLabel: '-- Select a Column --',
    entityClass: Brand::class,
    labelMethod: 'gettitle',
    defaultOptions: ['' => '-- Select a Column --', '1' => 'Company'],
)]
class FooterColumnOptionService extends AbstractSelectOptionsService
{
    public function __construct(
        private FooterMenuColumnModel $model,
    ) {
    }

    protected function fetchOptions(bool $active = true): array
    {
        $conditions = $active ? ['is_active', true] : [];
        $brands = $this->model->all($conditions)->asClass();
        return $this->processEntities($brands);
    }
}