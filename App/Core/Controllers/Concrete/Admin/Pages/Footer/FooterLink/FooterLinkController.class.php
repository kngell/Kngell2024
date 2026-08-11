<?php

declare(strict_types=1);

class FooterLinkController extends AbstractFooterPageController
{
    public function __construct(
        private FooterMenuLinkModel $model,
        private FooterLinkModalBuilder $modalBuilder,
        private FooterLinkFormConfigFactory $formFactory,
        private ObfuscatorManager $obfuscator,
        FormCreatorService $frm,
    ) {
        parent::__construct($frm);
    }

    protected function getFooterModel(): Model
    {
        return $this->model;
    }

    protected function getEntityData(): ?FooterMenuLink
    {
        $id = $this->resolveEntityId();
        if (empty($id)) {
            return null;
        }
        return $this->model->getById($id['value'])?->asClass();
    }

    protected function getEntityType(): string
    {
        return FooterMenuLink::class;
    }

    protected function getEntityKeyfield(): ?string
    {
        return $this->model->getEntiKeyField();
    }

    protected function getSaveRoute(): string
    {
        return '/admin/footer-link-save/index';
    }

    protected function getDeleteRoute(): string
    {
        return '/admin/footer-link-delete/delete';
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
     * @param null|FooterMenuLink $entity
     *
     * @return null|BaseFooterDTO
     */
    protected function createDTO(null|Entity $entity = null): ?BaseFooterDTO
    {
        $columnId = $this->request->get('column-filter');

        if ($columnId === null || $columnId === 'all') {
            return null;
        }
        $deobsfustaedId = $this->obfuscator->deobfuscate($columnId);
        $columnId = $this->obfuscator->obfuscate($deobsfustaedId);

        if ($entity === null) {
            return new FooterLinkDTO(
                cancelRoute: '/admin/footer-link-confirm-deletion/cancel',
                deleteRoute: $this->getDeleteRoute(),
                isVisible: true,
                columnId: (string) $columnId,
            );
        }

        return new FooterLinkDTO(
            cancelRoute: '/admin/footer-link-confirm-deletion/cancel',
            deleteRoute: $this->getDeleteRoute(),
            isVisible: true,
            id: $entity->getId(),
            columnId: $entity->getColumnId() ?? $columnId,
            title: $entity->getTitle(),
            url: $entity->getUrl(),
            target: $entity->getLinkTarget(),
            sortOrder: $entity->getSortOrder() ?? 0,
            isActive: $entity->getIsActive() ?? false,
            validFrom: $entity->getValidFrom(),
            validTo: $entity->getValidTo(),
        );
    }
}