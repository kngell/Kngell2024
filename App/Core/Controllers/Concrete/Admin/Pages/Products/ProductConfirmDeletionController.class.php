<?php

declare(strict_types=1);

class ProductConfirmDeletionController extends AbstractConfirmDeletionController
{
    public function __construct(
        private ProductDeleteValidator $validator,
        private ConfirmDeletionModalBuilder $modalBuilder,
        ConfirmDeletionFormConfigFactory $factory,
        HtmlTemplatePathInterface $templatePath,
        FormCreatorService $frm,
    ) {
        parent::__construct($frm, $factory);
    }

    #[Override]
    protected function entityClass(): string
    {
        return Product::class;
    }

    protected function getValidator(): AbstractDeleteValidator
    {
        return $this->validator;
    }

    protected function getLabel(): string
    {
        return DeletionLabel::PRODUCT->value;
    }

    protected function getDeleteRoute(): string
    {
        return '/admin/product-delete/delete';
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
        $this->modalBuilder->setDto($dto);
        return $this->decorate(
            FormDecorator::class,
            $this,
            [
                'modalBuilder' => $this->modalBuilder,
                'formValues' => $dto->toFormValues(),
                'action' => '/admin/admin/product-add',
                'factory' => $this->factory,
            ],
        );
    }
}