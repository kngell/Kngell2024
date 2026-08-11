<?php

declare(strict_types=1);

#[SelectOptionConfig(
    selectLabel: '-- Select a Brand --',
    entityClass: Brand::class,
    labelMethod: 'getName',
    defaultOptions: ['' => '-- Select a Brand --', '1' => 'Apple'],
)]
class BrandOptionsService extends AbstractSelectOptionsService
{
    public function __construct(
        private BrandModel $brandModel,
    ) {
    }

    protected function fetchOptions(bool $active = true): array
    {
        $conditions = $active ? ['is_active', true] : [];
        $brands = $this->brandModel->all($conditions)->asClass();
        return $this->processEntities($brands);
    }
}