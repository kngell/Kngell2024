<?php

declare(strict_types=1);

class ProductConfirmDeletionController extends AbstractConfirmDeletionController
{
    public function __construct(
        private ProductDeleteValidator $validator,
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
        return DeletionLabel::PRODUCTS->value;
    }

    protected function getDeleteRoute(): string
    {
        return '/product-delete/delete';
    }

    protected function getEntityKeyfield(): ?string
    {
        return $this->validator->getEntityKeyfield();
    }

    protected function getConfirmRedirectUrl(array $identifier): string
    {
        $id = $identifier['value'];
        return "/admin/$id/product-edit/";
    }

    protected function createDeletionDecorator(array $data): object
    {
        $dto = ConfirmDeletionDTO::fromFlashData(
            flashData: $data,
            label: $this->getLabel(),
            deleteRoute: $this->getDeleteRoute(),
            cancelRoute: '/product-confirm-deletion/cancel',
            isVisible: false,
        );

        return $this->decorate(
            ConfirmDeletionDecorator::class,
            $this,
            ['dto' => $dto],
        );
    }
}