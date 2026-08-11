<?php

declare(strict_types=1);

#[SelectOptionConfig(
    selectLabel: '',
    entityClass: PageSection::class,
    labelMethod: 'getSectionName',
    defaultOptions: ['' => '-- Select a Section --', '1' => 'Hero'],
)]
class PageSectionOptionsService extends AbstractSelectOptionsService
{
    public function __construct(
        private PageSectionModel $model,
    ) {
    }

    protected function fetchOptions(bool $active = true): array
    {
        $conditions = $active ? ['is_active', true] : [];
        $entities = $this->model->all($conditions)->asClass();
        return $this->processEntities($entities);
    }
}