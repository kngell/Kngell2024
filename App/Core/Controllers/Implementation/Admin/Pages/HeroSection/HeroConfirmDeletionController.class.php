<?php

declare(strict_types=1);

class HeroConfirmDeletionController extends AbstractConfirmDeletionController
{
    public function __construct(
        private HeroDeleteValidator $validator,
        HtmlTemplatePathInterface $templatePath,
        FormCreatorService $frm,
    ) {
        parent::__construct($templatePath, $frm);
    }

    protected function getValidator(): AbstractDeleteValidator
    {
        return $this->validator;
    }

    protected function getLabel(): string
    {
        return DeletionLabel::HERO->value;
    }

    protected function getDeleteRoute(): string
    {
        return '/hero-section-delete/delete';
    }

    protected function getEntityKeyfield(): ?string
    {
        return $this->validator->getEntityKeyfield();
    }

    protected function getConfirmRedirectUrl(array $identifier): string
    {
        $id = $identifier['value'];
        return "/hero-page/$id/edit/";
    }

    protected function createDeletionDecorator(array $data): object
    {
        $dto = ConfirmDeletionDTO::fromFlashData(
            flashData: $data,
            label: $this->getLabel(),
            deleteRoute: $this->getDeleteRoute(),
            cancelRoute: '/hero-confirm-deletion/cancel',
            isVisible: false,
        );

        return $this->decorate(
            ConfirmDeletionDecorator::class,
            $this,
            ['dto' => $dto],
        );
    }
}