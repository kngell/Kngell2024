<?php

declare(strict_types=1);

class FooterSocialController extends AbstractFooterPageController
{
    public function __construct(
        private FooterSocialModel $model,
        private FooterSocialModalBuilder $modalBuilder,
        private FooterSocialFormConfigFactory $formFactory,
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

    protected function getEntityData(): ?FooterSocial
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
        return '/admin/footer-socials-save/index';
    }

    protected function getDeleteRoute(): string
    {
        return '/admin/footer-socials-delete/delete';
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
     * @param null|FooterSocial $entity
     *
     * @throws InvalidArgumentException
     *
     * @return null|BaseFooterDTO
     */
    protected function createDTO(null|Entity $entity = null): ?BaseFooterDTO
    {
        if ($entity === null) {
            return null;
        }

        $isActive = $entity->getIsActive() ?? false;
        return new FooterSocialDTO(
            cancelRoute: '/admin/footer-socials-confirm-deletion/cancel',
            deleteRoute: $this->getDeleteRoute(),
            isVisible: true,
            id: $entity->getId() ?? null,
            platform: $entity->getPlatform() ?? null,
            name: $entity->getName() ?? null,
            url: $entity->getUrl() ?? null,
            icon: $entity->getIcon() ?? null,
            iconClass: $entity->getIconClass() ?? null,
            sortOrder: $entity->getSortOrder() ?? 0,
            isActive: $isActive === 1 ? true : false,
            validFrom: $entity->getValidFrom() ?? null,
            validTo: $entity->getValidTo() ?? null,
        );
    }
}