<?php

declare(strict_types=1);

class HeroConfirmDeletionController extends AbstractConfirmDeletionController
{
    public function __construct(
        private HeroDeleteValidator $validator,
        HtmlTemplatePathInterface $templatePath,
    ) {
        parent::__construct($templatePath);
    }

    protected function getValidator(): AbstractDeleteValidator
    {
        return $this->validator;
    }

    protected function getLabel(): string
    {
        return 'Hero Section';
    }

    protected function getDeleteRoute(): string
    {
        return 'hero-delete/delete';
    }

    protected function getEntityKeyfield(): ?string
    {
        return $this->validator->getEntityKeyfield();
    }

    protected function getConfirmRedirectUrl(string $id): string
    {
        return "/hero-section/ $id/edit/";
    }

    protected function createDeletionDecorator(array $data): object
    {
        return new HeroDeletionDecorator(
            $this,
            $this->getDeleteRoute(),
            $data,
            $this->templatePath,
        );
    }

    protected function buildFlashData(
        string $id,
        DeletionValidatorResult $validationResult,
    ): array {
        return array_merge(
            parent::buildFlashData($id, $validationResult),
            [
                'position' => $validationResult->getMetadata('position'),
                'page' => $validationResult->getMetadata('page'),
            ],
        );
    }
}