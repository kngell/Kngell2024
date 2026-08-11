<?php

declare(strict_types=1);

class FooterColumnController extends AbstractFooterPageController
{
    public function __construct(
        private FooterMenuColumnModel $model,
        private FooterColumnModalBuilder $modalBuilder,
        private FooterColumnFormConfigFactory $formFactory,
        FormCreatorService $frm,
    ) {
        parent::__construct($frm);
    }

    protected function getFooterModel(): Model
    {
        return $this->model;
    }

    protected function getEntityData(): ?FooterMenuColumn
    {
        $id = $this->resolveEntityId();
        if (empty($id)) {
            return null;
        }
        return $this->model->getById($id['value'])?->asClass();
    }

    protected function getEntityKeyfield(): ?string
    {
        return $this->model->getEntiKeyField();
    }

    protected function getEntityType(): string
    {
        return FooterMenuColumn::class;
    }

    protected function getSaveRoute(): string
    {
        return '/admin/footer-column-save/index';
    }

    protected function getDeleteRoute(): string
    {
        return '/admin/footer-column-delete/delete';
    }

    protected function getModalBuilder(): AbstractFooterModalBuilder
    {
        return $this->modalBuilder;
    }

    protected function getFormFactory(): AbstractFooterFormConfigFactory
    {
        return $this->formFactory;
    }

    /**
     * @param null|FooterMenuColumn $entity
     *
     * @return null|BaseFooterDTO
     */
    protected function createDTO(null|Entity $entity = null): ?BaseFooterDTO
    {
        if ($entity === null) {
            return new FooterColumnDTO(
                cancelRoute: '/admin/footer-column-confirm-deletion/cancel',
                deleteRoute: $this->getDeleteRoute(),
                isVisible: true,
            );
        }
        return new FooterColumnDTO(
            cancelRoute: '/admin/footer-column-confirm-deletion/cancel',
            deleteRoute: $this->getDeleteRoute(),
            isVisible: true,
            id: $entity?->getId(),
            columnKey: $entity->getColumnKey(),
            title: $entity->getTitle(),
            sortOrder: $entity->getSortOrder() ?: 0,
            isActive: $entity->getIsActive() ?: false,
            validFrom: $entity->getValidFrom(),
            validTo: $entity?->getValidTo(),
        );
    }
}