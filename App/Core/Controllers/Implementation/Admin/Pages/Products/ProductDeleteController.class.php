<?php

declare(strict_types=1);

class ProductDeleteController extends AbstractDeleteController
{
    public function __construct(
        private ProductDeleteService $deleteService,
        private ProductDeleteValidator $validator,
        FormCreatorService $frm,
        HtmlTemplatePathInterface $templatePath,
    ) {
        parent::__construct($frm, $templatePath);
    }

    protected function getDeleteService(): AbstractDeleteService
    {
        return $this->deleteService;
    }

    protected function getValidator(): AbstractDeleteValidator
    {
        return $this->validator;
    }

    protected function getLabel(): string
    {
        return 'Product';
    }

    protected function getDeleteRoute(): string
    {
        return 'product-delete/delete';
    }

    protected function getConfirmView(): string
    {
        return 'admin/confirm-deletion';
    }

    protected function createDeletionDecorator(array $data): object
    {
        return new ConfirmDeletionDecorator(
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
        return array_merge(parent::buildFlashData($id, $validationResult), [
            'sku' => $validationResult->getMetadata('sku'),
            'stock_quantity' => $validationResult->getMetadata('stock_quantity'),
        ]);
    }
}