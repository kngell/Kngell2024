<?php

declare(strict_types=1);

class FooterAboutController extends AbstractFooterPageController
{
    public function __construct(
        private FooterAboutModel $model,
        private FooterAboutModalBuilder $modalBuilder,
        private FooterAboutFormConfigFactory $formFactory,
        FormCreatorService $frm,
    ) {
        parent::__construct($frm);
    }

    protected function getEntityKeyfield(): ?string
    {
        return $this->model->getEntiKeyField();
    }

    protected function getFooterModel(): Model
    {
        return $this->model;
    }

    protected function getEntityData(): ?FooterAbout
    {
        $id = $this->resolveEntityId();
        if (empty($id)) {
            return null;
        }
        return $this->model->getById($id['value'])?->asClass();
    }

    protected function getEntityType(): string
    {
        return FooterAbout::class;
    }

    protected function getSaveRoute(): string
    {
        return '/admin/footer-about-save/index';
    }

    protected function getDeleteRoute(): string
    {
        return '/admin/footer-about-delete/delete';
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
     * @param null|FooterAbout $entity
     *
     * @return null|BaseFooterDTO
     */
    protected function createDTO(null|Entity $entity = null): ?BaseFooterDTO
    {
        if ($entity === null) {
            return new FooterAboutDTO(
                cancelRoute: '/admin/footer-about-confirm-deletion/cancel',
                deleteRoute: $this->getDeleteRoute(),
                isVisible: true,
            );
        }

        return new FooterAboutDTO(
            cancelRoute: '/admin/footer-about-confirm-deletion/cancel',
            deleteRoute: $this->getDeleteRoute(),
            isVisible: true,
            id: $entity->getId(),
            logoUrl: $entity->getLogoUrl(),
            logoIcon: $entity->getLogoIcon(),
            logoAlt: $entity->getLogoAlt(),
            logoLink: $entity->getLogoLink(),
            content: $entity->getContent(),
            sortOrder: $entity->getSortOrder() ?? 0,
            isActive: $entity->getIsActive() ?? false,
            validFrom: $entity->getValidFrom(),
            validTo: $entity->getValidTo(),
        );
    }
}